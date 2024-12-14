<?php
session_start();
require_once '../config/database.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$success = '';
$error = '';

// Proses tambah menu baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['action'])) {
    $nama = $_POST['nama'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    // Upload gambar
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['gambar']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $newname = uniqid() . "." . $ext;
            $destination = "../assets/img/menu/" . $newname;

            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $destination)) {
                $gambar = $newname;
            }
        }
    }

    try {
        $stmt = $conn->prepare("INSERT INTO menu (nama, kategori, deskripsi, harga, stok, gambar) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nama, $kategori, $deskripsi, $harga, $stok, $gambar]);
        header("Location: menu.php?success=added");
        exit();
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Proses edit menu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $menu_id = $_POST['menu_id'];
    $nama = $_POST['nama'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    try {
        // Update data dasar
        $stmt = $conn->prepare("UPDATE menu SET nama = ?, kategori = ?, deskripsi = ?, harga = ?, stok = ? WHERE id = ?");
        $stmt->execute([$nama, $kategori, $deskripsi, $harga, $stok, $menu_id]);

        // Upload dan update gambar jika ada
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['gambar']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                // Hapus gambar lama
                $stmt = $conn->prepare("SELECT gambar FROM menu WHERE id = ?");
                $stmt->execute([$menu_id]);
                $old_image = $stmt->fetchColumn();

                if ($old_image && file_exists("../assets/img/menu/" . $old_image)) {
                    unlink("../assets/img/menu/" . $old_image);
                }

                $newname = uniqid() . "." . $ext;
                $destination = "../assets/img/menu/" . $newname;

                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $destination)) {
                    $stmt = $conn->prepare("UPDATE menu SET gambar = ? WHERE id = ?");
                    $stmt->execute([$newname, $menu_id]);
                }
            }
        }

        header("Location: menu.php?success=updated");
        exit();
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Set pesan sukses dari parameter URL
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'added':
            $success = "Menu berhasil ditambahkan!";
            break;
        case 'updated':
            $success = "Menu berhasil diperbarui!";
            break;
        case 'deleted':
            $success = "Menu berhasil dihapus!";
            break;
    }
}

// Ambil daftar menu
$stmt = $conn->query("SELECT * FROM menu ORDER BY nama");
$menus = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Menu</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMenuModal">
            <i class="fas fa-plus"></i> Tambah Menu
        </button>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card data-table">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menus as $menu): ?>
                        <tr>
                            <td>
                                <?php if ($menu['gambar']): ?>
                                    <img src="../assets/img/menu/<?= $menu['gambar'] ?>"
                                        alt="<?= $menu['nama'] ?>"
                                        style="width: 50px; height: 50px; object-fit: cover;">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($menu['nama']) ?></td>
                            <td><?= htmlspecialchars($menu['kategori']) ?></td>
                            <td>Rp <?= number_format($menu['harga'], 0, ',', '.') ?></td>
                            <td><?= $menu['stok'] ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="editMenu(<?= $menu['id'] ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteMenu(<?= $menu['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Menu -->
<div class="modal fade" id="addMenuModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Menu Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Menu</label>
                        <input type="text" class="form-control" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="kategori" required>
                            <option value="makanan">Makanan</option>
                            <option value="minuman">Minuman</option>
                            <option value="snack">Snack</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="number" class="form-control" name="harga" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stok</label>
                        <input type="number" class="form-control" name="stok" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar</label>
                        <input type="file" class="form-control" name="gambar" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Menu -->
<div class="modal fade" id="editMenuModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" id="editMenuForm">
                <input type="hidden" name="menu_id" id="edit_menu_id">
                <input type="hidden" name="action" value="edit">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Menu</label>
                        <input type="text" class="form-control" name="nama" id="edit_nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="kategori" id="edit_kategori" required>
                            <option value="makanan">Makanan</option>
                            <option value="minuman">Minuman</option>
                            <option value="snack">Snack</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="number" class="form-control" name="harga" id="edit_harga" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stok</label>
                        <input type="number" class="form-control" name="stok" id="edit_stok" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar</label>
                        <input type="file" class="form-control" name="gambar" accept="image/*">
                        <small class="text-muted">Biarkan kosong jika tidak ingin mengubah gambar</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function editMenu(id) {
        // Ambil data menu dari server
        fetch(`get_menu.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                // Isi form dengan data menu
                document.getElementById('edit_menu_id').value = data.id;
                document.getElementById('edit_nama').value = data.nama;
                document.getElementById('edit_kategori').value = data.kategori;
                document.getElementById('edit_deskripsi').value = data.deskripsi;
                document.getElementById('edit_harga').value = data.harga;
                document.getElementById('edit_stok').value = data.stok;

                // Tampilkan modal
                new bootstrap.Modal(document.getElementById('editMenuModal')).show();
            });
    }

    function deleteMenu(id) {
        if (confirm('Apakah Anda yakin ingin menghapus menu ini?')) {
            fetch('delete_menu.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Refresh halaman setelah berhasil menghapus
                        window.location.reload();
                    } else {
                        alert(data.message || 'Gagal menghapus menu');
                    }
                });
        }
    }
</script>

<?php include 'includes/footer.php'; ?>