<?php

namespace App\Controllers;

use App\Models\AcademicSession;
use App\Models\Batch;
use App\Models\Department;

/**
 * Imports batches, teachers or students from a CSV file (api/import.php, admin only).
 *
 * Every row goes through the same controller and the same rules as the normal form
 * (BatchController::save and UserController::save), so a row is saved only when the form would accept it.
 * Rows that fail are reported with their line number; the other rows are still saved.
 * Names are used instead of ids in the file (the department code, the batch code ...) and are turned into ids here.
 */
class ImportController {

    private const DESIGNATIONS = ['Professor', 'Associate Professor', 'Assistant Professor', 'Senior Lecturer', 'Lecturer'];

    private const MAX_ROWS = 500;
    private const MAX_BYTES = 1048576;   // 1 MB

    // The columns of each file. Those marked required must have a value in every row.
    private const COLUMNS = [
        'batches' => [
            'name' => true, 'code' => true, 'shift' => true, 'department' => true, 'admission_session' => true,
            'description' => false, 'status' => false,
        ],
        'teachers' => [
            'name' => true, 'email' => true, 'phone' => false, 'password' => true, 'employee_id' => true, 'department' => true,
            'designation' => true, 'specialization' => false, 'research_area' => false, 'research_management' => false, 'status' => false,
        ],
        'students' => [
            'name' => true, 'email' => true, 'phone' => false, 'password' => true, 'student_id' => true, 'department' => true,
            'batch' => true, 'gender' => false, 'cgpa' => false, 'completed_credit_hours' => false, 'status' => false,
        ],
    ];

    // What each column means: shown in the "Columns & values" box before the upload.
    private const HELP = [
        'batches' => [
            'name'              => 'The batch name, for example CSE 66 Regular. It must be new.',
            'code'              => 'A short unique code, for example CSE-66R. Students use it to say which batch they are in.',
            'shift'             => 'Regular or Evening.',
            'department'        => 'The department code from the list below, for example CSE.',
            'admission_session' => 'The admission session name from the list below, for example Spring 2026.',
            'description'       => 'Any note about the batch (up to 255 characters).',
            'status'            => 'active or inactive. Empty means active.',
        ],
        'teachers' => [
            'name'                => 'The full name.',
            'email'               => 'A new email address. It is the login of the teacher.',
            'phone'               => 'The phone number.',
            'password'            => 'At least 8 characters. The teacher can change it later.',
            'employee_id'         => 'A new unique employee ID, for example T-1001.',
            'department'          => 'The department code from the list below, for example CSE.',
            'designation'         => 'One of the designations below.',
            'specialization'      => 'The field the teacher is expert in.',
            'research_area'       => 'The research area of the teacher.',
            'research_management' => 'yes or no. Yes lets the teacher manage research (approve, assign supervisors, batches, groups/teams). Empty means no.',
            'status'              => 'active or inactive. Empty means active.',
        ],
        'students' => [
            'name'                   => 'The full name.',
            'email'                  => 'A new email address. It is the login of the student.',
            'phone'                  => 'The phone number.',
            'password'               => 'At least 8 characters. The student can change it later.',
            'student_id'             => 'A new unique student ID.',
            'department'             => 'The department code from the list below, for example CSE.',
            'batch'                  => 'The batch code from the list below, for example CSE-66R. The batch must belong to the department.',
            'gender'                 => 'Male, Female or Other.',
            'cgpa'                   => 'A number from 0 to 4.',
            'completed_credit_hours' => 'A whole number from 0 to 400.',
            'status'                 => 'active or inactive. Empty means active.',
        ],
    ];

    // Two dummy rows for the sample file of each type.
    private const SAMPLES = [
        'batches' => [
            ['CSE 66 Regular', 'CSE-66R', 'Regular', 'CSE', 'Spring 2026', 'Regular shift batch 66', 'active'],
            ['CSE 66 Evening', 'CSE-66E', 'Evening', 'CSE', 'Spring 2026', 'Evening shift batch 66', 'active'],
        ],
        'teachers' => [
            ['Dr. Rahim Uddin', 'rahim.uddin@example.com', '01711000001', 'password123', 'T-1001', 'CSE', 'Professor', 'Machine Learning', 'Artificial Intelligence', 'yes', 'active'],
            ['Karima Begum', 'karima.begum@example.com', '01711000002', 'password123', 'T-1002', 'CSE', 'Lecturer', 'Web Development', 'Software Engineering', 'no', 'active'],
        ],
        'students' => [
            ['Abdul Karim', 'abdul.karim@example.com', '01811000001', 'password123', '66001', 'CSE', 'CSE-66R', 'Male', '3.50', '60', 'active'],
            ['Nusrat Jahan', 'nusrat.jahan@example.com', '01811000002', 'password123', '66002', 'CSE', 'CSE-66R', 'Female', '3.75', '60', 'active'],
        ],
    ];

    public static function isType($type): bool
    {
        return isset(self::COLUMNS[$type]);
    }

    /**
     * What the "Columns & values" box and the pick step need: the columns with their help, and the real
     * departments, sessions and batches of the database (so the user picks names that exist).
     */
    public static function reference($type): array
    {
        $columns = [];
        foreach (self::COLUMNS[$type] as $name => $required) {
            $columns[] = ['name' => $name, 'required' => $required, 'help' => self::HELP[$type][$name] ?? ''];
        }

        $options = (new Department())->options();
        $codeOf = [];
        foreach ($options as $d) {
            $codeOf[(int) $d['id']] = $d['code'];
        }

        $batches = [];
        foreach ((new Batch())->lookup() as $batch) {
            $batches[] = ['code' => $batch['code'], 'name' => $batch['name'], 'department' => $codeOf[(int) $batch['department_id']] ?? ''];
        }

        return [
            'columns'      => $columns,
            'departments'  => array_map(fn($d) => ['code' => $d['code'], 'name' => $d['name']], $options),
            'sessions'     => array_map(fn($s) => ['name' => $s['name']], (new AcademicSession())->options()),
            'batches'      => $batches,
            'designations' => self::DESIGNATIONS,
            'shifts'       => ['Regular', 'Evening'],
        ];
    }

    /**
     * The sample file: the header line and two dummy rows. Returns [file name, csv text].
     * $picks = department (a code), session (a name), batch (a code): the values the user picked, which replace
     * the dummy ones so the sample already holds names that exist. A pick that is not real is ignored.
     */
    public static function sample($type, array $picks = []): array
    {
        $rows = self::SAMPLES[$type];
        $reference = self::reference($type);

        $department = self::pick($picks['department'] ?? '', array_column($reference['departments'], 'code'));
        $session    = self::pick($picks['session'] ?? '', array_column($reference['sessions'], 'name'));
        $batch      = self::pick($picks['batch'] ?? '', array_column(
            array_filter($reference['batches'], fn($b) => $department === null || strcasecmp($b['department'], $department) === 0), 'code'
        ));

        // the position of each picked column in the dummy rows
        $position = array_flip(array_keys(self::COLUMNS[$type]));
        foreach ($rows as $i => $row) {
            if ($department !== null) {
                $rows[$i][$position['department']] = $department;
            }
            if ($session !== null && isset($position['admission_session'])) {
                $rows[$i][$position['admission_session']] = $session;
            }
            if ($batch !== null && isset($position['batch'])) {
                $rows[$i][$position['batch']] = $batch;
            }
        }

        $out = fopen('php://temp', 'r+');
        fputcsv($out, array_keys(self::COLUMNS[$type]));
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        rewind($out);

        return ["sample_{$type}.csv", "\xEF\xBB\xBF" . stream_get_contents($out)];   // the BOM lets Excel open it as UTF-8
    }

    /**
     * Reads the uploaded file and saves every row.
     * Returns ['success' => bool, 'message' => text, 'created' => n, 'failed' => n, 'errors' => [['line' => 3, 'name' => .., 'messages' => [..]]]]
     */
    public static function run($type, $file){

        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return self::fail('Please choose a CSV file.');
        }
        if (($file['error'] ?? 0) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            return self::fail('The file could not be uploaded. Please try again.');
        }
        if ($file['size'] > self::MAX_BYTES) {
            return self::fail('The file is too big. The most is 1 MB.');
        }
        if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'csv') {
            return self::fail('Please upload a .csv file. In Excel use File > Save As > CSV.');
        }

        $rows = self::readRows($file['tmp_name']);
        if ($rows === null) {
            return self::fail('The file is empty or is not a CSV file.');
        }

        [$header, $lines] = $rows;
        $missing = array_diff(array_keys(self::COLUMNS[$type]), $header);
        if ($missing) {
            return self::fail('These columns are missing: ' . implode(', ', $missing) . '. Please start from the sample file.');
        }
        if (count($lines) === 0) {
            return self::fail('The file has no rows under the header.');
        }
        if (count($lines) > self::MAX_ROWS) {
            return self::fail('The file has more than ' . self::MAX_ROWS . ' rows. Please split it.');
        }

        $lookups = self::lookups();
        $created = 0;
        $errors = [];

        foreach ($lines as $number => $cells) {
            $row = [];
            foreach ($header as $i => $column) {
                $row[$column] = trim($cells[$i] ?? '');
            }

            $result = self::saveRow($type, $row, $lookups);

            if ($result['success']) {
                $created++;
            } else {
                $errors[] = ['line' => $number, 'name' => $row['name'] ?? '', 'messages' => $result['messages']];
            }
        }

        $failed = count($errors);
        $words = ['batches' => 'batch(es)', 'teachers' => 'teacher(s)', 'students' => 'student(s)'][$type];

        return [
            'success' => true,
            'message' => "{$created} {$words} added" . ($failed ? ", {$failed} row(s) skipped." : '.'),
            'created' => $created,
            'failed'  => $failed,
            'errors'  => $errors,
        ];
    }

    // ---------------------------------------------------------------------------------
    // One row
    // ---------------------------------------------------------------------------------

    private static function saveRow(string $type, array $row, array $lookups): array
    {
        $messages = [];

        foreach (self::COLUMNS[$type] as $column => $required) {
            if ($required && $row[$column] === '') {
                $messages[] = "{$column} is required.";
            }
        }
        if ($messages) {
            return ['success' => false, 'messages' => $messages];
        }

        $status = self::status($row['status']);
        if ($status === null) {
            return ['success' => false, 'messages' => ['status must be active or inactive.']];
        }

        $department = $lookups['departments'][strtolower($row['department'])] ?? null;
        if ($department === null) {
            return ['success' => false, 'messages' => ["department \"{$row['department']}\" was not found. Use the department code, for example CSE."]];
        }

        if ($type === 'batches') {
            $session = $lookups['sessions'][strtolower($row['admission_session'])] ?? null;
            if ($session === null) {
                return ['success' => false, 'messages' => ["admission_session \"{$row['admission_session']}\" was not found. Use the session name, for example Spring 2026."]];
            }

            $result = BatchController::save([
                'name'                 => $row['name'],
                'code'                 => $row['code'],
                'shift'                => self::shift($row['shift']),
                'department_id'        => $department,
                'admission_session_id' => $session,
                'description'          => $row['description'],
                'status'               => $status,
            ]);

            return self::answer($result);
        }

        $request = [
            'name'          => $row['name'],
            'email'         => $row['email'],
            'phone'         => $row['phone'],
            'password'      => $row['password'],
            'status'        => $status,
            'department_id' => $department,
        ];

        if ($type === 'teachers') {
            $request += [
                'employee_id'      => $row['employee_id'],
                'designation'      => self::designation($row['designation']),
                'specialization'   => $row['specialization'],
                'research_area'    => $row['research_area'],
                'research_management' => self::yes($row['research_management']) ? 1 : 0,
            ];

            return self::answer(UserController::save('teacher', $request, [], null));
        }

        // a student: the batch is found by its code (or name) inside the department
        $batch = $lookups['batches'][$department . '|' . strtolower($row['batch'])] ?? null;
        if ($batch === null) {
            return ['success' => false, 'messages' => ["batch \"{$row['batch']}\" was not found in this department. Use the batch code, for example CSE-66R."]];
        }

        $request += [
            'student_id'             => $row['student_id'],
            'batch_id'               => $batch,
            'gender'                 => ucfirst(strtolower($row['gender'])),
            'cgpa'                   => $row['cgpa'],
            'completed_credit_hours' => $row['completed_credit_hours'],
        ];

        return self::answer(UserController::save('student', $request, [], null));
    }

    // Turns the answer of a controller into a list of readable messages.
    private static function answer(array $result): array
    {
        if ($result['success']) {
            return ['success' => true, 'messages' => []];
        }

        $messages = [];
        foreach ($result['errors'] as $field => $list) {
            foreach ((array) $list as $message) {
                $messages[] = $message;
            }
        }

        return ['success' => false, 'messages' => $messages ?: [$result['message']]];
    }

    // ---------------------------------------------------------------------------------
    // The file and the small value helpers
    // ---------------------------------------------------------------------------------

    /**
     * Reads the CSV: returns [header (lower case names), lines (line number => cells)] or null.
     * The delimiter is found from the first line, so a comma or a semicolon (some Excel settings) both work.
     */
    private static function readRows(string $path): ?array
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            return null;
        }

        $first = fgets($handle);
        if ($first === false) {
            fclose($handle);
            return null;
        }
        $first = preg_replace('/^\xEF\xBB\xBF/', '', $first);
        $delimiter = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';

        $header = array_map(fn($name) => strtolower(trim($name)), str_getcsv($first, $delimiter));
        $lines = [];
        $number = 1;

        while (($cells = fgetcsv($handle, 0, $delimiter)) !== false) {
            $number++;

            if ($cells === [null] || trim(implode('', $cells)) === '') {
                continue;   // an empty line
            }
            $lines[$number] = $cells;
        }
        fclose($handle);

        return [$header, $lines];
    }

    // The department, session and batch names of the database, in lower case, for matching.
    private static function lookups(): array
    {
        $lookups = ['departments' => [], 'sessions' => [], 'batches' => []];

        foreach ((new Department())->options() as $department) {
            $lookups['departments'][strtolower($department['code'])] = (int) $department['id'];
            $lookups['departments'][strtolower($department['name'])] = (int) $department['id'];
        }

        foreach ((new AcademicSession())->options() as $session) {
            $lookups['sessions'][strtolower($session['name'])] = (int) $session['id'];
        }

        foreach ((new Batch())->lookup() as $batch) {
            $lookups['batches'][$batch['department_id'] . '|' . strtolower($batch['code'])] = (int) $batch['id'];
            $lookups['batches'][$batch['department_id'] . '|' . strtolower($batch['name'])] = (int) $batch['id'];
        }

        return $lookups;
    }

    // active / inactive / 1 / 0 (empty = active). Returns 1, 0 or null when the word is not understood.
    private static function status(string $value): ?int
    {
        return match (strtolower($value)) {
            '', 'active', '1', 'yes'  => 1,
            'inactive', '0', 'no'     => 0,
            default                   => null,
        };
    }

    private static function yes(string $value): bool
    {
        return in_array(strtolower($value), ['yes', 'y', '1', 'true'], true);
    }

    // "evening" -> "Evening", so the file does not need exact capital letters.
    private static function shift(string $value): string
    {
        return ucfirst(strtolower($value));
    }

    // Matches the designation without caring about capital letters ("professor" -> "Professor").
    private static function designation(string $value): string
    {
        foreach (self::DESIGNATIONS as $name) {
            if (strtolower($value) === strtolower($name)) {
                return $name;
            }
        }

        return $value;
    }

    // The picked value written like the database has it, or null when it is empty or does not exist.
    private static function pick($value, array $allowed): ?string
    {
        $value = trim((string) $value);

        foreach ($allowed as $name) {
            if ($value !== '' && strcasecmp($value, (string) $name) === 0) {
                return (string) $name;
            }
        }

        return null;
    }

    private static function fail(string $message): array
    {
        return ['success' => false, 'message' => $message, 'created' => 0, 'failed' => 0, 'errors' => []];
    }
}
