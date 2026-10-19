<?php

namespace App\Services;

use App\Config\Database;

class Validation
{
    /**
     * Validate an array of data against an array of rules.
     *
     * $result = Validation::validate($_POST, [
     *     'name'     => 'required|max:100',
     *     'email'    => 'required|email|max:150|unique:users,email',
     *     'password' => ['required', 'min:8'],      // a string with | or an array both work
     * ]);
     *
     * The 3rd argument is optional and gives a field a nicer name in the messages:
     *     Validation::validate($data, $rules, ['department_id' => 'Department'])   ->   "Department is required."
     *
     * Returns ['success' => bool, 'errors' => ['field' => ['message', ...]]]
     *
     * Rules: required, email, min:n, max:n, in:a,b,c, numeric, integer, between:low,high (a number range), date (yyyy-mm-dd),
     *        unique:table,column[,ignoreId]          (deleted rows count too, for example user emails)
     *        unique_active:table,column[,ignoreId]   (rows with deleted_at set are ignored, for example category names)
     * min/max count characters. unique's optional ignoreId skips that row (use it when editing).
     * For a table whose key is not "id", add it as a 4th part: unique:student_profiles,student_id,5,user_id
     *
     * Bad input never throws. Only a mistake in the rules themselves (unknown rule name,
     * min/max without a number, a malformed unique rule) throws InvalidArgumentException.
     */
    public static function validate(array $data, array $rules, array $labels = []): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $field = (string) $field;
            $value = self::clean($data[$field] ?? null);
            $label = $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));   // a custom name for the messages, if given

            // These rules are for text. A non-empty array (for example a tampered form field) is just invalid.
            if (is_array($value) && $value !== []) {
                $errors[$field][] = "{$label} is invalid.";
                continue;
            }

            foreach (self::parseRules($fieldRules) as [$name, $param]) {
                $error = self::check($name, $value, $param, $label);

                if ($error !== null) {
                    $errors[$field][] = $error;

                    // An empty required field needs one message, not one per rule.
                    if ($name === 'required') {
                        break;
                    }
                }
            }
        }

        return ['success' => $errors === [], 'errors' => $errors];
    }

    /** Trim strings and turn numbers/booleans into strings. Arrays and other types stay as they are. */
    private static function clean(mixed $value): mixed
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (is_int($value) || is_float($value) || is_bool($value)) {
            return (string) $value;
        }

        return $value;
    }

    /** Turn 'required|max:5' or ['required', 'max:5'] into [['required', null], ['max', '5']]. */
    private static function parseRules(string|array $fieldRules): array
    {
        $list = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);
        $parsed = [];

        foreach ($list as $rule) {
            $rule = trim((string) $rule);

            if ($rule === '') {
                continue;
            }

            [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
            $parsed[] = [strtolower(trim($name)), $param === null ? null : trim($param)];
        }

        return $parsed;
    }

    private static function check(string $rule, mixed $value, ?string $param, string $label): ?string
    {
        $isEmpty = $value === null || $value === '' || $value === [];

        // Only "required" cares about empty values; the other rules apply once something was entered.
        if ($rule !== 'required' && $isEmpty) {
            return null;
        }

        switch ($rule) {
            case 'required':
                return $isEmpty ? "{$label} is required." : null;

            case 'email':
                return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL)
                    ? null
                    : "{$label} must be a valid email address.";

            case 'min':
                $min = self::number($rule, $param);
                return is_string($value) && mb_strlen($value) >= $min
                    ? null
                    : "{$label} must be at least {$min} characters.";

            case 'max':
                $max = self::number($rule, $param);
                return is_string($value) && mb_strlen($value) <= $max
                    ? null
                    : "{$label} must not be more than {$max} characters.";

            case 'unique':
                if (!is_string($value)) {
                    return "{$label} is invalid.";
                }
                return self::isUnique($value, (string) $param, $label, false);

            case 'unique_active':
                if (!is_string($value)) {
                    return "{$label} is invalid.";
                }
                return self::isUnique($value, (string) $param, $label, true);

            case 'in':
                $allowed = array_map('trim', explode(',', (string) $param));
                return is_string($value) && in_array($value, $allowed, true)
                    ? null
                    : "{$label} has an invalid value.";

            case 'date':
                $date = is_string($value) ? \DateTime::createFromFormat('Y-m-d', $value) : false;
                return $date && $date->format('Y-m-d') === $value ? null : "{$label} must be a valid date.";

            case 'numeric':
                return is_string($value) && is_numeric($value) ? null : "{$label} must be a number.";

            case 'integer':
                return is_string($value) && filter_var($value, FILTER_VALIDATE_INT) !== false
                    ? null
                    : "{$label} must be a whole number.";

            case 'between':
                [$low, $high] = array_pad(explode(',', (string) $param), 2, null);
                if (!is_numeric($low) || !is_numeric($high)) {
                    throw new \InvalidArgumentException('The between rule needs two numbers, for example between:0,4');
                }
                return is_string($value) && is_numeric($value) && $value >= $low && $value <= $high
                    ? null
                    : "{$label} must be between {$low} and {$high}.";

            default:
                throw new \InvalidArgumentException("Unknown validation rule: {$rule}");
        }
    }

    private static function number(string $rule, ?string $param): int
    {
        if ($param === null || !ctype_digit($param)) {
            throw new \InvalidArgumentException("The {$rule} rule needs a number, for example {$rule}:8");
        }

        return (int) $param;
    }

    /** Returns null when the value is unique, otherwise an error message. */
    private static function isUnique(string $value, string $param, string $label, bool $activeOnly): ?string
    {
        [$table, $column, $ignoreId, $idColumn] = array_pad(explode(',', $param), 4, null);
        $idColumn = $idColumn ?: 'id';   // the table's key column; profile tables use user_id

        // Table and column names cannot be bound as parameters, so only plain identifiers are allowed.
        if (!preg_match('/^[a-z_]+$/i', (string) $table) || !preg_match('/^[a-z_]+$/i', (string) $column)
            || !preg_match('/^[a-z_]+$/i', (string) $idColumn)) {
            throw new \InvalidArgumentException('The unique rule must look like unique:table,column[,ignoreId[,idColumn]]');
        }

        $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = :value";
        $bind = ['value' => $value];

        // Soft-deleted rows are still in the table, so unique_active skips them.
        if ($activeOnly) {
            $sql .= ' AND deleted_at IS NULL';
        }

        if ($ignoreId !== null && trim($ignoreId) !== '') {
            $sql .= " AND `{$idColumn}` != :ignore";
            $bind['ignore'] = (int) $ignoreId;
        }

        try {
            $stmt = Database::connect()->prepare($sql);
            $stmt->execute($bind);
            $count = (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            // Fail closed: if uniqueness cannot be confirmed, do not let the value through.
            error_log('Validation unique check failed: ' . $e->getMessage());
            return "{$label} could not be verified. Please try again.";
        }

        return $count === 0 ? null : "{$label} is already taken.";
    }
}
