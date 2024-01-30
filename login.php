<?php
require_once("functions.php");
require 'inc/PHPMailer/PHPMailer.php';
require 'inc/PHPMailer/SMTP.php';
require 'inc/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
session_start();
if (isset($_SESSION["oturum"]) && $_SESSION["oturum"] == "6789") {
  header("Location: index.php");
}
if (isset($_SESSION["verify"]) && $_SESSION["verify"] == "4567") {
  header("Location: verify.php");
} elseif (isset($_COOKIE["cerez"])) {
  $query = $conn->prepare("SELECT * FROM kullanicilar");
  $query->execute();
  while ($result = $query->fetch()) {
    if ($_COOKIE["cerez"] == hash("sha256", "aa" . $result["Eposta"] . "bb")) {
      $_SESSION["verify"] = "4567";
      $_SESSION["Eposta"] = $result["Eposta"];
    }
  }
}


?>

<!DOCTYPE html>

<html lang="tr" class="light-style layout-wide customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="assets/" data-template="horizontal-menu-template">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <title>Giriş Yap - FAYU</title>
  <meta name="description" content="Yönetim Paneli" />
  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="assets/img/logo/favicon.png" />
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap" rel="stylesheet" />

  <!-- Icons -->
  <link rel="stylesheet" href="assets/vendor/fonts/materialdesignicons.css" />
  <link rel="stylesheet" href="assets/vendor/fonts/flag-icons.css" />

  <!-- Menu waves for no-customizer fix -->
  <link rel="stylesheet" href="assets/vendor/libs/node-waves/node-waves.css" />

  <!-- Core CSS -->
  <link rel="stylesheet" href="assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
  <link rel="stylesheet" href="assets/css/demo.css" />

  <!-- Vendors CSS -->
  <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
  <link rel="stylesheet" href="assets/vendor/libs/typeahead-js/typeahead.css" />
  <!-- Vendor -->
  <link rel="stylesheet" href="assets/vendor/libs/@form-validation/umd/styles/index.min.css" />
  <link rel="stylesheet" href="assets/vendor/css/pages/page-auth.css" />
  <!-- Helpers -->
  <script src="assets/vendor/js/helpers.js"></script>
  <script src="assets/vendor/js/template-customizer.js"></script>
  <script src="assets/js/config.js"></script>
</head>
<body>
<!-- Content -->

<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-4">
      <!-- Login -->
      <div class="card p-2">
        <!-- Logo -->
        <div class="app-brand justify-content-center mt-5">
          <a href="index.php" class="app-brand-link gap-2">
            <img src="assets/img/logo/logo.png" >
          </a>
        </div>
        <!-- /Logo -->
        <?php
        if ($_POST) {
          $KullaniciAdi = htmlspecialchars($_POST["kullaniciadi"]);
          $Sifre = hash("sha256", "56" . $_POST["Sifre"] . "23");
          $date = date('Y-m-d H:i:s');
          if (empty($KullaniciAdi) || empty($Sifre)) {

            echo '<script type="text/javascript" src="assets/js/sweet-alert/sweetalert2.all.min.js"></script>';
            echo "<script> Swal.fire({title:'Hata!', text:'Kullanıcı Adı veya şifre alanını doldurun.', icon:'error', confirmButtonText:'Kapat'});</script>";
          }
          else {
            $query = $conn->prepare("SELECT * FROM kullanicilar WHERE KullaniciAdi=:KullaniciAdi");
            $query->execute(['KullaniciAdi' => $KullaniciAdi]);
            $row = $query->fetch();

            if (!$row) {
              // Kullanıcı adı bulunamadıysa hata ver

              echo '<script type="text/javascript" src="assets/js/sweet-alert/sweetalert2.all.min.js"></script>';
              echo "<script> Swal.fire({title:'Hata!', text:'Kullanıcı Adı Bulunamadı', icon:'error', confirmButtonText:'Kapat'});</script>";

            } else {
              $Eposta = $row["Eposta"];
              $facheck = $row["2FA"];

              if ($Sifre == $row["Sifre"]) {
                if ($facheck == 1) {
                  $_SESSION["id"] = $row["id"];

                  if (!isset($_SESSION['kod']) || !isset($_POST['kod'])) {
                    $_SESSION['kod'] = rand(111111, 999999);
                  } elseif (isset($_POST['kod'])) {
                    //validate code
                    if ($_POST['kod'] == $_SESSION['kod']) {
                      unset($_SESSION['kod']);
                    }
                  }

                  $mail = new PHPMailer(true);
                  $mail->CharSet = 'UTF-8';
                  $mail->Encoding = 'base64';
                  //Server settings
                  $mail->isSMTP();                                            //Send using SMTP
                  $mail->Host       = 'xxxxxx.fatihyuzuguldu.com';                     //Set the SMTP server to send through
                  $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                  $mail->Username   = 'xxxxxxx@fatihyuzuguldu.com';                     //SMTP username
                  $mail->Password   = 'xxxxxxxx';                               //SMTP password
                  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                  $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                  //Recipients
                  $mail->setFrom('xxxxxx@fatihyuzuguldu.com', 'Fayu Verify Code');
                  $mail->addAddress($row["Eposta"]); // Use the "Email" cookie value
                  //Content
                  $mail->isHTML(true);                                  //Set email format to HTML
                  $mail->Subject = "Doğrulama Kodunuz: " . $_SESSION['kod'];
                  $mail->Body = '
                  <!DOCTYPE html>
                  <html lang="tr">
                    <head>
                      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                      <meta http-equiv="X-UA-Compatible" content="IE=edge">
                      <meta name="viewport" content="width=device-width, initial-scale=1.0">
                      <meta name="description" content="FAYU Yönetim Paneli ile harekete geç">
                      <meta name="keywords" content="fayu,fatih,yüzügüldü,yönetim,paneli">
                      <meta name="author" content="Fatih Yüzügüldü">
                      <link rel="icon" href="https://malimusaviraliozturk.com/admin/assets/images/favicon.png" type="image/x-icon">
                      <link rel="shortcut icon" href="https://malimusaviraliozturk.com/admin/assets/images/favicon.png" type="image/x-icon">
                      <title>Verify - FAYU Yönetim Paneli</title>
                      <link href="https://fonts.googleapis.com/css?family=Work+Sans:100,200,300,400,500,600,700,800,900" rel="stylesheet">
                      <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
                      <link href="https://fonts.googleapis.com/css?family=Poppins:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
                      <style type="text/css">
                        body{
                        text-align: center;
                        margin: 0 auto;
                        width: 650px;
                        font-family: work-Sans, sans-serif;
                        background-color: #f6f7fb;
                        display: block;
                        }
                        ul{
                        margin:0;
                        padding: 0;
                        }
                        li{
                        display: inline-block;
                        text-decoration: unset;
                        }
                        a{
                        text-decoration: none;
                        }
                        p{
                        margin: 15px 0;
                        }
                        h5{
                        color:#444;
                        text-align:left;
                        font-weight:400;
                        }
                        .text-center{
                        text-align: center
                        }
                        .main-bg-light{
                        background-color: #fafafa;
                        box-shadow: 0px 0px 14px -4px rgba(0, 0, 0, 0.2705882353);
                        }
                        .title{
                        color: #444444;
                        font-size: 22px;
                        font-weight: bold;
                        margin-top: 10px;
                        margin-bottom: 10px;
                        padding-bottom: 0;
                        text-transform: uppercase;
                        display: inline-block;
                        line-height: 1;
                        }
                        table{
                        margin-top:30px
                        }
                        table.top-0{
                        margin-top:0;
                        }
                        table.order-detail , .order-detail th , .order-detail td {
                        border: 1px solid #ddd;
                        border-collapse: collapse;
                        }
                        .order-detail th{
                        font-size:16px;
                        padding:15px;
                        text-align:center;
                        }
                        .footer-social-icon tr td img{
                        margin-left:5px;
                        margin-right:5px;
                        }
                      </style>
                    </head>
                    <body style="margin: 20px auto;">
                      <table align="center" border="0" cellpadding="0" cellspacing="0" style="padding: 0 30px;background-color: #fff; -webkit-box-shadow: 0px 0px 14px -4px rgba(0, 0, 0, 0.2705882353);box-shadow: 0px 0px 14px -4px rgba(0, 0, 0, 0.2705882353);width: 100%;">
                        <tbody>
                          <tr>
                            <td>
                              <table align="center" border="0" cellpadding="0" cellspacing="0">
                                <tbody>
                                <tr>
                                  <td>
                                    <h3>Merhaba: ' . $row["Isim"] . '.</h3>
                                  </td>
                                </tr>
                                  <tr>
                                    <td><img src="https://malimusaviraliozturk.com/admin/assets/images/forms/email.png" width="144" alt="" style=";margin-bottom: 30px;"></td>
                                  </tr>
                                  <tr>
                                    <td><img src="https://malimusaviraliozturk.com/admin/assets/images/email-template/success.png" alt=""></td>
                                  </tr>
                                  <tr>
                                    <td>
                                      <h3>2FA Kodunuz: ' . $_SESSION['kod'] . '.</h3>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>
                                      <p>Lütfen sayfaya dönüp 2FA Doğrulamasını geçiniz.</p>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                      <table class="main-bg-light text-center top-0" align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tbody>
                          <tr>
                            <td style="padding: 30px;">
                              <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 20px auto 0;">
                                <tbody>
                                  <tr>
                                    <td>
                                      <p style="font-size:13px; margin:0;">2023 Copyright by Fatih Yüzügüldü</p>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td><a href="https://fatihyuzuguldu.com" style="font-size:13px; margin:0;text-decoration: underline;">Website</a></td>
                                  </tr>
                                </tbody>
                              </table>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </body>
                  </html>';
                  $mail->AltBody = strip_tags($_SESSION['kod']);

                  $mail->send();
                  $mail->ClearAddresses();
                  $mail->ClearAttachments();

                  $_SESSION["verify"] = "4567";
                  $_SESSION["Eposta"] = $row["Eposta"];
                  header("Location: verify.php");
                  exit();
                }
                elseif ($facheck == 2){
                  if (!isset($_SESSION['kod']) || !isset($_POST['kod'])) {
                    $_SESSION['kod'] = rand(111111, 999999);
                    $kodsms = $_SESSION['kod'];
                  } elseif (isset($_POST['kod'])) {
                    //validate code
                    if ($_POST['kod'] == $_SESSION['kod']) {
                      unset($_SESSION['kod']);
                    }
                  }

                  if (!empty($row["Telefon"]) && // Telefon alanı boş değilse
                      strlen($row["Telefon"]) == 10 && // Telefon numarası 10 hane mi?
                      substr($row["Telefon"], 0, 1) == '5'
                  ) {
                    // Telefon numarası geçerli ise
                    $curl = curl_init();
                    $params = [
                        'api_id' => 'xxxxx',
                        'api_key' => 'xxxx',
                        'sender' => 'xxxxx',
                        'message_type' => 'turkce',
                        'message_content_type' => 'bilgi',
                        'phones' => [
                            [
                                "phone" => $row["Telefon"],
                                "message" => "Fayu Giriş Kodu: $kodsms \nfatihyuzuguldu.com"
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
                    $updateQuery = "UPDATE kullanicilar SET SonGiris=:SonGiris WHERE id=:id";
                    $stmt = $conn->prepare($updateQuery);
                    $stmt->execute([
                        'SonGiris' => $date,
                        'id' => $row["id"]
                    ]);
                    $_SESSION["verify"] = "4567";
                    $_SESSION["Telefon"] = $row["Telefon"];
                    $_SESSION["id"] = $row["id"];
                    header("Location: verify.php");
                    exit();
                  } else {
                    echo '<script type="text/javascript" src="assets/js/sweet-alert/sweetalert2.all.min.js"></script>';
                    echo "<script> Swal.fire({title:'Hata!', text:'Telefon Numarası Hatalı', icon:'error', confirmButtonText:'Kapat'}).then((value) => {if (value.isConfirmed){window.location.href='index.php'}});</script>";
                    exit();
                  }




                }
                else {

                  $updateQuery = "UPDATE kullanicilar SET SonGiris=:SonGiris WHERE id=:id";
                  $stmt = $conn->prepare($updateQuery);
                  $stmt->execute([
                      'SonGiris' => $date,
                      'id' => $row["id"]
                  ]);
                  $_SESSION["oturum"] = "6789";
                  $_SESSION["id"] = $row["id"];
                  header("Location: index.php");
                  exit();
                }
              }
              else {
                echo '<script type="text/javascript" src="assets/js/sweet-alert/sweetalert2.all.min.js"></script>';
                echo "<script> Swal.fire({title:'Hata!', text:'Şifre Yanlış', icon:'error', confirmButtonText:'Kapat'});</script>";
              }
            }
          }
        }
        ?>
        <div class="card-body mt-2">
          <div style="text-align: center">
            <h4 class="mb-2">Hoşgeldin 👋</h4>
            <p class="mb-4">Panele erişmek için lütfen giriş yapın!</p>
          </div>

          <form id="formAuthentication" class="mb-3" method="post" action="login.php">
            <div class="form-floating form-floating-outline mb-3">
              <input type="text" class="form-control" id="kullaniciadi" name="kullaniciadi" placeholder="Kullanıcı adı girin" value="<?= @$KullaniciAdi ?>"  autofocus required/>
              <label for="kullaniciadi">Kullanıcı Adı</label>
            </div>
            <div class="mb-3">
              <div class="form-password-toggle">
                <div class="input-group input-group-merge">
                  <div class="form-floating form-floating-outline">
                    <input type="password" id="Sifre" class="form-control" name="Sifre" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                    <label for="Sifre">Şifre</label>
                  </div>
                  <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
                </div>
              </div>
            </div>
            <div class="mb-3 d-flex justify-content-between">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember-me" checked />
                <label class="form-check-label" for="remember-me"> Beni Hatırla </label>
              </div>
            </div>
            <div class="mb-3">
              <button class="btn btn-primary d-grid w-100" type="submit">Giriş Yap</button>
            </div>
          </form>

        </div>
      </div>
      <!-- /Login -->
      <img
          alt="mask"
          src="assets/img/illustrations/auth-basic-login-mask-light.png"
          class="authentication-image d-none d-lg-block"
          data-app-light-img="illustrations/auth-basic-login-mask-light.png"
          data-app-dark-img="illustrations/auth-basic-login-mask-dark.png" />
    </div>
  </div>
</div>

<!-- / Content -->

<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->
<script src="assets/vendor/libs/jquery/jquery.js"></script>
<script src="assets/vendor/libs/popper/popper.js"></script>
<script src="assets/vendor/js/bootstrap.js"></script>
<script src="assets/vendor/libs/node-waves/node-waves.js"></script>
<script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="assets/vendor/libs/hammer/hammer.js"></script>
<script src="assets/vendor/libs/i18n/i18n.js"></script>
<script src="assets/vendor/libs/typeahead-js/typeahead.js"></script>
<script src="assets/vendor/js/menu.js"></script>

<!-- endbuild -->

<!-- Vendors JS -->
<script src="assets/vendor/libs/@form-validation/umd/bundle/popular.min.js"></script>
<script src="assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js"></script>
<script src="assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js"></script>

<!-- Main JS -->
<script src="assets/js/main.js"></script>

<!-- Page JS -->
<script src="assets/js/pages-auth.js"></script>
</body>
</html>
