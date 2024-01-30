<?php
require_once "header.php";
error_reporting(0);
ini_set('display_errors', 0);
?>

  <div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4"><a href="index.php" class="text-muted fw-light">Anasayfa /</a> <a href="index.php">Müşteriler</a> </h4>
    <!-- Multilingual -->
    <div class="row">
      <div class="col-sm-12">
        <div class="card">
          <div class="card-header pb-0 card-no-border d-flex align-items-center justify-content-between">
            <a href="index.php" class="btn btn-primary mb-3">Geri Dön</a>&nbsp;
            <?php
            if (isset($_GET['id'])) {
              $customerID = $_GET['id']; // Get the "id" parameter from the URL
              $firmabsd = $conn->prepare("SELECT SUM(HareketBakiye) AS toplam_bakiye FROM carihareket WHERE customerid = :customer_id");
              $firmabsd->bindParam(':customer_id', $customerID);
              $firmabsd->execute();
              $firmafbsd = $firmabsd->fetch(PDO::FETCH_ASSOC);
              $firmamakiye = $firmafbsd['toplam_bakiye'];
              $customerQuery = $conn->prepare("SELECT * FROM customer WHERE id = :id;");
              $customerQuery->bindParam(':id', $customerID);
              $customerQuery->execute();

              if ($customerQuery->rowCount() > 0) {
                $customerData = $customerQuery->fetch(PDO::FETCH_ASSOC);
                $firmaAdi = $customerData["Firma"];
                $firmaTel = $customerData["FirmaTel"];
                $firmaBakiye = floatval($firmamakiye);
                $bakiyeClass = ($firmaBakiye >= 0) ? "text-success" : "text-danger";
                echo "<h3 class='d-inline'>$firmaAdi &nbsp;</h3>";

                echo "<div class='mt-3'><h3 class='d-inline &nbsp; $bakiyeClass'>Bakiye:  " . number_format($firmaBakiye, 2) . "</h3>";
                echo "<h6 class='&nbsp;'> &nbsp;  </h6></div>";
              } else {
                echo '<script type="text/javascript" src="assets/js/sweet-alert/sweetalert2.all.min.js"></script>';
                echo "<script> Swal.fire({title:'Hata!', text:'Müşteri Bulunamadı', icon:'error', confirmButtonText:'Kapat'}).then((value) => {if (value.isConfirmed){window.location.href='index.php'}});</script>";
                echo '<meta http-equiv="refresh" content="0;url=index.php">';
                exit();
              }
            } else {
              echo '<script type="text/javascript" src="assets/js/sweet-alert/sweetalert2.all.min.js"></script>';
              echo "<script> Swal.fire({title:'Hata!', text:'Müşteri Bulunamadı', icon:'error', confirmButtonText:'Kapat'}).then((value) => {if (value.isConfirmed){window.location.href='index.php'}});</script>";
              echo '<meta http-equiv="refresh" content="0;url=index.php">';
              exit();
            }
            ?>
            <div>
              <div class="btn btn-info" data-bs-toggle="modal" data-bs-target="#tahsilatyap">Tahsilat Yap</div>
              <div class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#borcekle">Borç Ekle</div>
            </div>
          </div>
          <div class="card-body">
            <div class="dt-ext table-responsive">
              <div >
                <div class="btn btn-info" data-bs-toggle="modal" data-bs-target="#excelaktar">Excele Aktar</div>
                <div class="btn btn-info" data-bs-toggle="modal" data-bs-target="#pdfolustur">Pdf Oluştur</div>
              </div>
              <br>
              <table id="example" class="display responsive nowrap" style="width:100%">
                <thead>
                <tr>
                  <th>Tarih</th>
                  <th>Açıklama</th>
                  <th>Bakiye</th>
                  <th>Hareket Tür</th>
                  <th> </th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (isset($_GET['id'])) {
                  $query = $conn->prepare("SELECT * FROM carihareket WHERE customerid = :customerid ORDER BY HareketTarih desc;");
                  $query->bindParam(':customerid', $customerID);
                  $query->execute();
                  $getcustomer = $_GET['id'];
                  if ($query->rowCount() > 0) {
                    // Fill the table with data
                    foreach ($query as $row) {
                      $dateFromMySQL = $row["HareketTarih"];
                      $formattedDate = date('d.m.Y', strtotime($dateFromMySQL));
                      echo '<tr>';
                      echo "<td><span style='font-weight:bold;' >". $formattedDate ."</td>";
                      echo "<td><span style='font-weight:bold;' > " . $row["HareketAciklama"] . "</span> </td>";

                      $hareketBakiye = floatval($row["HareketBakiye"]);
                      $bakiyeClass = ($hareketBakiye >= 0) ? "text-success" : "text-danger";
                      echo "<td><span style='font-weight:bold;' class='$bakiyeClass'>" . number_format($hareketBakiye, 2) . "</span></td>";

                      echo "<td><span style='font-weight:bold;' > " . $row["HareketTur"] . "</span> </td>";
                      echo "<td><a href='del_gelirgider.php?getcustomer=". $getcustomer ."&caridelete=". $row['id']."' class='delete-btn btn btn-danger'>Sil</a></td>";

                      echo "</tr>";
                    }
                  } else {
                    echo "<tr><td colspan='4'>Hareket bulunamadı.</td></tr>";
                  }
                } else {
                  echo '<script type="text/javascript" src="assets/js/sweet-alert/sweetalert2.all.min.js"></script>';
                  echo "<script> Swal.fire({title:'Hata!', text:'Müşteri Bulunamadı', icon:'error', confirmButtonText:'Kapat'}).then((value) => {if (value.isConfirmed){window.location.href='index.php'}});</script>";
                  echo '<meta http-equiv="refresh" content="0;url=index.php">';
                  exit();
                }
                ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <input style="display:none;" name="firmabilgi" value="<?= $customerData['Firma'] ?>">
        <input style="display:none;" name="customerid" value="<?= $_GET['id']?>" >
        <div class="modal fade" id="excelaktar" tabindex="-1" aria-labelledby="excelaktar" style="display: none;" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <form id="excelaktar" method="POST"> <!-- Added ID and method="POST" -->
                <div class="modal-body">
                  <div class="modal-toggle-wrapper">
                    <div class="col">
                      <div class="card-header">
                        <h1>Tarih Filtrele</h1>
                      </div>
                      <div class="card-body card-wrapper">
                        <div class="mb-3 row">
                          <label class="col-md-3 col-form-label">Başlangıç Tarihi</label>
                          <div class="col-md-9">
                            <input class="form-control digits" name="baslangictarihi" type="date" value="<?= date('Y-m-01'); ?>">
                          </div>
                        </div>
                        <div class="mb-3 row">
                          <label class="col-md-3 col-form-label">Bitiş Tarihi</label>
                          <div class="col-md-9">
                            <input class="form-control digits" name="bitistarihi" type="date" value="<?= date('Y-m-t') ?>">
                          </div>
                        </div>
                      </div>
                      <button class="btn bg-primary d-flex align-items-center gap-2 text-light ms-auto" type="button" onclick="downloadExcel()">Excel İndir</button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="modal fade" id="pdfolustur" tabindex="-1" aria-labelledby="pdfolustur" style="display: none;" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <form id="pdfolustur" method="POST"> <!-- Added ID and method="POST" -->
                <div class="modal-body">
                  <div class="modal-toggle-wrapper">
                    <div class="col">
                      <div class="card-header">
                        <h1>Tarih Filtrele</h1>
                      </div>
                      <div class="card-body card-wrapper">
                        <div class="mb-3 row">
                          <label class="col-md-3 col-form-label">Başlangıç Tarihi</label>
                          <div class="col-md-9">
                            <input class="form-control digits" name="pdfbaslangictarihi" type="date" value="<?= date('Y-m-01'); ?>">
                          </div>
                        </div>
                        <div class="mb-3 row">
                          <label class="col-md-3 col-form-label">Bitiş Tarihi</label>
                          <div class="col-md-9">
                            <input class="form-control digits" name="pdfbitistarihi" type="date" value="<?= date('Y-m-t') ?>">
                          </div>
                        </div>
                      </div>
                      <button class="btn bg-primary d-flex align-items-center gap-2 text-light ms-auto" type="button" onclick="downloadPdf()">Pdf İndir</button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        <!-- Modal for Tahsilat Yap -->
        <div class="modal fade" id="tahsilatyap" tabindex="-1" aria-labelledby="tahsilatyap" style="display: none;" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <form id="tahsilatForm" method="POST"> <!-- Added ID and method="POST" -->
                <div class="modal-body">
                  <div class="modal-toggle-wrapper">
                    <div class="col">
                      <input style="display:none;" name="customerid" value="<?= $_GET['id']?>" >
                      <div class="card-header">
                        <h1>Tahsilat Yap</h1>
                      </div>
                      <div class="card-body card-wrapper">
                        <div class="mb-3 row">
                          <label class="col-md-3 col-form-label">Tarih</label>
                          <div class="col-md-9">
                            <input class="form-control digits" name="Tarih" type="date" value="<?= date('Y-m-d'); ?>">
                          </div>
                        </div>
                        <div class="mb-3 row">
                          <label class="col-md-3 col-form-label">Saat</label>
                          <div class="col-md-9">
                            <input class="form-control digits" name="Saat" type="time" value="<?= date('H:i'); ?>">
                          </div>
                        </div>
                        <div class="mb-3 row">
                          <label class="col-md-3 col-form-label">Bakiye</label>
                          <div class="col-md-9">
                            <div class="input-group"><span class="input-group-text">₺</span>
                              <input class="form-control" name="tHareketBakiye" type="number" aria-label="Amount (to the nearest dollar)" placeholder="0" autofocus required>
                            </div>
                          </div>
                        </div>
                        <div class="mb-3 row">
                          <label class="col-md-3 col-form-label">Açıklama</label>
                          <div class="col-md-9">
                            <input class="form-control" type="text" placeholder="Açıklama" name="HareketAciklama" value="<?php // Mevcut ay adını alın
                            $mevcutAy = date('F');
                            $turkceAy = turkceAyAdi($mevcutAy);

                            echo $turkceAy ." Ayı Tahsilatı" ; ?>">
                          </div>
                        </div>
                      </div>
                      <button class="btn bg-primary d-flex align-items-center gap-2 text-light ms-auto" type="button" data-bs-original-title="" title="" onclick="addTahsilatViaAjax()">Tahsilat Yap</button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="modal fade" id="borcekle" tabindex="-1" aria-labelledby="borcekle" style="display: none;" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <form id="borcForm" method="POST"> <!-- Added ID and method="POST" -->
                <div class="modal-body">
                  <div class="modal-toggle-wrapper">
                    <div class="col">
                      <input style="display:none;" name="customerid" value="<?= $_GET['id']?>" >
                      <div class="card-header">
                        <h1>Borç Ekle</h1>
                      </div>
                      <div class="card-body card-wrapper">
                        <div class="mb-3 row">
                          <label class="col-md-3 col-form-label">Tarih</label>
                          <div class="col-md-9">
                            <input class="form-control digits" name="Tarih" type="date" value="<?= date('Y-m-d'); ?>">
                          </div>
                        </div>
                        <div class="mb-3 row">
                          <label class="col-md-3 col-form-label">Saat</label>
                          <div class="col-md-9">
                            <input class="form-control digits" name="Saat" type="time" value="<?= date('H:i'); ?>">
                          </div>
                        </div>
                        <div class="mb-3 row">
                          <label class="col-md-3 col-form-label">Bakiye</label>
                          <div class="col-md-9">
                            <div class="input-group"><span class="input-group-text">₺</span>
                              <input class="form-control" name="bHareketBakiye" type="number" aria-label="Amount (to the nearest dollar)" placeholder="0" autofocus="" required>
                            </div>
                          </div>
                        </div>
                        <div class="mb-3 row">
                          <label class="col-md-3 col-form-label">Açıklama</label>
                          <div class="col-md-9">
                            <input class="form-control" type="text" placeholder="Açıklama" name="HareketAciklama" value="<?php // Mevcut ay adını alın
                            $mevcutAy = date('F');
                            $turkceAy = turkceAyAdi($mevcutAy);

                            echo $turkceAy ." Ayı Borcu " ; ?>">
                          </div>
                        </div>
                      </div>
                      <button class="btn bg-primary d-flex align-items-center gap-2 text-light ms-auto" type="button" data-bs-original-title="" title="" onclick="addBorcViaAjax()">Borç Ekle</button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
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
<script>
  function addTahsilatViaAjax() {
    // Get form data for Tahsilat Yap
    const form = document.getElementById("tahsilatForm");
    const formData = new FormData(form);

    // Add the operation type to the formData
    formData.append("operation", "Tahsilat");

    // Send AJAX request for Tahsilat Yap
    fetch("functions.php", {
      method: "POST",
      body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
          // Handle the response from the server for Tahsilat Yap
          // For example, show a success message or update the UI
          console.log(data);
          // Reload the page on successful data insertion
          if (data.status === "success") {
            location.reload(); // Reload the page
          }
        })
        .catch((error) => {
          // Handle any errors that occur during the AJAX request for Tahsilat Yap
          console.error("Error:", error);
          location.reload(); // Reload the page
        });
  }

  function addBorcViaAjax() {
    // Get form data for Borç Ekle
    const form = document.getElementById("borcForm");
    const formData = new FormData(form);

    // Add the operation type to the formData
    formData.append("operation", "Borc");

    // Send AJAX request for Borç Ekle
    fetch("functions.php", {
      method: "POST",
      body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
          // Handle the response from the server for Borç Ekle
          // For example, show a success message or update the UI
          console.log(data);
          // Reload the page on successful data insertion
          if (data.status === "success") {
            location.reload(); // Reload the page
          }
        })
        .catch((error) => {
          // Handle any errors that occur during the AJAX request for Borç Ekle
          console.error("Error:", error);
          location.reload(); // Reload the page
        });
  }

</script>
<script>
  // Define a variable to hold the DataTable instance
  var dataTable;

  function initializeDataTable() {
    if ($.fn.DataTable.isDataTable('#export-button')) {
      // If the DataTable is already initialized, destroy it first
      $('#export-button').DataTable().destroy();
    }

    dataTable = $('#export-button').DataTable({
      "order": [[0, "desc"]] // Sort by the first column (index 0) in descending order (latest date first)
    });
  }

  // Call the function to initialize DataTable on document ready
  $(document).ready(function() {
    initializeDataTable();
  });

  // Call the function to reinitialize DataTable after adding data dynamically
  function reinitializeDataTable() {
    initializeDataTable();
  }
</script>
<script>
  function downloadExcel() {
    const startDate = document.querySelector('input[name="baslangictarihi"]').value;
    const endDate = document.querySelector('input[name="bitistarihi"]').value;
    const title = document.querySelector('input[name="firmabilgi"]').value;
    const customerid = document.querySelector('input[name="customerid"]').value;

    // Prepare the data to be sent to the server
    const data = {
      startDate: startDate.trim() || null,
      endDate: endDate.trim() || null,
      title: title.trim() || null,
      customerid: customerid.trim() || null
    };

    // Send the form data to the server using POST method
    const form = document.createElement('form');
    form.method = 'post';
    form.action = 'inc/carihareketexcel.php';
    form.style.display = 'none';

    for (const key in data) {
      if (data.hasOwnProperty(key)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = data[key];
        form.appendChild(input);
      }
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
  }
</script>
<script>
  function downloadPdf() {
    const startDate = document.querySelector('input[name="baslangictarihi"]').value;
    const endDate = document.querySelector('input[name="bitistarihi"]').value;
    const title = document.querySelector('input[name="firmabilgi"]').value;
    const customerid = document.querySelector('input[name="customerid"]').value;

    // Prepare the data to be sent to the server
    const data = {
      startDate: startDate.trim() || null,
      endDate: endDate.trim() || null,
      title: title.trim() || null,
      customerid: customerid.trim() || null
    };

    // Send the form data to the server using POST method
    const form = document.createElement('form');
    form.method = 'post';
    form.action = 'inc/carihareketpdf.php';
    form.style.display = 'none';

    for (const key in data) {
      if (data.hasOwnProperty(key)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = data[key];
        form.appendChild(input);
      }
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
  }
</script>