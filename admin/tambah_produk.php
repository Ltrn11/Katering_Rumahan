<?php include "header.php"; ?>
<?php include "sidebar.php"; ?>
<?php require "../koneksi.php"; ?>

<h3>Tambah Produk</h3>

<form method="POST" enctype="multipart/form-data" class="w-50">

    <div class="mb-3">
        <label>Nama Menu</label>
        <input type="text" name="nama" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Harga</label>
        <input type="number" name="harga" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Kategori</label>
        <select name="kategori" class="form-control">
            <option>Kue</option>
            <option>Lauk Pauk</option>
            <option>Minuman</option>
            <option>Nasi Box</option>
            <option>Snack</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Foto Menu</label>
        <input type="file" name="foto" class="form-control" required>
    </div>

    <button type="submit" name="save" class="btn btn-primary">Simpan</button>

</form>

<?php
if (isset($_POST['save'])) {

    $namaFoto = time() . "_" . $_FILES['foto']['name'];
    $temp = $_FILES['foto']['tmp_name'];

    move_uploaded_file($temp, "../uploads/menu_foto/" . $namaFoto);

    $stmt = $conn->prepare("INSERT INTO menu (nama_menu, harga, kategori, foto) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['nama'], $_POST['harga'], $_POST['kategori'], $namaFoto]);

    echo "<script>alert('Produk berhasil ditambahkan!');window.location='produk.php';</script>";
}
?>

<?php include "footer.php"; ?>
