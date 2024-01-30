<?php
require_once "header.php";
error_reporting(0);
ini_set('display_errors', 0);
if ($_POST) {

  // Gelen verilerin boş olup olmadığını kontrol et
  if (!empty($_POST['FirmaTel']) && !empty($_POST['Firma'])) {
    $FirmaTel = $_POST['FirmaTel'];

    // FirmaTel'nin customer tablosunda olup olmadığını kontrol et
    $checkQuery = "SELECT COUNT(*) AS count FROM customer WHERE FirmaTel = :FirmaTel";
    $stmt = $conn->prepare($checkQuery);
    $stmt->execute(['FirmaTel' => $FirmaTel]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty($FirmaTel) && // Telefon alanı boş değilse
        strlen($FirmaTel) == 10 && // Telefon numarası 10 hane mi?
        substr($FirmaTel, 0, 1) == '5'
    )
    {
      if ($row['count'] == 0) {
        // Veritabanına ekleme işlemini gerçekleştir
        $insertQuery = "INSERT INTO customer (KisaAd, FirmaTur, Firma, FirmaVergiNo, FirmaAdres, FirmaTel, FirmaEmail) 
            VALUES (:KisaAd, :FirmaTur, :Firma, :FirmaVergiNo, :FirmaAdres, :FirmaTel, :FirmaEmail)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->execute([
            'KisaAd' => $_POST['KisaAd'],
            'FirmaTur' => $_POST['FirmaTur'],
            'Firma' => $_POST['Firma'],
            'FirmaVergiNo' => $_POST['FirmaVergiNo'],
            'FirmaAdres' => $_POST['FirmaAdres'],
            'FirmaTel' => $_POST['FirmaTel'],
            'FirmaEmail' => $_POST['FirmaEmail']
        ]);
        echo '<meta http-equiv="refresh" content="0;url=index.php">';
        exit();
      } else {
        echo '<script type="text/javascript" src="assets/js/sweet-alert/sweetalert2.all.min.js"></script>';
        echo "<script> Swal.fire({title:'Hata!', text:'Bu telefon numarası zaten kayıtlı', icon:'error', confirmButtonText:'Kapat'}).then((value) => {if (value.isConfirmed){window.location.href='index.php'}});</script>";
        echo '<meta http-equiv="refresh" content="0;url=index.php">';
        exit();
      }
    }
    else {
      echo '<script type="text/javascript" src="assets/js/sweet-alert/sweetalert2.all.min.js"></script>';
      echo "<script> Swal.fire({title:'Hata!', text:'Telefon Numarası Hatalı', icon:'error', confirmButtonText:'Kapat'})</script>";
    }

  } else {
    echo '<script type="text/javascript" src="assets/js/sweet-alert/sweetalert2.all.min.js"></script>';
    echo "<script> Swal.fire({title:'Hata!', text:'Telefon ve Firma boş olamaz', icon:'error', confirmButtonText:'Kapat'}).then((value) => {if (value.isConfirmed){window.location.href='index.php'}});</script>";
    echo '<meta http-equiv="refresh" content="0;url=index.php">';
    exit();
  }
}
?>

  <div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4"><a href="index.php" class="text-muted fw-light">Anasayfa /</a> <a href="index.php">Müşteriler</a> </h4>
    <!-- Multilingual -->
    <div class="row">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h2>Müşteriler</h2>
          <div class="ms-auto">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pricingModal">Yeni Müşteri Ekle</button>
          </div>
        </div>
        <div class="card-datatable text-nowrap">
          <table id="example" class="display responsive nowrap" style="width:100%">
            <thead>
            <tr>
              <th>#</th>
              <th>Firma</th>
              <th>Tür</th>
              <th>Tel/Email</th>
              <th>Bakiye</th>
              <th>Son Hareket</th>
              <th>VergiNo/VD</th>
              <th>Adres</th>
              <th>Aksiyon</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $query = $conn->query("SELECT * FROM customer;");
            if ($query->rowCount() > 0) {

              // Verileri tabloya doldur
              foreach ($query as $row) {
                $customerID = $row["id"];

                $firmabsd = $conn->prepare("SELECT SUM(HareketBakiye) AS toplam_bakiye FROM carihareket WHERE customerid = :customer_id");
                $firmabsd->bindParam(':customer_id', $customerID);
                $firmabsd->execute();
                $firmafbsd = $firmabsd->fetch(PDO::FETCH_ASSOC);


                if (isset($firmafbsd['toplam_bakiye'])){
                  $totalbakiye = number_format($firmafbsd["toplam_bakiye"], 2, ',', '.');
                }
                else {
                  $totalbakiye = "Veri Bulunamadı";
                }

                // Fetch the customer's information from the Customer table
                $stmt = $conn->prepare("SELECT HareketTarih FROM carihareket WHERE customerid = ? ORDER BY HareketTarih desc ");
                $stmt->execute([$customerID]);
                $customerInfo = $stmt->fetch(PDO::FETCH_ASSOC);

                if (isset($customerInfo["HareketTarih"])){
                  $caritarih = $customerInfo["HareketTarih"];
                }
                else {
                  $caritarih = "Veri Bulunamadı";
                }
                echo "<tr>";
                echo "<td><span style='font-weight:bold;' >" . $row["KisaAd"] . "</td>";
                echo "<td><span style='font-weight:bold;' >" . $row["Firma"] . "</td>";
                echo "<td><span style='font-weight:bold;' >" . $row["FirmaTur"] . "</td>";
                echo "<td><span style='font-weight:bold;' ><a href='tel:0'>0" . $row["FirmaTel"] . "</a> <br> " . $row["FirmaEmail"] . "</td>";
                echo "<td><span style='font-weight:bold;' >₺" . $totalbakiye. "</span></td>";
                echo "<td><span style='font-weight:bold;' >" . $caritarih . "</td>";
                echo "<td><span style='font-weight:bold;' >" . $row["FirmaVergiNo"] . "<br> " . $row["FirmaVD"] . "</td>";
                echo "<td><span style='font-weight:bold;' >" . $row["FirmaAdres"] . "</td>";
                echo '<td style="text-align: right;">
                <div class="btn-group" role="group" aria-label="First group">
                    <a href="carihareket.php?id=' . $row["id"] . '" class="btn btn-outline-danger waves-effect">
                        <i class="mdi mdi-currency-try" style="font-size: 1.5em;"></i>
                    </a>
                    <a href="editcustomer.php?update=' . $row["id"] . '" class="btn btn-outline-primary waves-effect">
                        <i class="mdi mdi-account-edit" style="font-size: 1.5em;"></i>
                    </a>
                </div>
              </td>';
                echo "</tr>";
              }
            }
            ?>
            </tbody>

          </table>
        </div>
      </div>
    </div>
    <div class="modal fade" id="pricingModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-simple modal-edit-user">
        <div class="modal-content p-3 p-md-5">
          <div class="modal-body py-3 py-md-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="text-center mb-4">
              <h3 class="mb-2">Müşteri Ekle</h3>
              <p class="pt-1">&nbsp;</p>
            </div>
            <form id="editUserForm" class="row g-4"  action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" onsubmit="return false">
              <div class="col-12 col-md-3">
                <div class="form-floating form-floating-outline">
                  <input type="text" id="KisaAd" name="KisaAd" class="form-control" placeholder="Kisa Ad" />
                  <label for="KisaAd">#</label>
                </div>
              </div>
              <div class="col-12 col-md-9">
                <div class="form-floating form-floating-outline">
                  <input type="text" id="Firma" name="Firma" class="form-control" placeholder="Firma" required/>
                  <label for="Firma">Firma</label>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="input-group input-group-merge">
                  <span class="input-group-text">TR (+90)</span>
                  <div class="form-floating form-floating-outline">
                    <input type="text" id="FirmaTel" name="FirmaTel" class="form-control phone-number-mask" placeholder="535xxxxxxxx" required oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10" onkeypress="return event.charCode >= 48 && event.charCode <= 57"/>
                    <label for="FirmaTel">Telefon</label>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="form-floating form-floating-outline">
                  <input type="text" id="FirmaEmail" name="FirmaEmail" class="form-control" placeholder="ornek@domain.com" />
                  <label for="FirmaEmail">Email</label>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="form-floating form-floating-outline">
                  <input type="text" id="FirmaTur" name="FirmaTur" class="form-control" placeholder="FirmaTur" />
                  <label for="FirmaTur">Firma Türü</label>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="form-floating form-floating-outline">
                  <input type="text" id="FirmaVergiNo" name="FirmaVergiNo" class="form-control modal-edit-tax-id" placeholder="19xxxxxxxxx" />
                  <label for="FirmaVergiNo">Vergi No</label>
                </div>
              </div>
              <div class="col-12">
                <div class="form-floating form-floating-outline">
                  <input type="text" id="FirmaAdres" name="FirmaAdres" class="form-control" placeholder="FirmaAdres" />
                  <label for="FirmaAdres">Adres</label>
                </div>
              </div>
              <div class="col-12">
                &nbsp;
              </div>
              <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary me-sm-3 me-1" onclick="submitForm()">Ekle</button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal" aria-label="Close">İptal</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Content -->
<script>
  function submitForm() {
    // Formu submit et
    document.getElementById('editUserForm').submit();
  }
</script>
<?php
require_once "footer.php";
?>
