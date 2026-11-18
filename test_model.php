<?php
session_start();
include 'db.php';

/* ===============================
   CEK LOGIN ADMIN
================================ */
if(!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']){
    header('Location: admin.php');
    exit;
}

/* ===============================
   CEK TABEL TESTING
================================ */
$table_exists = $conn->query("SHOW TABLES LIKE 'data_testing'")->num_rows > 0;
if(!$table_exists){
    header('Location: split_data.php?msg=no_testing_data');
    exit;
}

/* ===============================
   AMBIL MODEL NAIVE BAYES
   (HANYA SEMESTER 1–4)
================================ */
$model = [];
$result = $conn->query("SELECT * FROM model_naive_bayes");

while($row = $result->fetch_assoc()){
    $kelas   = $row['kelas'];       // 1 = lulus, 0 = tidak
    $atribut = $row['atribut'];     // ip_semester_1 - ip_semester_4

    $model[$kelas][$atribut] = [
        'mean'     => $row['mean'],
        'variance' => $row['variance'] > 0 ? $row['variance'] : 0.1
    ];
}

/* ===============================
   HITUNG PRIOR
================================ */
$total_train = $conn->query(
    "SELECT COUNT(*) AS total FROM data_training"
)->fetch_assoc()['total'];

$total_lulus = $conn->query(
    "SELECT COUNT(*) AS lulus FROM data_training WHERE status_kelulusan = 1"
)->fetch_assoc()['lulus'];

$total_tidak = $total_train - $total_lulus;

$prior = [
    1 => $total_lulus / $total_train,
    0 => $total_tidak / $total_train
];

/* ===============================
   FUNGSI GAUSSIAN
================================ */
function gaussian($x, $mean, $variance){
    if($variance < 0.01) $variance = 0.01;
    $exponent = exp(-pow($x - $mean, 2) / (2 * $variance));
    return (1 / sqrt(2 * pi() * $variance)) * $exponent;
}

/* ===============================
   RULE-BASED CHECK
   GAGAL 2 SEMESTER AWAL
================================ */
function rule_based_fail($ips){
    return (
        $ips['ip_semester_1'] < 2.0 &&
        $ips['ip_semester_2'] < 2.0 &&
        $ips['ip_semester_3'] < 2.0 &&
        $ips['ip_semester_4'] < 2.0
    );
}

/* ===============================
   FUNGSI PREDIKSI
================================ */
function predict($ips, $model, $prior){

    // 1️⃣ RULE KERAS
    if(rule_based_fail($ips)){
        return [
            'prediksi'     => 0,
            'prob_lulus'   => 0.0,
            'prob_tidak'   => 1.0,
            'metode'       => 'Rule-Based'
        ];
    }

    // 2️⃣ NAIVE BAYES (SEMESTER 1–4)
    $log_prob = [];

    foreach([1, 0] as $kelas){
        $log_prob[$kelas] = log($prior[$kelas]);

        for($i = 1; $i <= 4; $i++){
            $atribut = "ip_semester_$i";

            if(isset($model[$kelas][$atribut])){
                $mean = $model[$kelas][$atribut]['mean'];
                $var  = $model[$kelas][$atribut]['variance'];

                $likelihood = gaussian($ips[$atribut], $mean, $var);
                $log_prob[$kelas] += log($likelihood > 0 ? $likelihood : 1e-10);
            }
        }
    }

    // NORMALISASI
    $max_log = max($log_prob);
    $exp = [];

    foreach($log_prob as $k => $v){
        $exp[$k] = exp($v - $max_log);
    }

    $sum = array_sum($exp);

    return [
        'prediksi'     => $exp[1] > $exp[0] ? 1 : 0,
        'prob_lulus'   => $exp[1] / $sum,
        'prob_tidak'   => $exp[0] / $sum,
        'metode'       => 'Naive Bayes'
    ];
}

/* ===============================
   TESTING & CONFUSION MATRIX
================================ */
$hasil_test = [];
$confusion_matrix = [
    'TP' => 0,
    'TN' => 0,
    'FP' => 0,
    'FN' => 0
];

$result = $conn->query("SELECT * FROM data_testing");

while($row = $result->fetch_assoc()){
    // Ambil IP Semester 1–4
    $ips = [];
    for($i = 1; $i <= 4; $i++){
        $ips["ip_semester_$i"] = $row["ip_semester_$i"];
    }

    $prediksi = predict($ips, $model, $prior);
    $aktual   = $row['status_kelulusan'];

    // Confusion Matrix
    if($prediksi['prediksi'] == 1 && $aktual == 1) $confusion_matrix['TP']++;
    elseif($prediksi['prediksi'] == 0 && $aktual == 0) $confusion_matrix['TN']++;
    elseif($prediksi['prediksi'] == 1 && $aktual == 0) $confusion_matrix['FP']++;
    elseif($prediksi['prediksi'] == 0 && $aktual == 1) $confusion_matrix['FN']++;

    $hasil_test[] = [
        'id'           => $row['id'],
        'ips'          => $ips,
        'aktual'       => $aktual,
        'prediksi'     => $prediksi['prediksi'],
        'prob_lulus'   => $prediksi['prob_lulus'],
        'prob_tidak'   => $prediksi['prob_tidak'],
        'metode'       => $prediksi['metode'],
        'benar'        => $prediksi['prediksi'] == $aktual
    ];
}

/* ===============================
   METRIK EVALUASI
================================ */
$total_test = count($hasil_test);
$benar = array_filter($hasil_test, fn($h) => $h['benar']);

$accuracy = $total_test > 0
    ? (count($benar) / $total_test) * 100
    : 0;

$precision = ($confusion_matrix['TP'] + $confusion_matrix['FP']) > 0
    ? $confusion_matrix['TP'] / ($confusion_matrix['TP'] + $confusion_matrix['FP']) * 100
    : 0;

$recall = ($confusion_matrix['TP'] + $confusion_matrix['FN']) > 0
    ? $confusion_matrix['TP'] / ($confusion_matrix['TP'] + $confusion_matrix['FN']) * 100
    : 0;

$f1_score = ($precision + $recall) > 0
    ? 2 * ($precision * $recall) / ($precision + $recall)
    : 0;
?>
