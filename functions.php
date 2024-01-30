<?php
require_once ("vt.php");

function turkceAyAdi($ay) {
    $aylar = array(
        'January' => 'Ocak',
        'February' => 'Şubat',
        'March' => 'Mart',
        'April' => 'Nisan',
        'May' => 'Mayıs',
        'June' => 'Haziran',
        'July' => 'Temmuz',
        'August' => 'Ağustos',
        'September' => 'Eylül',
        'October' => 'Ekim',
        'November' => 'Kasım',
        'December' => 'Aralık'
    );

    return $aylar[$ay];
}
function sessionstart()
{
    date_default_timezone_set('Europe/Istanbul');
    session_set_cookie_params(9999999999);
    ini_set('session.gc_maxlifetime', 999999999999);

    session_start();

    // Çerez kontrolü
    if (isset($_SESSION["oturum"])) {
        // "oturum" oturum değişkeni varsa, hiçbir şey yapma
    } else if (isset($_SESSION["verify"])) {
        header("Location: verify.php"); // "verify" oturum değişkeni varsa, verify.php'ye yönlendir
        ob_end_clean();
        exit();
    } else {
        header("Location: login.php"); // Hiçbiri yoksa, login.php'ye yönlendir
        ob_end_clean();
        exit();
    }
}


function insertDataToCariHareketTable($data) {
    global $conn;

    // Extract form data
    $tarih = $data["Tarih"];
    $saat = $data["Saat"];
    $customerid = $data["customerid"];
    $aciklama = $data["HareketAciklama"];
    $bbakiye = $data["bHareketBakiye"];
    $tbakiye = $data["tHareketBakiye"];
    // Determine the value of $hareketTur based on the operation
    if ($data["operation"] === "Tahsilat") {
        $hareketTur = "Tahsilat";
        $bakiyettl = $tbakiye;
        $bakiye = -abs($bakiyettl); // Make sure the value is negative (with the minus sign)
        $bakiyesms = "₺ " . number_format($bakiyettl, 2, ',', '.'); // Make sure the value is negative (with the minus sign)
        $tursms = "tahsil edilmiştir.";

    } else if ($data["operation"] === "Borc") {
        $hareketTur = "Borç";
        $bakiye = $bbakiye;
        $bakiyesms ="₺ " . number_format($bakiye, 2, ',', '.');
        $tursms = "ödeme girişi olmuştur.";
    } else {
        // Invalid operation type
        $response = array("status" => "error", "message" => "Invalid operation type!");
        echo json_encode($response);
        return;
    }

    // Combine date and time into a single DATETIME value
    $hareketTarih = $tarih . ' ' . $saat;

    // Perform data insertion for carihareket table
    $stmt = $conn->prepare("INSERT INTO carihareket (customerid, HareketTarih, HareketAciklama, HareketTur, HareketBakiye) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$customerid, $hareketTarih, $aciklama, $hareketTur, $bakiye]);

    // Calculate the total balance for the customer
    $stmt = $conn->prepare("SELECT SUM(HareketBakiye) AS totalBalance FROM carihareket WHERE customerid = ?");
    $stmt->execute([$customerid]);
    $totalBalanceRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalBalance = $totalBalanceRow['totalBalance'];
    if ($totalBalance < 0) {
        $totatText ="Toplam";
        $totalBalanceText = number_format(abs($totalBalance), 2, ',', '.') . " TL alacaklısınız.";
    }
    else if ($totalBalance > 0){
        $totatText ="Toplam";
        $totalBalanceText = number_format($totalBalance, 2, ',', '.') . " TL ödemeniz bulunmaktadır.";
    }
    else if ($totalBalance == 0){
        $totatText ="Ödemeniz";
        $totalBalanceText = "bulunmamaktadır.";
    }

    // Fetch the customer's information from the Customer table
    $stmt = $conn->prepare("SELECT Firma, FirmaTel FROM customer WHERE id = ?");
    $stmt->execute([$customerid]);
    $customerInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    $firmaisim = $customerInfo["Firma"];
    $firmatel =  $customerInfo["FirmaTel"];

        //SMS
        $curl = curl_init();
        $params = [
            'api_id' => 'xxxxxxx',
            'api_key' => 'xxxxxxx',
            'sender' => 'xxxxx',
            'message_type' => 'turkce',
            'message_content_type' => 'bilgi',
            'phones' => [
                [
                    "phone" => $firmatel,
                    "message" => "Sn. $firmaisim, $tarih tarihinde $bakiyesms TL $tursms $totatText $totalBalanceText \nfatihyuzuguldu.com"
                ]
            ]
        ];

        $curl_options = [
            CURLOPT_URL => 'https://api.vatansms.net/api/v1/NtoN',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ]
        ];

    curl_setopt_array($curl, $curl_options);

    $response = curl_exec($curl);

    curl_close($curl);
    $stmt = $conn->prepare("INSERT INTO sms (response) VALUES (?)");
    $stmt->execute([$response]);

}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["Tarih"])) {
    // Call the function to insert data into both tables
    insertDataToCariHareketTable($_POST);
}