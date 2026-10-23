<?php
declare(strict_types=1);

require_once __DIR__ . '/admin.php';
require_once __DIR__ . '/../lib/fpdf.php';


class StudentXChangePDF extends FPDF
{
    public string $reportTitle = '';

    public function Header(): void
    {
        
        $this->SetFillColor(13, 110, 253);
        $this->Rect(0, 0, 210, 20, 'F');

        
        $this->SetFont('Helvetica', 'B', 16);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(10, 5);
        $this->Cell(0, 10, 'StudentXChange', 0, 0, 'L');

       
        $this->SetFont('Helvetica', 'B', 11);
        $this->SetXY(0, 6);
        $this->Cell(200, 8, $this->reportTitle, 0, 0, 'R');

        
        $this->SetFillColor(255, 193, 7);
        $this->Rect(0, 20, 210, 1.2, 'F');

        $this->SetY(25);
        $this->SetTextColor(33, 37, 41);
    }

    public function Footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 8);
        $this->SetTextColor(108, 117, 125);

        $this->Cell(0, 10, 'Generated on ' . date('d M Y, H:i'), 0, 0, 'L');
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }

    /**
     * Render a summary grid of stat boxes.
     */
    public function StatsBoxes(array $stats): void
    {
        $labels = [
            'total_users'    => 'Total Users',
            'total_products' => 'Active Listings',
            'total_messages' => 'Messages Sent',
            'total_reviews'  => 'Reviews',
        ];

        $boxWidth  = 42;
        $boxHeight = 22;
        $gap       = 4;
        $startX    = 10;
        $y         = $this->GetY();

        $this->SetFillColor(248, 249, 250);
        $this->SetDrawColor(222, 226, 230);
        $this->SetLineWidth(0.3);

        $x = $startX;
        foreach ($labels as $key => $label) {
            // Card
            $this->SetXY($x, $y);
            $this->Cell($boxWidth, $boxHeight, '', 1, 0, 'C', true);

            // Value (blue)
            $this->SetFont('Helvetica', 'B', 18);
            $this->SetTextColor(13, 110, 253);
            $this->SetXY($x, $y + 2);
            $this->Cell($boxWidth, 10, (string)($stats[$key] ?? 0), 0, 0, 'C');

            // Label (grey)
            $this->SetFont('Helvetica', '', 8);
            $this->SetTextColor(108, 117, 125);
            $this->SetXY($x, $y + 13);
            $this->Cell($boxWidth, 6, $label, 0, 0, 'C');

            $x += $boxWidth + $gap;
        }

        $this->SetY($y + $boxHeight + 8);
        $this->SetTextColor(33, 37, 41);
    }

    /**
     * Render a section heading.
     */
    public function SectionTitle(string $title): void
    {
        $this->SetFont('Helvetica', 'B', 12);
        $this->SetTextColor(13, 110, 253);
        $this->Cell(0, 8, $title, 0, 1, 'L');

        $this->SetDrawColor(255, 193, 7);
        $this->SetLineWidth(0.6);
        $this->Line(10, $this->GetY(), 60, $this->GetY());
        $this->Ln(4);
    }

    /**
     * Render a table with header + rows.
     * Columns: [ 'label' => '...', 'width' => n, 'align' => 'L|C|R' ]
     */
    public function Table(array $columns, array $rows): void
    {
        // Header
        $this->SetFont('Helvetica', 'B', 9);
        $this->SetFillColor(13, 110, 253);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(13, 110, 253);

        foreach ($columns as $col) {
            $this->Cell($col['width'], 8, $col['label'], 0, 0, $col['align'] ?? 'L', true);
        }
        $this->Ln();

        // Rows
        $this->SetFont('Helvetica', '', 8.5);
        $this->SetTextColor(33, 37, 41);
        $this->SetDrawColor(222, 226, 230);

        $fill = false;
        foreach ($rows as $row) {
            // Page break safeguard
            if ($this->GetY() > 260) {
                $this->AddPage();
                // re-render header
                $this->SetFont('Helvetica', 'B', 9);
                $this->SetFillColor(13, 110, 253);
                $this->SetTextColor(255, 255, 255);
                $this->SetDrawColor(13, 110, 253);
                foreach ($columns as $col) {
                    $this->Cell($col['width'], 8, $col['label'], 0, 0, $col['align'] ?? 'L', true);
                }
                $this->Ln();
                $this->SetFont('Helvetica', '', 8.5);
                $this->SetTextColor(33, 37, 41);
                $this->SetDrawColor(222, 226, 230);
            }

            $this->SetFillColor(248, 249, 250);
            foreach ($row as $i => $value) {
                $col = $columns[$i];
                $this->Cell($col['width'], 7, $this->truncate((string)$value, $col['width']), 'B', 0, $col['align'] ?? 'L', $fill);
            }
            $this->Ln();
            $fill = !$fill;
        }
    }

    private function truncate(string $text, float $width): string
    {
        $max = max(3, (int)($width / 1.9));
        return mb_strlen($text) > $max ? mb_substr($text, 0, $max - 1) . '…' : $text;
    }
}

/**
 * Build and stream a report PDF.
 *
 * @param string $type  One of: 'summary', 'users', 'products', 'full'
 */
function generateReportPdf(string $type = 'full'): void
{
    requireAdmin();

    $stats    = getAdminStats();
    $users    = getAllUsers();
    $products = getAllProducts();

    $titles = [
        'summary'  => 'Summary Report',
        'users'    => 'Users Report',
        'products' => 'Products Report',
        'full'     => 'Full Marketplace Report',
    ];
    $title = $titles[$type] ?? 'Marketplace Report';

    $pdf = new StudentXChangePDF('P', 'mm', 'A4');
    $pdf->reportTitle = $title;
    $pdf->AliasNbPages();
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();

    // Intro line
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->SetTextColor(108, 117, 125);
    $pdf->Cell(0, 6, 'StudentXChange | Campus Peer Marketplace - Admin Reporting', 0, 1, 'L');
    $pdf->Ln(2);

    if (in_array($type, ['summary', 'full'], true)) {
        $pdf->SectionTitle('Overview Statistics');
        $pdf->StatsBoxes($stats);
    }

    if (in_array($type, ['users', 'full'], true)) {
        $pdf->SectionTitle('Registered Users (' . count($users) . ')');

        $columns = [
            ['label' => 'ID',        'width' => 12, 'align' => 'L'],
            ['label' => 'Name',      'width' => 42, 'align' => 'L'],
            ['label' => 'Student #', 'width' => 28, 'align' => 'L'],
            ['label' => 'Email',     'width' => 56, 'align' => 'L'],
            ['label' => 'Role',      'width' => 20, 'align' => 'L'],
            ['label' => 'Joined',    'width' => 22, 'align' => 'L'],
        ];

        $rows = array_map(static fn($u) => [
            $u['id'],
            $u['name'] . ' ' . ($u['surname'] ?? ''),
            $u['student_number'],
            $u['email'],
            ucfirst($u['role']),
            date('d M Y', strtotime($u['created_at'])),
        ], $users);

        $pdf->Table($columns, $rows);
        $pdf->Ln(6);
    }

    if (in_array($type, ['products', 'full'], true)) {
        $pdf->SectionTitle('Listed Products (' . count($products) . ')');

        $columns = [
            ['label' => 'ID',        'width' => 12, 'align' => 'L'],
            ['label' => 'Title',     'width' => 48, 'align' => 'L'],
            ['label' => 'Price',     'width' => 22, 'align' => 'R'],
            ['label' => 'Category',  'width' => 30, 'align' => 'L'],
            ['label' => 'Seller',    'width' => 32, 'align' => 'L'],
            ['label' => 'Condition', 'width' => 22, 'align' => 'L'],
        ];

        $rows = array_map(static fn($p) => [
            $p['id'],
            $p['title'],
            'R ' . number_format((float)$p['price'], 2),
            $p['category_name'],
            $p['seller_name'],
            $p['item_condition'],
        ], $products);

        $pdf->Table($columns, $rows);
    }

    $filename = 'StudentXChange_' . $title . '_' . date('Ymd_His') . '.pdf';
    $pdf->Output('D', $filename);
    exit;
}
