<?php 
session_start();
// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['userid'])) {
    header("Location: ../index.php");
    exit();
}

// ตรวจสอบระดับผู้ใช้
$userid = $_SESSION['userid'];
$userlevel = $_SESSION['userlevel'];
if ($userlevel != 'a') {
    header("Location: ../logout.php");
    exit();
}

include('../connection.php');

// ใช้ prepared statement เพื่อป้องกัน SQL Injection
$query = "SELECT firstname, lastname, img_path FROM mable WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$uploadedImage = !empty($user['img_path']) ? '../imgs/' . htmlspecialchars($user['img_path']) : '../imgs/default.jpg';

// ดึงข้อมูลงานทั้งหมดจากฐานข้อมูล
$query = "SELECT j.job_id, m.username, j.job_title, j.job_type, j.job_subtype, j.job_description, j.start_date, j.end_date, j.start_time, j.end_time, j.created_at, j.jobs_file, m.firstname, m.lastname
          FROM jobs j
          JOIN mable m ON j.id = m.id
          ORDER BY j.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

// ฟังก์ชันสำหรับการส่งออกข้อมูลงานเป็น CSV
if (isset($_POST['export_jobs'])) {
    exportJobs($result);
}

function formatThaiDate($date) {
    $thaiMonths = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    $day = date('d', strtotime($date));
    $month = $thaiMonths[date('n', strtotime($date)) - 1];
    $year = date('Y', strtotime($date)) + 543;
    return "$day $month $year";
}

function exportJobs($result) {
    // Generate CSV file
    $filename = "ข้อมูลงานพนักงาน_" . formatThaiDate(date("Y-m-d H:i:s")) . ".csv";

    // Set headers for CSV file download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    // Add BOM to fix UTF-8 in Excel
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Headers for additional information in Thai
    fputcsv($output, ["รายการงานพนักงาน"]);
    fputcsv($output, ["วันที่ส่งออกข้อมูล", formatThaiDate(date("Y-m-d H:i:s"))]);
    fputcsv($output, []);

    // Headers for job data in Thai
    fputcsv($output, ['รหัสพนักงาน', 'ชื่อ-นามสกุล', 'ชื่องาน', 'ตำแหน่งงาน', 'ประเภทงาน', 'รายละเอียดเพิ่มเติม', 'เวลาที่ส่งงาน']);

    // Fetch and output job data
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            htmlspecialchars($row['username']),
            htmlspecialchars($row['firstname']) . " " . htmlspecialchars($row['lastname']),
            htmlspecialchars($row['job_title']),
            htmlspecialchars($row['job_type']),
            htmlspecialchars($row['job_subtype']),
            htmlspecialchars($row['job_description']),
            htmlspecialchars($row['created_at'])
        ]);
    }

    // Close output stream
    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>งานทั้งหมด</title>
    <link href="../css/sidebar.css" rel="stylesheet">
    <link href="../css/popup.css" rel="stylesheet">
    <link href="../css/navbar.css" rel="stylesheet">
    <link href="https://www.ppkhosp.go.th/images/logoppk.png" rel="icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
        }
       .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }
        .table th, .table td {
            padding: 15px;
            text-align: center;
            vertical-align: middle;
            font-size: 18px; /* เพิ่มขนาดฟอนต์ */
        }
        .table th {
            background-color: #21a42e; /* Header background color */
            color: white;
        }
        .table td {
            background-color: #f8f9fa; /* Row background color */
        }
        .table td a {
            color: #FFFFFF; /* Link color */
            text-decoration: none;
        }
        .table-responsive {
            -webkit-overflow-scrolling: touch;
        }
        .search-container {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-end;
        }
        .search-container input {
            width: 300px;
            font-size: 18px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 10px;
        }
        .btn {
            font-size: 20px; /* เพิ่มขนาดฟอนต์ของปุ่ม */
        }
        .btn-detal {
            font-size: 20px;
            background-color: #1dc02b;
            color: #fff;
        }
        .btn-detal:hover {
            background: #0a840a;
            color: #fff;
        }
        .btn-detal:active {
            background: #229224 !important; /* สีปุ่มเมื่อกด */
            color: #fff !important;
        }
        /* เพิ่มการสนับสนุนสำหรับ text-size-adjust */
        body {
            -webkit-text-size-adjust: 100%;
            text-size-adjust: 100%;
            -webkit-user-select: text;
            user-select: text;
        }

        /* เพิ่มการสนับสนุนสำหรับ text-align */
        th {
            text-align: inherit;
            text-align: -webkit-match-parent;
            text-align: match-parent;
        }
        #main {
            margin-left: 0; /* Start with main content full width */
            transition: margin-left .5s;
            padding: 16px;
        }
    </style>
</head>
<body>
    <div class="navbar navbar-expand-lg navbar-dark ">
        <button class="openbtn" id="menuButton" onclick="toggleNav()">☰</button>
        <div class="container-fluid">
            <span class="navbar-brand">งานทั้งหมด</span>
        </div>
    </div>

    <div id="mySidebar" class="sidebar">
        <div class="user-info">
            <div class="circle-image">
                <img src="<?php echo $uploadedImage; ?>" alt="Uploaded Image">
            </div>
            <h1><?php echo htmlspecialchars($user['firstname']) . " " . htmlspecialchars($user['lastname']); ?></h1>
            </div>
                <a href="admin_page.php"><i class="fa-regular fa-clipboard"></i> แดชบอร์ด</a>
                <a href="emp.php"><i class="fa-solid fa-users"></i> รายชื่อพนักงานทั้งหมด</a>
                <a href="view_all_jobs.php"><i class="fa-solid fa-briefcase"></i> งานทั้งหมด</a>
                <a href="admin_assign.php"><i class="fa-solid fa-tasks"></i> สั่งงาน</a>
                <a href="admin_view_assignments.php"><i class="fa-solid fa-eye"></i> ดูงานที่สั่งแล้ว</a>
                <a href="review_assignment.php"><i class="fa-solid fa-check-circle"></i> ตรวจสอบงานที่ตอบกลับ</a>
                <a href="group_review.php"><i class="fa-solid fa-user-edit"></i>ตรวจสอบงานกลุ่มที่สั่ง</a>
                <a href="edit_profile_admin.php"><i class="fa-solid fa-user-edit"></i> แก้ไขข้อมูลส่วนตัว</a>
                <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> ออกจากระบบ</a> 
            </div>

    <div id="main">
        <div class="container table-container">
            <div class="search-container">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="ค้นหางาน...">
            </div>
            <form method="post">
                <button type="submit" name="export_jobs" class="btn btn-primary">ส่งออกเป็นรายงาน</button>
            </form>
            <table class="table table-striped mt-3" id="jobTable">
                <thead >
                    <tr>
                        <th scope="col">รหัสพนักงาน</th>
                        <th scope="col">ชื่อ-นามสกุล</th>
                        <th scope="col">ชื่องาน</th>
                        <th scope="col">ตำแหน่งงาน</th>
                        <th scope="col">ประเภทงาน</th>
                        <th scope="col">รายละเอียดเพิ่มเติม</th>
                        <th scope="col">เวลาที่ส่งงาน</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody id="jobTable">
                    <?php
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['username']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['firstname']) ." ". htmlspecialchars($row['lastname']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['job_title']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['job_type']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['job_subtype']) . '</td>';
                                echo '<td><button class="btn btn-detal btn-lg view-details" data-job-id="' . htmlspecialchars($row['job_id']) . '" class="btn btn-info btn-lg">รายละเอียดเพิ่มเติม</a></td>';
                                echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
                                echo '<td><button class="btn btn-danger btn-lg delete-job" data-job-id="' . htmlspecialchars($row['job_id']) . '">ยกเลิก</button></td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="8" class="text-center">ไม่พบงาน</td></tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="jobDetailsModal" tabindex="-1" aria-labelledby="jobDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="jobDetailsModalLabel">รายละเอียดของงาน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Job details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('jobDetailsModal'));

            document.querySelectorAll('.view-details').forEach(button => {
                button.addEventListener('click', function() {
                    const jobId = this.getAttribute('data-job-id');
                    
                    fetch(`../assignment_user.php?id=${jobId}`)
                        .then(response => response.text())
                        .then(data => {
                            document.getElementById('modalBody').innerHTML = data;
                            modal.show();
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                });
            });
        });
    </script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/check.js"></script>
    <script src="../js/delete.js"></script>
    <script src="../js/searchjob.js"></script>
</body>
</html>

<?php
mysqli_close($conn);
?>
