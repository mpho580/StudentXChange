<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/admin.php';
require_once __DIR__ . '/../src/fpdf.php';   // ← path to the FPDF file

requireLogin();
requireAdmin();

$type = $_GET['type'] ?? 'full';
$allowed = ['full', 'summary', 'users', 'products'];
if (!in_array($type, $allowed, true)) {
    $type = 'full';
}

$stats    = getAdminStats();
$users    = getAllUsers();
$products = getAllProducts();

// ---------- PDF class ----------
class ReportPDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(13, 110, 253); // primary blue
        $this->Cell(0, 10, 'StudentXChange - Admin Report', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(108, 117, 125);
        $this->Cell(0, 6, 'Generated: ' . date('d M Y H:i'), 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(108, 117, 125);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' | StudentXChange', 0, 0, 'C');
    }

    function SectionTitle(string $title)
    {
        $this->Ln(8);
        $this->SetFont('Arial', 'B', 13);
        $this->SetTextColor(10, 88, 202);
        $this->Cell(0, 8, $title, 0, 1);
        $this->SetDrawColor(13, 110, 253);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(4);
    }
}

// ---------- Create PDF ----------
$pdf = new ReportPDF();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

// ===== SUMMARY / FULL =====
if ($type === 'full' || $type === 'summary') {
    $pdf->SectionTitle('Overview Statistics');

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetFillColor(248, 249, 250);
    $pdf->SetTextColor(13, 110, 253);

    $w = 45;
    $pdf->Cell($w, 12, $stats['total_users'], 1, 0, 'C', true);
    $pdf->Cell($w, 12, $stats['total_products'], 1, 0, 'C', true);
    $pdf->Cell($w, 12, $stats['total_messages'], 1, 0, 'C', true);
    $pdf->Cell($w, 12, $stats['total_reviews'], 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(80, 80, 80);
    $pdf->Cell($w, 8, 'Total Users', 1, 0, 'C');
    $pdf->Cell($w, 8, 'Active Listings', 1, 0, 'C');
    $pdf->Cell($w, 8, 'Messages Sent', 1, 0, 'C');
    $pdf->Cell($w, 8, 'Reviews', 1, 1, 'C');
}

// ===== USERS =====
if ($type === 'full' || $type === 'users') {
    $pdf->SectionTitle('All Users (' . count($users) . ')');

    // Header
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(13, 110, 253);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(12, 8, 'ID', 1, 0, 'C', true);
    $pdf->Cell(40, 8, 'Name', 1, 0, 'L', true);
    $pdf->Cell(30, 8, 'Student No.', 1, 0, 'L', true);
    $pdf->Cell(55, 8, 'Email', 1, 0, 'L', true);
    $pdf->Cell(22, 8, 'Role', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Joined', 1, 1, 'C', true);

    // Rows
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(0, 0, 0);
    $fill = false;

    foreach ($users as $u) {
        $pdf->SetFillColor(248, 249, 250);
        $pdf->Cell(12, 7, $u['id'], 1, 0, 'C', $fill);
        $pdf->Cell(40, 7, substr($u['name'], 0, 22), 1, 0, 'L', $fill);
        $pdf->Cell(30, 7, $u['student_number'], 1, 0, 'L', $fill);
        $pdf->Cell(55, 7, substr($u['email'], 0, 30), 1, 0, 'L', $fill);
        $pdf->Cell(22, 7, ucfirst($u['role']), 1, 0, 'C', $fill);
        $pdf->Cell(25, 7, date('d M Y', strtotime($u['created_at'])), 1, 1, 'C', $fill);
        $fill = !$fill;
    }
}

// ===== PRODUCTS =====
if ($type === 'full' || $type === 'products') {
    $pdf->SectionTitle('All Products (' . count($products) . ')');

    // Header
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(13, 110, 253);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(10, 8, 'ID', 1, 0, 'C', true);
    $pdf->Cell(50, 8, 'Title', 1, 0, 'L', true);
    $pdf->Cell(22, 8, 'Price', 1, 0, 'R', true);
    $pdf->Cell(30, 8, 'Category', 1, 0, 'L', true);
    $pdf->Cell(35, 8, 'Seller', 1, 0, 'L', true);
    $pdf->Cell(20, 8, 'Condition', 1, 0, 'C', true);
    $pdf->Cell(23, 8, 'Listed', 1, 1, 'C', true);

    // Rows
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(0, 0, 0);
    $fill = false;

    foreach ($products as $p) {
        $pdf->SetFillColor(248, 249, 250);
        $pdf->Cell(10, 7, $p['id'], 1, 0, 'C', $fill);
        $pdf->Cell(50, 7, substr($p['title'], 0, 28), 1, 0, 'L', $fill);
        $pdf->Cell(22, 7, 'R ' . number_format((float)$p['price'], 2), 1, 0, 'R', $fill);
        $pdf->Cell(30, 7, substr($p['category_name'] ?? '', 0, 15), 1, 0, 'L', $fill);
        $pdf->Cell(35, 7, substr($p['seller_name'] ?? '', 0, 18), 1, 0, 'L', $fill);
        $pdf->Cell(20, 7, $p['item_condition'] ?? '', 1, 0, 'C', $fill);
        $pdf->Cell(23, 7, date('d M Y', strtotime($p['created_at'])), 1, 1, 'C', $fill);
        $fill = !$fill;
    }
}

// Output
$filename = 'StudentXChange_' . ucfirst($type) . '_Report_' . date('Y-m-d') . '.pdf';
$pdf->Output('D', $filename);   // 'D' = force download
exit;