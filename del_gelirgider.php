<?php
require_once "vt.php"; // Veritabanı bağlantısını içe aktar

if (isset($_GET["getcustomer"]) && isset($_GET["caridelete"])) {
    $getcustomer = $_GET["getcustomer"];
    $caridelete = $_GET["caridelete"];

    $query = $conn->prepare("DELETE FROM carihareket WHERE id = :id");
    $query->bindParam(":id", $caridelete, PDO::PARAM_STR); // UUID tipini PDO::PARAM_STR olarak belirtin

    if ($query->execute()) {
        header("Location: carihareket.php?id=$getcustomer");
    } else {
        echo "Hata: Cari hareket silinirken bir sorun oluştu.";
    }
}


if (isset($_GET["customerdel"])) {
    $idToDelete = $_GET["customerdel"];

    try {
        $conn->beginTransaction();

        // Önce ilgili "carihareket" verilerini silme
        $carQuery = $conn->prepare("DELETE FROM carihareket WHERE customerid = :customerid");
        $carQuery->bindParam(":customerid", $idToDelete, PDO::PARAM_STR);
        $carQuery->execute();

        // Sonra müşteri verisini silme
        $customerQuery = $conn->prepare("DELETE FROM customer WHERE id = :id");
        $customerQuery->bindParam(":id", $idToDelete, PDO::PARAM_STR);
        $customerQuery->execute();

        $conn->commit();

        header("Location: customer.php");
    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Hata: " . $e->getMessage();
    }
}

