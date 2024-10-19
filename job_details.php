<?php
session_start();

if (!isset($_SESSION['userid'])) {
    header("Location: index.php");
    exit();
}

include('../connection.php');



$job_id = $_GET['id'];

$query = "SELECT * FROM jobs INNER JOIN mable ON jobs.id = mable.id WHERE jobs.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดของงาน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
        }
        .container {
            width: 100%;
        }
        .back-button {
            margin-top: 20px;
        }
        .topbar {
            font-size: 20px;
            color: white;
            text-align: center;
            background: #000;
            width: 100%;
            padding: 60px 0;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="container">
            <?php if ($job) : ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h2><?= htmlspecialchars($job['job_type']) . " - " . htmlspecialchars($job['job_subtype']) ?></h2>
                    </div>
                    <div class="card-body">
                        <p><strong>รหัสพนักงาน:</strong> <?= htmlspecialchars($job['username']) ?></p>
                        <p><strong>ชื่อ - นามสกุล:</strong> <?= htmlspecialchars($job['firstname']) . " " . htmlspecialchars($job['lastname']) ?></p>
                        <p><strong>ตำแหน่งหลัก:</strong> <?= htmlspecialchars($job['job_type']) ?></p>
                        <p><strong>ตำแหน่งย่อย:</strong> <?= htmlspecialchars($job['job_subtype']) ?></p>
                        <p><strong>วันที่เริ่มงาน:</strong> <?= htmlspecialchars($job['job_date']) ?></p>
                        <p><strong>เวลาเริ่มต้น:</strong> <?= htmlspecialchars($job['start_time']) ?></p>
                        <p><strong>เวลาสิ้นสุด:</strong> <?= htmlspecialchars($job['end_time']) ?></p>
                        <p><strong>ไฟล์งาน:</strong> <a href="upload/<?= htmlspecialchars($job['jobs_file']) ?>" target="_blank" class="btn btn-info btn-sm">ดูไฟล์</a></p>
                    </div>
                </div>
            <?php else : ?>
                <p>ไม่พบรายละเอียดของงานนี้</p>
            <?php endif; ?>
            <div class="back-button text-center">
                <button onclick="window.location.href='../view_all_jobs.php';" class="btn btn-primary">ย้อนกลับ</button>
            </div>
        </div>
    </div>
</body>
</html>
