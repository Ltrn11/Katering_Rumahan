<?php
require "../koneksi.php";

$id = $_GET['id'];

$foto = $conn->query("SELECT foto FROM menu WHERE id_menu=$id")->fetchColumn();
unlink("../uploads/menu_foto/" . $foto);

$conn->query("DELETE FROM menu WHERE id_menu=$id");

header("Location: produk.php");
?>
