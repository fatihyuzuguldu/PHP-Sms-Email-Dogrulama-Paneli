<?php
require_once("header.php");
if (isset($_GET['update']) && !empty($_GET['update'])) {
  $updateid = $_GET['update'];
  $selectQuery = "SELECT * FROM customer WHERE id = :id";
  $stmt = $conn->prepare($selectQuery);
  $stmt->execute(['id' => $updateid]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $Telefon = $row["FirmaTel"];
if ($_POST){
  if (!empty($Telefon) && // Telefon alanı boş değilse
      strlen($Telefon) == 10 && // Telefon numarası 10 hane mi?
      substr($Telefon, 0, 1) == '5'
  )
  {
    $updateQuery = "UPDATE customer SET KisaAd=:KisaAd, FirmaTur=:FirmaTur, Firma = :Firma, FirmaVergiNo = :FirmaVergiNo, FirmaAdres = :FirmaAdres, FirmaTel = :FirmaTel, FirmaEmail = :FirmaEmail WHERE id = :id";
    $stmt = $conn->prepare($updateQuery);
    $stmt->execute([
        'FirmaTur' => $_POST['FirmaTur'],
        'KisaAd' => $_POST['KisaAd'],
        'Firma' => $_POST['Firma'],
        'FirmaVergiNo' => $_POST['FirmaVergiNo'],
        'FirmaAdres' => $_POST['FirmaAdres'],
        'FirmaTel' => $_POST['FirmaTel'],
        'FirmaEmail' => $_POST['FirmaEmail'],
        'id' => $updateid
    ]);
    echo '<meta http-equiv="refresh" content="0;url=index.php">';
    exit();
  }
  else {
    echo '<script type="text/javascript" src="assets/js/sweet-alert/sweetalert2.all.min.js"></script>';
    echo "<script> Swal.fire({title:'Hata!', text:'Telefon Numarası Hatalı', icon:'error', confirmButtonText:'Kapat'})</script>";
  }
}

}

else {
    echo '<meta http-equiv="refresh" content="0;url=index.php">';
}

?>

<!-- Content -->

<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4"><a href="index.php" class="text-muted fw-light">Anasayfa /</a> <a href="index.php">Müşteriler</a> </h4>
  <!-- Multi Column with Form Separator -->
  <div class="card mb-4">
    <h2 class="card-header"><?= $row["Firma"] ?> düzenle</h2>

    <form id="editUserForm" class="card-body" action="" method="post">
      <div class="row g-3">
      <div class="col-md-3">
        <div class="form-floating form-floating-outline">
          <input type="text" id="KisaAd" name="KisaAd" class="form-control" placeholder="Kisa Ad" value="<?= $row["KisaAd"] ?>"/>
          <label for="KisaAd">#</label>
        </div>
      </div>
      <div class="col-md-9">
        <div class="form-floating form-floating-outline">
          <input type="text" id="Firma" name="Firma" class="form-control" placeholder="Firma" value="<?= $row["Firma"] ?>" required/>
          <label for="Firma">Firma</label>
        </div>
      </div>
      <div class="col-md-6">
        <div class="input-group input-group-merge">
          <span class="input-group-text">TR (+90)</span>
          <div class="form-floating form-floating-outline">
            <input type="text" id="FirmaTel" name="FirmaTel" class="form-control phone-number-mask" placeholder="535xxxxxxxx" required oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10" onkeypress="return event.charCode >= 48 && event.charCode <= 57" value="<?= $row["FirmaTel"] ?>"/>

            <label for="FirmaTel">Telefon</label>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <input type="text" id="FirmaEmail" name="FirmaEmail" class="form-control" placeholder="ornek@domain.com" value="<?= $row["FirmaEmail"] ?>"/>
          <label for="FirmaEmail">Email</label>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <input type="text" id="FirmaTur" name="FirmaTur" class="form-control" placeholder="FirmaTur" value="<?= $row["FirmaTur"] ?>" />
          <label for="FirmaTur">Firma Türü</label>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <input type="text" id="FirmaVergiNo" name="FirmaVergiNo" class="form-control modal-edit-tax-id" placeholder="19xxxxxxxxx" value="<?= $row["FirmaVergiNo"] ?>" />
          <label for="FirmaVergiNo">Vergi No</label>
        </div>
      </div>
      <div class="col-12">
        <div class="form-floating form-floating-outline">
          <input type="text" id="FirmaAdres" name="FirmaAdres" class="form-control" placeholder="FirmaAdres" value="<?= $row["FirmaAdres"] ?>" />
          <label for="FirmaAdres">Adres</label>
        </div>
      </div>
      <div class="col-12">
        &nbsp;
      </div>
      <div class="col-12 text-center">
        <button type="submit" class="btn btn-primary me-sm-3 me-1" >Güncelle</button>
      </div>
      </div>
    </form>
  </div>
</div>
<!--/ Content -->
<!-- footer start-->
<?php
require_once("footer.php");
?>

