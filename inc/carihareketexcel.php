<?php
require_once "../vt.php";
require_once "PhpSpreadsheet/vendor/autoload.php";

// Get the filter values from the POST data
$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : null;
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : null;
$title = isset($_POST['title']) ? $_POST['title'] : null;
$customerid = isset($_POST['customerid']) ? $_POST['customerid'] : null;

// Perform the database query based on the filter values
// Make sure to use prepared statements for security (this is a simplified example)
$filters = [];
$sql = "SELECT * FROM carihareket WHERE customerid = :customerid";

if ($startDate) {
    $sql .= " AND HareketTarih >= :startDate";
    $filters['startDate'] = $startDate;
}
if ($endDate) {
    $sql .= " AND HareketTarih <= :endDate";
    $filters['endDate'] = $endDate;
}

// Add this filter to list all data regardless of the dates
// Comment this block if you want to list data based on the dates
// $sql .= " AND AcekTarih >= '1970-01-01' AND AcekTarih <= '2038-01-19'";

// Sort data by 'HareketTarih' column in ascending order
$sql .= " ORDER BY HareketTarih ASC";

$query = $conn->prepare($sql);
$query->bindParam(':customerid', $customerid, PDO::PARAM_INT);

if ($startDate) {
    $query->bindParam(':startDate', $startDate, PDO::PARAM_STR);
}
if ($endDate) {
    $query->bindParam(':endDate', $endDate, PDO::PARAM_STR);
}

$query->execute();
$data = $query->fetchAll(PDO::FETCH_ASSOC);


// Create a new PhpSpreadsheet object
$spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set the title cell style
$titleCellStyle = [
    'font' => [
        'bold' => true,
        'size' => 22,
        'name' => 'Calibri',
        'color' => ['rgb' => 'FF0000'],
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
    ],
];

$sheet->setCellValue('A3', 'Tarih');
$sheet->setCellValue('B3', 'Açıklama');
$sheet->setCellValue('C3', 'Tutar');
$sheet->setCellValue('D3', 'Tür');

$row = 5;
foreach ($data as $row_data) {
    // Format AcekTarih as short date format (e.g., 01.10.2023)
    $AcekTarih = date('d.m.Y', strtotime($row_data['HareketTarih']));
    $sheet->setCellValue('A' . $row, $AcekTarih);
    $sheet->setCellValue('B' . $row, mb_strtoupper($row_data['HareketAciklama'], 'UTF-8')); // Make all letters uppercase
    $sheet->setCellValue('C' . $row, $row_data['HareketBakiye']);
    $sheet->setCellValue('D' . $row, $row_data['HareketTur']);
    $row++;
}

// Set the title cell value and merge cells
$sheet->setCellValue('A1', $title);
$sheet->mergeCells('A1:D2');

// Apply the title cell style
$sheet->getStyle('A1:D2')->applyFromArray($titleCellStyle);

// Set the header cell styles
$headerCellStyle = [
    'font' => [
        'bold' => true,
        'size' => 14,
        'name' => 'Calibri',
        'color' => ['rgb' => 'FF0000'],
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
    ],
];

// Merge cells and apply header cell styles
$sheet->mergeCells('A3:A4');
$sheet->getStyle('A3:A4')->applyFromArray($headerCellStyle);
$sheet->getColumnDimension('A')->setWidth(16);

$sheet->mergeCells('B3:B4');
$sheet->getStyle('B3:B4')->applyFromArray($headerCellStyle);
$sheet->getColumnDimension('B')->setWidth(35);

$sheet->mergeCells('C3:C4');
$sheet->getStyle('C3:C4')->applyFromArray($headerCellStyle);
$sheet->getColumnDimension('C')->setWidth(17);

$sheet->mergeCells('D3:D4');
$sheet->getStyle('D3:D4')->applyFromArray($headerCellStyle);
$sheet->getColumnDimension('D')->setWidth(15);

// Set the row height
$sheet->getDefaultRowDimension()->setRowHeight(30);

// Set the column width
$sheet->getDefaultColumnDimension()->setWidth(15);

// Apply Arial Black 11 bold and center alignment for all data rows (A5:E5 and below)
$allDataStyle = [
    'font' => [
        'bold' => true,
        'size' => 10,
        'name' => 'Arial Black',
        'color' => ['rgb' => '000000'],
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
    ],
];

$sheet->getStyle('A5:D' . ($row - 1))->applyFromArray($allDataStyle);

// Add border to all data cells
$allDataBorders = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['rgb' => '000000'],
        ],
    ],
];

$sheet->getStyle('A5:D' . ($row - 1))->applyFromArray($allDataBorders);

// Apply short date format to the 'A' column (Tarih)
$sheet->getStyle('A5:A' . ($row - 1))->getNumberFormat()->setFormatCode('dd.mm.yyyy');

// Apply number format to the 'C' column (Tutar)
$sheet->getStyle('C5:C' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');

// Calculate the total sum of the 'AcekTutar' column
$total = array_sum(array_column($data, 'HareketBakiye'));

// Set the total sum in the Excel sheet
$sheet->setCellValue('C' . ($row + 1), $total);

// Apply center alignment and bold font to the total cell
$sheet->getStyle('C' . ($row + 1))->applyFromArray([
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
    ],
    'font' => [
        'bold' => true,
        'size' => 11,
        'name' => 'Arial Black',
        'color' => ['rgb' => '000000'],
    ],
]);

$sheet->setCellValue('B' . ($row + 1), 'Toplam:');

// Apply center alignment and bold font to the 'Toplam' cell (B hücresine Kalın şekilde "Toplam" yazdır)
$sheet->getStyle('B' . ($row + 1))->applyFromArray([
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
    ],
    'font' => [
        'bold' => true,
        'size' => 11,
        'name' => 'Arial Black',
        'color' => ['rgb' => '000000'],
    ],
]);
// Generate the Excel file
$writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

// Generate a unique filename for the Excel file
$filename = 'carihareket_' . uniqid() . '.xlsx';

// Save the Excel file to a temporary directory
$writer->save(__DIR__ . '/tmp/' . $filename);

// Directly send the file to the client for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="carihareket.xlsx"');
header('Cache-Control: max-age=0');
readfile(__DIR__ . '/tmp/' . $filename);

// Delete the temporary file
unlink(__DIR__ . '/tmp/' . $filename);
?>
