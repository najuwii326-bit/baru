<?php
session_start();
include 'db.php';

// Handle Login
if(isset($_POST['login'])){
    $nim = trim($_POST['nim']);
    $tanggal_lahir = $_POST['tanggal_lahir'];
    
    // Query untuk cek akun berdasarkan NIM dan tanggal lahir
    $sql = "SELECT * FROM akun WHERE nim = ? AND tanggal_lahir = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nim, $tanggal_lahir);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0){
        $user = $result->fetch_assoc();
        
        // Set session
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nama'] = $user['nama_pengguna'];
        $_SESSION['user_nim'] = $user['nim'];
        
        header('Location: prediksi_kelulusan.php'); // Redirect ke halaman utama
        exit;
    } else {
        $error = "NIM atau Tanggal Lahir salah!";
    }
}

// Handle Logout
if(isset($_GET['logout'])){
    session_destroy();
    header('Location: user_login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Prediksi Kelulusan Mahasiswa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Background Image dengan Blur Effect */
        body::after {
            content: '';
            position: fixed;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            z-index: -1;
            
            
            background-image: url('img/background.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: blur(5px);
            -webkit-filter: blur(8px);
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5); 
            z-index: 0;
        }

        .login-container {
            display: grid;
            grid-template-columns: 400px 440px;
            max-width: 900px;
            width: 100%;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 1;
            animation: fadeInScale 0.6s ease-out;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Left Side - Blue Section */
        .left-section {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 30px;
            position: relative;
            overflow: hidden;
        }

        .left-section::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
            top: -100px;
            left: -100px;
            animation: pulse 8s ease-in-out infinite;
        }

        .left-section::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
            border-radius: 50%;
            bottom: -80px;
            right: -80px;
            animation: pulse 6s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 0.5;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .graduation-icon {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
            border: 3px solid rgba(255, 255, 255, 0.2);
            animation: fadeInScale 0.8s ease-out;
        }

        .graduation-icon::before {
            content: '🎓';
            font-size: 50px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .left-content {
            text-align: center;
            color: white;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.8s ease-out 0.2s backwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .left-content h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 15px;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .left-content p {
            font-size: 15px;
            font-weight: 400;
            opacity: 0.95;
            line-height: 1.6;
            max-width: 320px;
        }

        /* Right Side - Login Form */
        .right-section {
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 35px;
            position: relative;
        }

        .login-form-container {
            width: 100%;
            max-width: 100%;
            animation: fadeInRight 0.8s ease-out;
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .form-header {
            margin-bottom: 30px;
        }

        .form-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .form-header p {
            font-size: 14px;
            color: #64748b;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px;
            font-size: 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            background: #ffffff;
            color: #1e293b;
        }

        .form-group input::placeholder {
            color: #94a3b8;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        /* Date input styling */
        .form-group input[type="date"] {
            cursor: pointer;
        }

        .form-group input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            color: #2563eb;
        }

        .admin-link-container {
            text-align: right;
            margin-top: 8px;
        }

        .admin-link-container a {
            font-size: 14px;
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .admin-link-container a:hover {
            color: #1e40af;
            text-decoration: underline;
        }

        .login-button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 24px;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .error-message {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .error-message::before {
            content: '⚠️';
            font-size: 18px;
        }

        /* Info Box */
        .info-box {
            background: #f0f9ff;
            border: 1px solid #bfdbfe;
            border-left: 3px solid #2563eb;
            padding: 12px 14px;
            border-radius: 6px;
            margin-top: 20px;
        }

        .info-box h4 {
            color: #1e40af;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .info-box p {
            color: #475569;
            font-size: 12px;
            line-height: 1.5;
            margin: 3px 0;
        }

        .info-box strong {
            color: #1e293b;
            font-weight: 600;
        }

        /* Responsive Design */
        @media (max-width: 900px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 440px;
            }

            .left-section {
                display: none;
            }

            .right-section {
                padding: 30px 25px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .login-container {
                border-radius: 15px;
            }

            .form-header h2 {
                font-size: 24px;
            }

            .form-header p {
                font-size: 13px;
            }

            .right-section {
                padding: 25px 20px;
            }

            .login-form-container {
                max-width: 100%;
            }
        }

        /* Loading state */
        .login-button.loading {
            opacity: 0.7;
            cursor: not-allowed;
            position: relative;
        }

        .login-button.loading::after {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: translateY(-50%) rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Section -->
        <div class="left-section">
            <div class="graduation-icon"></div>
            <div class="left-content">
                <h1>PREDIKSI<br>KELULUSAN</h1>
                <p>Sistem Untuk Memprediksi Kelulusan Mahasiswa</p>
            </div>
        </div>

        <!-- Right Section -->
        <div class="right-section">
            <div class="login-form-container">
                <div class="form-header">
                    <h2>Selamat Datang</h2>
                    <p>Silakan login ke akun Anda</p>
                </div>

                <?php if(isset($error)): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="" id="loginForm">
                    <div class="form-group">
                        <label for="nim">NIM (Nomor Induk Mahasiswa)</label>
                        <input 
                            type="text" 
                            id="nim" 
                            name="nim" 
                            placeholder="Masukkan NIM Anda" 
                            required 
                            autocomplete="username"
                            autofocus
                            value="<?= isset($_POST['nim']) ? htmlspecialchars($_POST['nim']) : '' ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="tanggal_lahir">Tanggal Lahir</label>
                        <input 
                            type="date" 
                            id="tanggal_lahir" 
                            name="tanggal_lahir" 
                            placeholder="Pilih tanggal lahir" 
                            required
                            autocomplete="bday"
                        >
                        <div class="admin-link-container">
                            <a href="login.php">
                                <i class="fas fa-user-shield"></i>
                                Login sebagai Admin
                            </a>
                        </div>
                    </div>

                    <button type="submit" name="login" class="login-button" id="loginBtn">
                        Login
                    </button>
                </form>

                <div class="info-box">
                    <h4>
                        <i class="fas fa-info-circle"></i>
                        Informasi Login
                    </h4>
                    <p>Username: <strong>NIM Anda</strong></p>
                    <p>Password: <strong>Tanggal Lahir Anda</strong></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add loading state to button on form submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.textContent = 'Memproses...';
        });

        // Clear error message on input
        const inputs = document.querySelectorAll('input');
        const errorMsg = document.querySelector('.error-message');
        
        if(errorMsg) {
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    errorMsg.style.display = 'none';
                });
            });
        }

        // Validasi input NIM (hanya angka)
        document.getElementById('nim').addEventListener('input', function(e) {
            // Hapus karakter non-angka
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Set max date untuk tanggal lahir (maksimal 17 tahun yang lalu)
        const today = new Date();
        const maxDate = new Date(today.getFullYear() - 17, today.getMonth(), today.getDate());
        const minDate = new Date(today.getFullYear() - 50, today.getMonth(), today.getDate());

        const dateInput = document.getElementById('tanggal_lahir');
        dateInput.max = maxDate.toISOString().split('T')[0];
        dateInput.min = minDate.toISOString().split('T')[0];
    </script>
</body>
</html>