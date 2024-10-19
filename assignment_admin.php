<?php
session_start();

if (!isset($_SESSION['userid'])) {
    header("Location: ./index.php");
    exit();
}

include('./connection.php');

$job_id = intval($_GET['id']);
$userid = $_SESSION['userid'];
$userlevel = $_SESSION['userlevel'];

// ปรับปรุงคำสั่ง SQL ให้รวมข้อมูลของผู้สั่งงานด้วย
$query = "SELECT a.*, m.username, m.firstname, m.lastname, 
                 admin.firstname AS admin_firstname, admin.lastname AS admin_lastname 
          FROM assignments a
          INNER JOIN mable m ON a.user_id = m.id 
          INNER JOIN mable admin ON a.admin_id = admin.id 
          WHERE a.job_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();

if ($job) {
    echo '<div class="card mt-4">';
    echo '<div class="card-header"><h2>' . htmlspecialchars($job['job_title']) . '</h2></div>';
    echo '<div class="card-body">';
    echo '<div class="row mb-2">';
    echo '<div class="col-md-6"><strong>รหัสพนักงาน:</strong> ' . htmlspecialchars($job['username']) . '</div>';
    echo '<div class="col-md-6"><strong>ชื่อ - นามสกุล:</strong> ' . htmlspecialchars($job['firstname']) . " " . htmlspecialchars($job['lastname']) . '</div>';
    echo '</div>';
    echo '<div class="row mb-2">';
    echo '<div class="col-md-6"><strong>ชื่องาน:</strong> ' . htmlspecialchars($job['job_title']) . '</div>';
    echo '<div class="col-md-6"><strong>รายละเอียดงาน:</strong> ' . htmlspecialchars($job['job_description']) . '</div>';
    echo '</div>';
    echo '<div class="row mb-2">';
    echo '<div class="col-md-6"><strong>กำหนดส่ง:</strong> ' . htmlspecialchars($job['due_date']) . '</div>';
    echo '<div class="col-md-6"><strong>เวลา:</strong> ' . htmlspecialchars($job['due_time']) . '</div>';
    echo '</div>';
    echo '<div class="row mb-2">';
    echo '<div class="col-md-6"><strong>สถานะ:</strong> ' . htmlspecialchars($job['status']) . '</div>';
    echo '<div class="col-md-6"><strong>วันที่สั่งงาน:</strong> ' . htmlspecialchars($job['created_at']) . '</div>';
    echo '</div>';
    // แสดงชื่อผู้สั่งงาน
    echo '<div class="row mb-2">';
    echo '<div class="col-md-6"><strong>ผู้สั่งงาน:</strong> ' . htmlspecialchars($job['admin_firstname']) . ' ' . htmlspecialchars($job['admin_lastname']) . '</div>';
    echo '</div>';

    // แสดงไฟล์ที่ส่งไป
    echo '<div class="col-md-12"><strong>ไฟล์งาน:</strong>';
    echo '<p>ชื่อไฟล์: ' . htmlspecialchars($job['file_path']) . '</p>';
    echo '<a href="../upload/' . htmlspecialchars($job['file_path']) . '" target="_blank" class="btn btn-info btn-lg">ดูไฟล์</a>';
    echo '</div>';

    // แสดงรายละเอียดการตอบกลับ
    if (!empty($job['text_reply']) || !empty($job['file_path'])) {
        echo '<div class="col-md-12 mt-4"><h3>รายละเอียดการตอบกลับ</h3>';
        
        if (!empty($job['text_reply'])) {
            echo '<div><strong>รายละเอียดการตอบกลับ:</strong>';
            echo '<p>' . htmlspecialchars($job['text_reply']) . '</p></div>';
        }
        
        if (!empty($job['time_reply'])) {
            echo '<div><strong>เวลาที่ตอบกลับ:</strong>';
            echo '<p>' . htmlspecialchars($job['time_reply']) . '</p></div>';
        }

        if (!empty($job['file_reply'])) {
            echo '<div><strong>ไฟล์ที่ตอบกลับ:</strong>';
            echo '<p>ชื่อไฟล์: ' . htmlspecialchars($job['file_reply']) . '</p>';
            echo '<a href="../upload/' . htmlspecialchars($job['file_reply']) . '" target="_blank" class="btn btn-success btn-lg">ดูไฟล์</a>';
            echo '</div>';
        }

        echo '</div>';
    }

    echo '</div></div>';
} else {
    echo '<p>ไม่พบรายละเอียดของงานนี้</p>';
}
?>
