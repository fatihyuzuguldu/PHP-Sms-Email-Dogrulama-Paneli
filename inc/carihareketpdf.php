<?php
require_once "../functions.php";
require_once "dompdf/autoload.inc.php"; // DOMPDF kütüphanesi yolu

// Get the filter values from the POST data
$startDate = isset($_POST['startDate']) ? date('Y-m-d 00:00:00', strtotime($_POST['startDate'])) : null;
$endDate = isset($_POST['endDate']) ? date('Y-m-d 23:59:59', strtotime($_POST['endDate'])) : null;
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

// HTML içeriğini oluştur
$html = '<html>';
$html .= '<head><style>table { width: 100%; border-collapse: collapse; font-family: DejaVu Sans, sans-serif; } th, td { border: 1px solid black; padding: 5px; text-align: center; } th { background-color: #f2f2f2; }</style></head>';
$html .= '<body>';
$html .= '<h1 style="text-align: center; font-family: DejaVu Sans, sans-serif;">' . $title . '</h1>';
$html .= '<table>';
$html .= '<tr>';
$html .= '<th>Tarih</th>';
$html .= '<th>Aciklama</th>';
$html .= '<th>Bakiye</th>';
$html .= '<th>Tür</th>';
$html .= '</tr>';

$totalBalanceUSD = 0;
$totalBalanceTRY = 0;

foreach ($data as $row_data) {
    $html .= '<tr>';
    $html .= '<td>' . date('d.m.Y', strtotime($row_data['HareketTarih'])) . '</td>';
    $html .= '<td>' . mb_strtoupper($row_data['HareketAciklama'], 'UTF-8') . '</td>';
    $html .= '<td>' . number_format($row_data['HareketBakiye'], 2, ',', '.') . '</td>';
    $html .= '<td>' . mb_strtoupper($row_data['HareketTur'], 'UTF-8') . '</td>';
    $html .= '</tr>';

    // Toplam bakiyeleri güncelle
    $totalBalanceUSD += $row_data['HareketBakiye'];
}

$html .= '<tr>';
$html .= '<td colspan="2" style="text-align:right"><strong>Toplam:</strong></td>';
$html .= '<td><strong>₺' . number_format($totalBalanceUSD, 2, ',', '.') . '</strong></td>';
$html .= '<td></td>';
$html .= '</tr>';

$html .= '</table>';
$html .= '</body>';
$html .= '</html>';

// DOMPDF nesnesi oluştur
$dompdf = new Dompdf\Dompdf();

// HTML içeriğini yükle
$dompdf->loadHtml($html);

// Sayfa boyutunu ve yönünü ayarla
$dompdf->setPaper('A4', 'portrait');

// PDF'i oluştur
$dompdf->render();

// PDF'i indir
$dompdf->stream('CariHareket.pdf', array('Attachment' => 1)); // 'Attachment' değeri 1 olarak ayarlandı
?>
