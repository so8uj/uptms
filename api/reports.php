<?php

/**
 * The reports (admin only). Always answers with JSON, except the export, which sends a file.
 *
 *   GET api/reports.php                                         the numbers at the top, the list of reports, the years
 *   GET api/reports.php?type=research&kind=Thesis&status=ongoing&batch_id=3&year=2026&q=ai&page=2     one page of a report
 *   GET api/reports.php?type=research&...same filters...&export=xlsx      all the rows as an Excel file (or export=csv)
 *
 * type = research | teams | students | supervisors | evaluations.
 * Filters (each report uses the ones that fit it): q, kind (Project | Thesis | Research Paper), status, category_id, batch_id, year.
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\ReportController;
use App\Services\Api;

Api::start();
Api::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Api::json(['message' => 'Method not allowed.'], 405);
}

if (!isset($_GET['type'])) {
    Api::json(ReportController::overview());
}

$type = $_GET['type'];
if (!ReportController::isType($type)) {
    Api::json(['message' => 'Unknown report.'], 400);
}

$format = $_GET['export'] ?? '';
if ($format !== '') {
    if (!in_array($format, ['xlsx', 'csv'], true)) {
        Api::json(['message' => 'Unknown file type.'], 400);
    }

    [$name, $contentType, $content] = ReportController::file($type, $_GET, $format);

    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="' . $name . '"');
    header('Content-Length: ' . strlen($content));
    header('Cache-Control: no-store');
    echo $content;
    exit;
}

Api::json(ReportController::page($type, $_GET));
