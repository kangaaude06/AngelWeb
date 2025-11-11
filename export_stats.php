<?php
// export_stats.php
// NECESSITE composer require phpoffice/phpspreadsheet
require_once 'vendor/autoload.php'; 
require_once 'Pointage.class.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Vérification de sécurité (seul l'administrateur peut exporter)
if (!isset($_SESSION['user_id']) || $_SESSION['grade'] !== 'Administrateur') {
    header("Location: login.php");
    exit;
}

$pointageHandler = new Pointage();
$stats_ouvriers = $pointageHandler->getWorkerStats();

// 1. CRÉATION DU SPREADSHEET
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Stats_Pointage_AngelHouse");

// 2. STYLISATION DES EN-TÊTES (Respect du style Violet/Or)
$headerStyle = array(
    'font' => array('bold' => true, 'color' => array('rgb' => '8A2BE2')), // Violet
    'fill' => array('fillType' => Fill::FILL_SOLID, 'startColor' => array('rgb' => 'FFD700')), // Or
);
$sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

// 3. EN-TÊTES DU FICHIER
$sheet->setCellValue('A1', 'Ouvrier');
$sheet->setCellValue('B1', 'Département');
$sheet->setCellValue('C1', 'Présences (P)');
$sheet->setCellValue('D1', 'Retards (R)');
$sheet->setCellValue('E1', 'Absences (A)');
$sheet->setCellValue('F1', 'Total Pointé');

// 4. REMPLISSAGE DES DONNÉES
$row = 2; // Commencer à la deuxième ligne
foreach ($stats_ouvriers as $stats) {
    $sheet->setCellValue('A' . $row, $stats['prenom'] . ' ' . $stats['nom']);
    $sheet->setCellValue('B' . $row, $stats['departement']);
    
    // Style de couleur conditionnel pour Présences/Retards/Absences
    $sheet->setCellValue('C' . $row, $stats['total_present'])->getStyle('C' . $row)->getFont()->setColor(new Color(Color::COLOR_GREEN));
    $sheet->setCellValue('D' . $row, $stats['total_retard'])->getStyle('D' . $row)->getFont()->setColor(new Color(Color::COLOR_YELLOW));
    $sheet->setCellValue('E' . $row, $stats['total_absent'])->getStyle('E' . $row)->getFont()->setColor(new Color(Color::COLOR_RED));
    
    $sheet->setCellValue('F' . $row, $stats['total_pointage']);
    $row++;
}

// 5. AJUSTEMENT DE LA LARGEUR DES COLONNES
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// 6. ENVOI DU FICHIER AU NAVIGATEUR
$writer = new Xlsx($spreadsheet);
$fileName = 'Statistiques_Pointage_' . date('Ymd_His') . '.xlsx';

// Configuration des en-têtes HTTP pour forcer le téléchargement du fichier
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;
?>