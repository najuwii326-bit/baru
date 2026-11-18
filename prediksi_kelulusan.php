<?php
session_start();
include 'db.php';

/* ===============================
   CEK LOGIN USER
================================ */
$is_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'];
$user_nama = $is_logged_in ? $_SESSION['user_nama'] : '';
$user_nim  = $is_logged_in ? $_SESSION['user_nim']  : '';

/* ===============================
   PRIOR PROBABILITY
   1 = Lulus Tepat Waktu
   0 = Tidak Lulus Tepat Waktu
================================ */
$prior = [
    1 => 0.8,
    0 => 0.2
];

/* ===============================
   MODEL NAIVE BAYES (SEMESTER 1–6)
   Tambahkan mean & variance untuk semester 5 dan 6
================================ */
$model = [
    1 => [
        'ip_semester_1' => ['mean' => 3.0104, 'variance' => 0.3414],
        'ip_semester_2' => ['mean' => 3.0245, 'variance' => 0.3077],
        'ip_semester_3' => ['mean' => 2.8250, 'variance' => 0.3776],
        'ip_semester_4' => ['mean' => 3.1413, 'variance' => 0.3270],
        'ip_semester_5' => ['mean' => 3.1000, 'variance' => 0.3200], // contoh nilai
        'ip_semester_6' => ['mean' => 3.1500, 'variance' => 0.3100]  // contoh nilai
    ],
    0 => [
        'ip_semester_1' => ['mean' => 2.0000, 'variance' => 0.1784],
        'ip_semester_2' => ['mean' => 2.0000, 'variance' => 0.3002],
        'ip_semester_3' => ['mean' => 2.0000, 'variance' => 0.2931],
        'ip_semester_4' => ['mean' => 2.0000, 'variance' => 0.2568],
        'ip_semester_5' => ['mean' => 2.0000, 'variance' => 0.2500], // contoh nilai
        'ip_semester_6' => ['mean' => 2.0000, 'variance' => 0.2450]  // contoh nilai
    ]
];

/* ===============================
   FUNGSI GAUSSIAN
================================ */
function gaussian($x, $mean, $variance){
    if ($variance < 0.01) $variance = 0.01;
    return (1 / sqrt(2 * pi() * $variance)) *
           exp(-pow($x - $mean, 2) / (2 * $variance));
}

/* ===============================
   RULE-BASED
================================ */
function rule_based_fail($ips){
    $rata_ipk = array_sum($ips) / count($ips);
    foreach($ips as $ip){
        if($ip <= 1.0) return true;
    }
    if($rata_ipk < 3.0) return true;
    return false;
}

/* ===============================
   PROSES PREDIKSI
================================ */
$hasil_prediksi = "";
$probabilitas_lulus = 0;
$probabilitas_tidak_lulus = 0;
$ips = [];

if(isset($_POST['prediksi'])){
    // Ambil dan validasi input untuk 6 semester
    for($i=1; $i<=6; $i++){
        $ip = floatval($_POST["ip_semester_$i"]);
        $ip = max(0, min(4, $ip)); // batasi IP antara 0–4
        $ips["ip_semester_$i"] = $ip;
    }

    if(rule_based_fail($ips)){
        $hasil_prediksi = "Tidak Lulus Tepat Waktu";
        $probabilitas_lulus = 0.0;
        $probabilitas_tidak_lulus = 1.0;
    } else {
        // Hitung log probability Naive Bayes
        $log_prob = [];
        foreach([1,0] as $kelas){
            $log_prob[$kelas] = log($prior[$kelas]);
            for($i=1; $i<=6; $i++){
                $atribut = "ip_semester_$i";
                $mean   = $model[$kelas][$atribut]['mean'];
                $var    = $model[$kelas][$atribut]['variance'];
                $likelihood = gaussian($ips[$atribut], $mean, $var);
                $likelihood = max($likelihood, 1e-6);
                $log_prob[$kelas] += log($likelihood);
            }
        }

        // Normalisasi probabilitas dengan log-sum-exp
        $max_log = max($log_prob);
        $sum_exp = 0;
        foreach($log_prob as $k => $lp){
            $log_prob[$k] = exp($lp - $max_log);
            $sum_exp += $log_prob[$k];
        }

        $probabilitas_lulus = $log_prob[1] / $sum_exp;
        $probabilitas_tidak_lulus = $log_prob[0] / $sum_exp;

        $hasil_prediksi = ($probabilitas_lulus >= $probabilitas_tidak_lulus)
            ? "Lulus Tepat Waktu"
            : "Tidak Lulus Tepat Waktu";
    }
}
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prediksi Kelulusan Mahasiswa - Naive Bayes</title>
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
            padding: 20px;
            position: relative;
            overflow-x: hidden;
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
            background-image: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=1920');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: blur(8px);
            -webkit-filter: blur(8px);
        }

        /* Dark Overlay */
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

        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        /* User Info Bar */
        .user-info-bar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 25px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideDown 0.6s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .user-info-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .user-details h3 {
            color: #1e293b;
            font-size: 1em;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .user-details p {
            color: #64748b;
            font-size: 0.85em;
        }

        .user-details .nim {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #e0e7ff;
            padding: 3px 10px;
            border-radius: 8px;
            font-size: 0.8em;
            color: #2563eb;
            font-weight: 600;
        }

        .user-actions {
            display: flex;
            gap: 10px;
        }

        .logout-btn, .admin-link {
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
            font-size: 0.9em;
        }

        .admin-link {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .admin-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }

        .logout-btn {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }

        /* Header */
        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
            padding: 20px;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .header h1 {
            font-size: 2.2em;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .header p {
            font-size: 1em;
            opacity: 0.95;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.3);
        }

        /* Content Wrapper */
        .content-wrapper {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            animation: fadeInUp 0.8s ease 0.2s backwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        <?php if($hasil_prediksi): ?>
        .content-wrapper {
            grid-template-columns: 480px 1fr;
        }
        <?php endif; ?>

        /* Card Styling */
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .input-card {
            <?php if(!$hasil_prediksi): ?>
            max-width: 550px;
            margin: 0 auto;
            <?php endif; ?>
        }

        .card h2 {
            color: #1e293b;
            margin-bottom: 24px;
            font-size: 1.5em;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
        }

        .card h2 i {
            color: #2563eb;
        }

        /* Input Group */
        .input-group {
            margin-bottom: 18px;
        }

        .input-group label {
            display: block;
            color: #334155;
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 0.9em;
        }

        .input-group label i {
            color: #2563eb;
            margin-right: 5px;
        }

        .input-group input {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95em;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
            background: white;
        }

        .input-group input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        /* Button */
        .btn-predict {
            width: 100%;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            padding: 13px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-predict:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }

        /* Result Card */
        .result-card {
            display: <?php echo $hasil_prediksi ? 'block' : 'none'; ?>;
        }

        .result-header {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.3);
        }

        .result-header h3 {
            font-size: 1.3em;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .result-status {
            font-size: 1.8em;
            font-weight: 700;
            margin-bottom: 18px;
        }

        .probability-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .prob-box {
            background: rgba(255, 255, 255, 0.2);
            padding: 14px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .prob-box .label {
            font-size: 0.85em;
            margin-bottom: 6px;
            opacity: 0.95;
        }

        .prob-box .value {
            font-size: 1.6em;
            font-weight: 700;
        }

        .prob-box small {
            font-size: 0.75em;
            opacity: 0.8;
        }

        /* Info Note */
        .info-note {
            background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
            border-left: 4px solid #2563eb;
            padding: 16px;
            border-radius: 10px;
            margin-top: 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .info-note i {
            font-size: 20px;
            color: #2563eb;
            margin-top: 2px;
        }

        .info-note p {
            margin: 0;
            color: #1e293b;
            line-height: 1.6;
            text-align: left;
            font-size: 0.9em;
        }

        /* Stats Info */
        .stats-info {
            background: #f8fafc;
            padding: 18px;
            border-radius: 10px;
            margin-top: 16px;
            border: 1px solid #e2e8f0;
        }

        .stats-info h4 {
            color: #1e293b;
            margin-bottom: 14px;
            font-size: 1.1em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stats-info h4 i {
            color: #2563eb;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 12px;
        }

        .stat-item {
            background: white;
            padding: 14px;
            border-radius: 8px;
            border-left: 3px solid #2563eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .stat-item .stat-label {
            font-size: 0.8em;
            color: #64748b;
            margin-bottom: 6px;
        }

        .stat-item .stat-value {
            font-size: 1.4em;
            font-weight: 700;
            color: #1e293b;
        }

        /* Login Prompt */
        .login-prompt {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            max-width: 500px;
            margin: 50px auto;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: fadeInScale 0.6s ease;
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

        .login-prompt i {
            font-size: 3.5em;
            color: #2563eb;
            margin-bottom: 20px;
        }

        .login-prompt h2 {
            color: #1e293b;
            margin-bottom: 12px;
            font-size: 1.8em;
        }

        .login-prompt p {
            color: #64748b;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .btn-login {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .content-wrapper {
                grid-template-columns: 1fr !important;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            .header h1 {
                font-size: 1.6em;
                flex-direction: column;
            }

            .header p {
                font-size: 0.9em;
            }
            
            .probability-container {
                grid-template-columns: 1fr;
            }
            
            .user-info-bar {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }
            
            .user-info-content {
                width: 100%;
                justify-content: center;
            }

            .user-actions {
                width: 100%;
                justify-content: center;
            }

            .card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <?php if($is_logged_in): ?>
            <!-- User Info Bar -->
            <div class="user-info-bar">
                <div class="user-info-content">
                    <div class="user-avatar">
                        <?php 
                        // Ambil inisial nama (huruf pertama dari nama depan dan belakang)
                        $nama_parts = explode(' ', $user_nama);
                        $inisial = strtoupper(substr($nama_parts[0], 0, 1));
                        if(count($nama_parts) > 1) {
                            $inisial .= strtoupper(substr($nama_parts[count($nama_parts)-1], 0, 1));
                        }
                        echo $inisial;
                        ?>
                    </div>
                    <div class="user-details">
                        <h3><i class="fas fa-user"></i> <?= htmlspecialchars($user_nama) ?></h3>
                        <p>
                            <span class="nim">
                                <i class="fas fa-id-card"></i> NIM: <?= htmlspecialchars($user_nim) ?>
                            </span>
                        </p>
                    </div>
                </div>
                <div class="user-actions">
                    <a href="login.php" class="admin-link">
                        <i class="fas fa-user-shield"></i> Admin Panel
                    </a>
                    <a href="user_login.php?logout" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <div class="header">
                <h1><i class="fas fa-graduation-cap"></i> Prediksi Kelulusan Mahasiswa</h1>
                <p>Sistem Prediksi menggunakan Metode Naive Bayes dengan Distribusi Gaussian</p>
            </div>

            <div class="content-wrapper">
                <div class="card input-card">
                    <h2><i class="fas fa-edit"></i> Input Data Mahasiswa</h2>
                    <form method="POST">
                        <?php for($i=1; $i<=6; $i++): ?>
                            <div class="input-group">
                                <label for="ip_semester_<?= $i ?>">
                                    <i class="fas fa-book"></i> IP Semester <?= $i ?>
                                </label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    max="4" 
                                    name="ip_semester_<?= $i ?>" 
                                    id="ip_semester_<?= $i ?>"
                                    placeholder="Masukkan IP Semester <?= $i ?> (0.00 - 4.00)" 
                                    value="<?= isset($ips["ip_semester_$i"]) ? $ips["ip_semester_$i"] : '' ?>"
                                    required>
                            </div>
                        <?php endfor; ?>
                        <button type="submit" name="prediksi" class="btn-predict">
                            <i class="fas fa-calculator"></i> Hitung Prediksi
                        </button>
                    </form>
                </div>

                <?php if($hasil_prediksi): ?>
                <div class="card result-card">
                    <div class="result-header">
                        <h3><i class="fas fa-chart-line"></i> Hasil Prediksi</h3>
                        <div class="result-status">
                            <?php if($probabilitas_lulus > $probabilitas_tidak_lulus): ?>
                                <i class="fas fa-check-circle"></i> <?= $hasil_prediksi ?>
                            <?php else: ?>
                                <i class="fas fa-exclamation-circle"></i> <?= $hasil_prediksi ?>
                            <?php endif; ?>
                        </div>
                        <div class="probability-container">
                            <div class="prob-box">
                                <div class="label">Probabilitas Lulus</div>
                                <div class="value"><?= number_format($probabilitas_lulus * 100, 2) ?>%</div>
                                <small><?= number_format($probabilitas_lulus, 6) ?></small>
                            </div>
                            <div class="prob-box">
                                <div class="label">Probabilitas Tidak Lulus</div>
                                <div class="value"><?= number_format($probabilitas_tidak_lulus * 100, 2) ?>%</div>
                                <small><?= number_format($probabilitas_tidak_lulus, 6) ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="info-note">
                        <i class="fas fa-info-circle"></i>
                        <p>Sistem ini menggunakan metode <strong>Gaussian Naive Bayes</strong> untuk memprediksi kelulusan mahasiswa berdasarkan data IP dari 4 semester. Model dilatih dengan 100 data mahasiswa (80 Lulus, 20 Tidak Lulus).</p>
                    </div>

                    <div class="stats-info">
                        <h4><i class="fas fa-chart-bar"></i> Informasi Data Input</h4>
                        <div class="stats-grid">
                            <?php 
                            $total_ip = 0;
                            foreach($ips as $ip){
                                $total_ip += $ip;
                            }
                            $rata_ipk = $total_ip / 6;
                            ?>
                            <div class="stat-item">
                                <div class="stat-label">Rata-rata IPK</div>
                                <div class="stat-value"><?= number_format($rata_ipk, 2) ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">IP Tertinggi</div>
                                <div class="stat-value"><?= number_format(max($ips), 2) ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">IP Terendah</div>
                                <div class="stat-value"><?= number_format(min($ips), 2) ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Tingkat Keyakinan</div>
                                <div class="stat-value"><?= number_format(max($probabilitas_lulus, $probabilitas_tidak_lulus) * 100, 1) ?>%</div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Login Prompt -->
            <div class="login-prompt">
                <i class="fas fa-lock"></i>
                <h2>Login Diperlukan</h2>
                <p>Anda harus login terlebih dahulu untuk menggunakan sistem prediksi kelulusan mahasiswa.</p>
                <a href="user_login.php" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login Sekarang
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>