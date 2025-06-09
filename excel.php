<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Load your template file
$templatePath = 'assets/template/DASHBOARD REPORT.xlsx'; // Replace with your actual file path
$spreadsheet = IOFactory::load($templatePath);
$sheet = $spreadsheet->getActiveSheet();

// Data for the referring health facilities
$healthFacilities = [
    ['name' => 'Mariveles Medical Center', 'primary' => 1, 'secondary' => 1, 'tertiary' => 1],
    ['name' => 'Bataan General Hospital', 'primary' => 0, 'secondary' => 1, 'tertiary' => 1],
];

// Starting row for adding facilities
$startRow = 15;

// Add rows for each health facility
$totalPrimary = 0;
$totalSecondary = 0;
$totalTertiary = 0;

foreach ($healthFacilities as $facility) {
    $sheet->setCellValue("B$startRow", $facility['name']);
    $sheet->setCellValue("C$startRow", $facility['primary']);
    $sheet->setCellValue("D$startRow", $facility['secondary']);
    $sheet->setCellValue("E$startRow", $facility['tertiary']);
    
    // Update totals
    $totalPrimary += $facility['primary'];
    $totalSecondary += $facility['secondary'];
    $totalTertiary += $facility['tertiary'];
    
    $startRow++;
}

// Add percentage row
$sheet->setCellValue("B$startRow", 'Percentage');
$sheet->setCellValue("C$startRow", ($totalPrimary > 0 ? round(($totalPrimary / $totalPrimary) * 100, 2) : 0) . '%');
$sheet->setCellValue("D$startRow", ($totalSecondary > 0 ? round(($totalSecondary / $totalSecondary) * 100, 2) : 0) . '%');
$sheet->setCellValue("E$startRow", ($totalTertiary > 0 ? round(($totalTertiary / $totalTertiary) * 100, 2) : 0) . '%');

// Save the updated spreadsheet
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="updated_dashboard.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
