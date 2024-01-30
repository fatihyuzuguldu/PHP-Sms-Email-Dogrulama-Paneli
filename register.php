<?php
require_once("functions.php");
error_reporting(0);
ini_set('display_errors', 0);
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
}


?><!DOCTYPE html>

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

<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-4">
      <!-- Register Card -->
      <div class="card p-2">
        <!-- Logo -->
        <div class="app-brand justify-content-center mt-5">
          <a href="index.php" class="app-brand-link gap-2">
            <img src="assets/img/logo/logo.png" >
          </a>
        </div>
        <!-- /Logo -->
        <div class="card-body mt-2">
          <h4 class="mb-2">Kayıt Ol! 🚀</h4>
          <p class="mb-4">Panele erişim için kayıt olunuz.!</p>
          <?php
          if ($_POST) {
            $KullaniciAdi = htmlspecialchars($_POST["KullaniciAdi"]);
            $Sifre = hash("sha256", "56" . $_POST["Sifre"] . "23");
            $ReSifre = hash("sha256", "56" . $_POST["SifreTekrar"] . "23");
            $Verify = htmlspecialchars($_POST["2FA"]);
            $Isim = htmlspecialchars($_POST["Isim"]);
            $Eposta = htmlspecialchars($_POST["Eposta"]);
            $Telefon = htmlspecialchars($_POST["Telefon"]);
            $date = date('Y-m-d H:i:s');
            $query = $conn->prepare("SELECT * FROM kullanicilar");
            $query->execute();
            $row = $query->fetch();

            if (empty($KullaniciAdi) || empty($Sifre) || empty($Verify) || empty($Isim) || empty($Eposta) || empty($ReSifre)) {
              echo '<script type="text/javascript" src="assets/js/sweet-alert/sweetalert2.all.min.js"></script>';
              echo "<script> Swal.fire({title:'Hata!', text:'Tüm alanları eksiksiz doldurunuz.', icon:'error', confirmButtonText:'Kapat'})</script>";
            }
            else {
              if($Eposta == $row["Eposta"] || $KullaniciAdi == $row["KullaniciAdi"]){
                echo '<script type="text/javascript" src="assets/js/sweet-alert/sweetalert2.all.min.js"></script>';
                echo "<script> Swal.fire({title:'Hata!', text:'Kullanıcı adı ve şifre sistemde kayıtlı.', icon:'error', confirmButtonText:'Kapat'});</script>";
              }
              else{
                if ($Sifre != $ReSifre)
                {
                  echo '<script type="text/javascript" src="assets/js/sweet-alert/sweetalert2.all.min.js"></script>';
                  echo "<script> Swal.fire({title:'Hata!', text:'Şifreler Uyuşmuyor', icon:'error', confirmButtonText:'Kapat'});</script>";
                }
                else{
                  if (!filter_var($Eposta, FILTER_VALIDATE_EMAIL)) {
                    echo '<script type="text/javascript" src="assets/js/sweet-alert/sweetalert2.all.min.js"></script>';
                    echo "<script> Swal.fire({title:'Hata!', text:'Eposta Hatalı ', icon:'error', confirmButtonText:'Kapat'});</script>";
                  }
                  elseif (!empty($Telefon) && // Telefon alanı boş değilse
                      strlen($Telefon) == 10 && // Telefon numarası 10 hane mi?
                      substr($Telefon, 0, 1) == '5'
                  ) {
                    $updatequery = $conn->prepare("INSERT INTO kullanicilar (KullaniciAdi, Sifre, 2FA, Isim, Eposta,SonGiris,Telefon) VALUES (:KullaniciAdi, :Sifre, :2FA, :Isim, :Eposta,:SonGiris,:Telefon)");
                    $update = $updatequery->execute([
                        'KullaniciAdi' => $KullaniciAdi,
                        'Sifre' => $Sifre,
                        '2FA' => $Verify,
                        'Isim' => $Isim,
                        'Eposta' => $Eposta,
                        'Telefon' => $Telefon,
                        'SonGiris' => $date,
                    ]);
                    header("Location: logout.php");
                    exit();
                  }
                  else {
                    echo '<script type="text/javascript" src="assets/js/sweet-alert/sweetalert2.all.min.js"></script>';
                    echo "<script> Swal.fire({title:'Hata!', text:'Telefon Numarası Hatalı', icon:'error', confirmButtonText:'Kapat'})</script>";
                  }
                }
              }
            }
          }
          ?>
          <form id="formAuthentication" class="mb-3" method="post"  action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-floating form-floating-outline mb-3">
              <input type="text" class="form-control" id="Isim"  name="Isim" placeholder="Isim" value="<?= @$Isim ?>" required/>
              <label for="Isim">Isim</label>
            </div>
            <div class="form-floating form-floating-outline mb-3">
              <input type="text" class="form-control" id="KullaniciAdi"  name="KullaniciAdi" placeholder="KullaniciAdi" value="<?= @$KullaniciAdi ?>" required/>
              <label for="KullaniciAdi">Kullanici Adi</label>
            </div>
            <div class="form-floating form-floating-outline mb-3">
              <input type="text" class="form-control" id="Eposta"  name="Eposta" placeholder="Eposta" value="<?= @$Eposta ?>" required/>
              <label for="Eposta">Eposta</label>
            </div>
            <div class="form-floating form-floating-outline mb-3">
              <div class="input-group input-group-merge">
                <span class="input-group-text">TR (+90)</span>
                <div class="form-floating form-floating-outline">
                  <input value="<?= @$Telefon ?>" type="text" id="Telefon" name="Telefon" class="form-control phone-number-mask" placeholder="535xxxxxxxx" required oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10" onkeypress="return event.charCode >= 48 && event.charCode <= 57"/>
                  <label for="Telefon">Telefon</label>
                </div>
              </div>
            </div>
            <div class="form-floating form-floating-outline mb-3">
              <select name="2FA" id="2FA" class="form-select" required>
                <option value="2">SMS</option>
                <option value="1">Eposta</option>
                <option value="3">Pasif</option>
              </select>
              <label for="2FA">2FA Doğrulama</label>
            </div>
            <div class="form-floating form-floating-outline mb-3">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
              <input type="password" class="form-control" id="Sifre"  name="Sifre" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="Sifre"  required/>
              <label for="Sifre">Sifre</label>
                </div>
                <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
              </div>
            </div>
            <div class="form-floating form-floating-outline mb-3">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
                  <input type="password" class="form-control" id="SifreTekrar"  name="SifreTekrar" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="SifreTekrar"  required/>
                  <label for="SifreTekrar">Şifre Tekrar</label>
                </div>
                <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
              </div>
            </div>

            <div class="mb-3">
              <br>
            </div>
            <button class="btn btn-primary d-grid w-100">Kayıt Ol</button>
          </form>

          <p class="text-center">
            <span>Hesabın varmı?</span>
            <a href="logout.php">
              <span>Giriş Yap</span>
            </a>
          </p>

        </div>
      </div>
      <!-- Register Card -->
      <img alt="mask" src="assets/img/illustrations/auth-basic-register-mask-light.png" class="authentication-image d-none d-lg-block"  data-app-light-img="illustrations/auth-basic-register-mask-light.png" data-app-dark-img="illustrations/auth-basic-register-mask-dark.png" />
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
