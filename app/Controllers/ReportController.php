<?php

namespace App\Controllers;

use App\Models\Report;
use App\Services\XlsxWriter;

/**
 * Called by api/reports.php (admin only). It builds the reports and the files for the export.
 * The same rows are used for the screen (one page) and for the files (all rows with the same filters).
 */
class ReportController {

    private const PER_PAGE = 15;

    // How the statuses and the codes are written for a reader.
    private const STATUS = [
        'requested' => 'Requested', 'pending' => 'Pending', 'approved' => 'Approved', 'ongoing' => 'Ongoing',
        'completed' => 'Completed', 'rejected' => 'Rejected', 'correction' => 'Needs correction',
        'open' => 'Approved', 'closed' => 'Completed',
    ];

    public static function isType($type): bool
    {
        return isset(Report::TYPES[$type]);
    }

    // The numbers at the top, the list of reports and the years for the filter.
    public static function overview(){

        $model = new Report();

        return [
            'summary' => $model->summary(),
            'types'   => Report::TYPES,
            'years'   => $model->years(),
        ];
    }

    // One page of a report for the screen.
    public static function page($type, $query){

        $model   = new Report();
        $filters = self::filters($query);
        $total   = $model->count($type, $filters);
        $all     = !empty($query['all']);   // the print: every row on one page (at most 2000)
        $perPage = $all ? 2000 : self::PER_PAGE;
        $pages   = max(1, (int) ceil($total / $perPage));
        $page    = $all ? 1 : min(max(1, (int) ($query['page'] ?? 1)), $pages);

        return [
            'type'    => $type,
            'columns' => self::columnList($model, $type),
            'rows'    => self::readable($model, $type, $model->rows($type, $filters, $perPage, ($page - 1) * $perPage)),
            'total'   => $total,
            'page'    => $page,
            'pages'   => $pages,
            'perPage' => $perPage,
        ];
    }

    /**
     * A file with all the rows of a report (with the filters of the page).
     * $format = xlsx | csv. Returns [file name, content type, content].
     */
    public static function file($type, $query, $format){

        $model   = new Report();
        $titles  = $model->columns($type);
        $rows    = self::readable($model, $type, $model->rows($type, self::filters($query)));
        $name    = 'uptms_' . $type . '_report_' . date('Y-m-d');

        // the values in the order of the columns
        $table = array_map(function ($row) use ($titles) {
            $line = [];
            foreach (array_keys($titles) as $key) {
                $line[] = $row[$key] ?? '';
            }
            return $line;
        }, $rows);

        if ($format === 'csv') {
            $out = fopen('php://temp', 'r+');
            fputcsv($out, array_values($titles));
            foreach ($table as $line) {
                fputcsv($out, $line);
            }
            rewind($out);

            // the BOM lets Excel read the letters as UTF-8
            return [$name . '.csv', 'text/csv; charset=utf-8', "\xEF\xBB\xBF" . stream_get_contents($out)];
        }

        return [
            $name . '.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            XlsxWriter::build(Report::TYPES[$type], array_values($titles), $table),
        ];
    }

    // ---------------------------------------------------------------------------------

    // The filters that came from the page, cleaned.
    private static function filters($query): array
    {
        return [
            'q'           => trim((string) ($query['q'] ?? '')),
            'type'        => (string) ($query['kind'] ?? ''),
            'status'      => (string) ($query['status'] ?? ''),
            'category_id' => (int) ($query['category_id'] ?? 0),
            'batch_id'    => (int) ($query['batch_id'] ?? 0),
            'year'        => (int) ($query['year'] ?? 0),
        ];
    }

    private static function columnList(Report $model, string $type): array
    {
        $list = [];
        foreach ($model->columns($type) as $key => $label) {
            $list[] = ['key' => $key, 'label' => $label];
        }

        return $list;
    }

    // The rows for a reader: statuses in words, numbers as numbers, the hidden columns taken away.
    private static function readable(Report $model, string $type, array $rows): array
    {
        foreach ($rows as $i => $row) {
            foreach (['status', 'research_status'] as $key) {
                if (isset($row[$key]) && $row[$key] !== null) {
                    $rows[$i][$key] = self::STATUS[$row[$key]] ?? $row[$key];
                }
            }

            foreach (Report::NUMBERS as $key) {
                if (array_key_exists($key, $rows[$i]) && $rows[$i][$key] !== null) {
                    $rows[$i][$key] = (int) $rows[$i][$key];
                }
            }

            unset($rows[$i]['_id']);
        }

        return $rows;
    }
}
