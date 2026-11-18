<?php

session_start();
include 'db.php';

// semua kode PHP lainnya, misal handle tambah/edit/hapus akun


if (isset($_POST['login'])) {
    // misalnya login berhasilN
    header("Location: kelola_akun.php");
    exit();
}


// Handle Tambah Akun
if(isset($_POST['tambah_akun'])){
    $nama = trim($_POST['nama_pengguna']);
    $nim = trim($_POST['nim']);
    $tanggal_lahir = $_POST['tanggal_lahir'];
    
    // Cek apakah NIM sudah ada
    $check = $conn->prepare("SELECT id FROM akun WHERE nim = ?");
    $check->bind_param("s", $nim);
    $check->execute();
    $result = $check->get_result();
    
    if($result->num_rows > 0){
        $error_msg = "NIM sudah terdaftar!";
    } else {
        $sql = "INSERT INTO akun (nama_pengguna, nim, tanggal_lahir) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $nama, $nim, $tanggal_lahir);
        
        if($stmt->execute()){
            $success_msg = "Akun berhasil ditambahkan!";
        } else {
            $error_msg = "Error: " . $stmt->error;
        }
    }
}

// Handle Edit Akun
if(isset($_POST['edit_akun'])){
    $id = intval($_POST['id']);
    $nama = trim($_POST['nama_pengguna']);
    $nim = trim($_POST['nim']);
    $tanggal_lahir = $_POST['tanggal_lahir'];
    
    // Cek apakah NIM sudah digunakan oleh akun lain
    $check = $conn->prepare("SELECT id FROM akun WHERE nim = ? AND id != ?");
    $check->bind_param("si", $nim, $id);
    $check->execute();
    $result = $check->get_result();
    
    if($result->num_rows > 0){
        $error_msg = "NIM sudah digunakan oleh akun lain!";
    } else {
        $sql = "UPDATE akun SET nama_pengguna = ?, nim = ?, tanggal_lahir = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nama, $nim, $tanggal_lahir, $id);
        
        if($stmt->execute()){
            $success_msg = "Akun berhasil diperbarui!";
        } else {
            $error_msg = "Error: " . $stmt->error;
        }
    }
}

// Handle Hapus Akun
if(isset($_GET['hapus'])){
    $id = intval($_GET['hapus']);
    
    if($conn->query("DELETE FROM akun WHERE id = $id")){
        $success_msg = "Akun berhasil dihapus!";
    } else {
        $error_msg = "Error menghapus akun!";
    }
}

// Handle Import CSV
if(isset($_POST['import_csv_akun'])){
    if(isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0){
        $conn->begin_transaction();
        
        try {
            $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
            
            // Lewati header
            $header = fgetcsv($file);
            
            $count_success = 0;
            $count_failed = 0;
            
            while(($row = fgetcsv($file)) !== FALSE){
                if(empty($row[0]) || empty($row[1])){
                    continue;
                }
                
                $nama = trim($row[0]);
                $nim = trim($row[1]);
                $tanggal_lahir = $row[2];
                
                // Cek apakah NIM sudah ada
                $check = $conn->prepare("SELECT id FROM akun WHERE nim = ?");
                $check->bind_param("s", $nim);
                $check->execute();
                $result = $check->get_result();
                
                if($result->num_rows > 0){
                    $count_failed++;
                    continue;
                }
                
                $sql = "INSERT INTO akun (nama_pengguna, nim, tanggal_lahir) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $nama, $nim, $tanggal_lahir);
                
                if($stmt->execute()){
                    $count_success++;
                } else {
                    $count_failed++;
                }
            }
            
            fclose($file);
            $conn->commit();
            
            $success_msg = "Import selesai! Berhasil: $count_success, Gagal/Duplikat: $count_failed";
            
        } catch(Exception $e) {
            $conn->rollback();
            if(isset($file)) fclose($file);
            $error_msg = "Error import: " . $e->getMessage();
        }
    } else {
        $error_msg = "Error upload file!";
    }
}

// Ambil semua akun
$akun_list = [];
$result = $conn->query("SELECT * FROM akun ORDER BY id ASC");
while($row = $result->fetch_assoc()){
    $akun_list[] = $row;
}

// Statistik
$total_akun = count($akun_list);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Akun Pengguna - Admin Panel</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 20px;
}

.main-container {
    max-width: 1400px;
    margin: 0 auto;
}

.header {
    background: white;
    padding: 20px 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header h1 {
    color: #667eea;
    font-size: 1.8em;
    display: flex;
    align-items: center;
    gap: 10px;
}

.nav-buttons {
    display: flex;
    gap: 10px;
}

.btn-nav {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    transition: all 0.3s;
}

.btn-nav:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-info h3 {
    font-size: 0.9em;
    color: #666;
    font-weight: 500;
}

.stat-info .value {
    font-size: 2em;
    font-weight: 700;
    color: #333;
}

.card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.card h3 {
    color: #667eea;
    margin-bottom: 20px;
    font-size: 1.5em;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    color: #333;
    font-weight: 500;
    margin-bottom: 8px;
    font-size: 0.9em;
}

.form-group input {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1em;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1em;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: 'Poppins', sans-serif;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
}

.btn-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.btn-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.table-container {
    overflow-x: auto;
    margin-top: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

table th,
table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

table tbody tr:hover {
    background: #f5f5f5;
}

.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

.action-btns {
    display: flex;
    gap: 8px;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 0.85em;
}

.file-input-wrapper {
    position: relative;
    overflow: hidden;
    display: inline-block;
}

.file-input-wrapper input[type=file] {
    position: absolute;
    left: -9999px;
}

.file-input-label {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 25px;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    transition: all 0.3s;
}

.file-input-label:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    animation: fadeIn 0.3s;
}

.modal-content {
    background: white;
    margin: 5% auto;
    padding: 30px;
    border-radius: 15px;
    width: 90%;
    max-width: 500px;
    animation: slideDown 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideDown {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 20px;
}

.close:hover {
    color: #000;
}

@media (max-width: 768px) {
    .header {
        flex-direction: column;
        gap: 15px;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
</head>
<body>
<div class="main-container">
    <div class="header">
        <h1><i class="fas fa-users"></i> Kelola Akun Pengguna</h1>
        <div class="nav-buttons">
            <a href="admin.php" class="btn-nav">
                <i class="fas fa-arrow-left"></i> Kembali ke Admin Panel
            </a>
        </div>
    </div>

    <?php if(isset($success_msg)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?= $success_msg ?></span>
        </div>
    <?php endif; ?>

    <?php if(isset($error_msg)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?= $error_msg ?>
        </div>
    <?php endif; ?>

    <!-- Statistik -->
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3>Total Akun Pengguna</h3>
            <div class="value"><?= $total_akun ?></div>
        </div>
    </div>

    <!-- Tambah Akun -->
    <div class="card">
        <h3><i class="fas fa-user-plus"></i> Tambah Akun Baru</h3>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_pengguna" placeholder="Nama Pengguna" required>
                </div>
                <div class="form-group">
                    <label>NIM</label>
                    <input type="text" name="nim" placeholder="Nomor Induk Mahasiswa" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" required>
                </div>
            </div>
            <button type="submit" name="tambah_akun" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Akun
            </button>
        </form>
    </div>

    <!-- Import CSV -->
    <div class="card">
        <h3><i class="fas fa-file-csv"></i> Import Akun dari CSV</h3>
        <p style="color: #666; margin-bottom: 15px;">
            Format CSV: Nama_Pengguna, NIM, Tanggal_Lahir (YYYY-MM-DD)
        </p>
        <form method="POST" enctype="multipart/form-data">
            <div class="action-buttons">
                <div class="file-input-wrapper">
                    <input type="file" name="csv_file" id="csv_file_akun" accept=".csv" required>
                    <label for="csv_file_akun" class="file-input-label">
                        <i class="fas fa-file-upload"></i> Pilih File CSV
                    </label>
                </div>
                <button type="submit" name="import_csv_akun" class="btn btn-success">
                    <i class="fas fa-upload"></i> Import CSV
                </button>
            </div>
        </form>
    </div>

    <!-- Daftar Akun -->
    <div class="card">
        <h3><i class="fas fa-list"></i> Daftar Akun (<?= $total_akun ?> akun)</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Pengguna</th>
                        <th>NIM</th>
                        <th>Tanggal Lahir</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($akun_list)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #999;">
                                <i class="fas fa-inbox" style="font-size: 3em; margin-bottom: 10px;"></i><br>
                                Belum ada akun pengguna
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($akun_list as $akun): ?>
                            <tr>
                                <td><strong><?= $akun['id'] ?></strong></td>
                                <td><?= htmlspecialchars($akun['nama_pengguna']) ?></td>
                                <td><strong><?= htmlspecialchars($akun['nim']) ?></strong></td>
                                <td><?= date('d/m/Y', strtotime($akun['tanggal_lahir'])) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($akun['created_at'])) ?></td>
                                <td>
                                    <div class="action-btns">
                                        <button onclick="editAkun(<?= $akun['id'] ?>, '<?= addslashes($akun['nama_pengguna']) ?>', '<?= $akun['nim'] ?>', '<?= $akun['tanggal_lahir'] ?>')" 
                                                class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="?hapus=<?= $akun['id'] ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Yakin ingin menghapus akun ini?');">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3 style="color: #667eea; margin-bottom: 20px;">
            <i class="fas fa-edit"></i> Edit Akun
        </h3>
        <form method="POST">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_pengguna" id="edit_nama" required>
            </div>
            <div class="form-group">
                <label>NIM</label>
                <input type="text" name="nim" id="edit_nim" required>
            </div>
            <div class="form-group">
                <label>Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" id="edit_tanggal" required>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" name="edit_akun" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <button type="button" onclick="closeModal()" class="btn btn-danger">
                    <i class="fas fa-times"></i> Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editAkun(id, nama, nim, tanggal) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_nim').value = nim;
    document.getElementById('edit_tanggal').value = tanggal;
    document.getElementById('editModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

// Update file input label
document.getElementById('csv_file_akun')?.addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name;
    if(fileName) {
        const label = document.querySelector('.file-input-label');
        label.innerHTML = '<i class="fas fa-file-check"></i> ' + fileName;
    }
});
</script>
</body>
</html>