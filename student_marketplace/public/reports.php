<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/reports.php';

// Only valid report types are allowed
$type = $_GET['type'] ?? 'full';
$allowed = ['summary', 'users', 'products', 'full'];

if (!in_array($type, $allowed, true)) {
    http_response_code(400);
    exit('Invalid report type.');
}

generateReportPdf($type);
