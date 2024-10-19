<?php
session_start();
include('../connection.php');

// ตรวจสอบการเข้าสู่ระบบและระดับผู้ใช้
if (!isset($_SESSION['userid']) || $_SESSION['userlevel'] != 'a') {
    header("Location: logout.php");
    exit();
}

$userid = $_SESSION['userid'];

// ใช้ prepared statements เพื่อป้องกัน SQL Injection
$stmt = $conn->prepare("SELECT firstname, lastname, img_path FROM mable WHERE id = ?");
$stmt->bind_param("s", $userid);
$stmt->execute();
$result = $stmt->get_result();

$user = $result->fetch_assoc();
$uploadedImage = !empty($user['img_path']) ? '../imgs/' . htmlspecialchars($user['img_path']) : '../imgs/default.jpg';

// ปิด statement หลังการใช้งาน
$stmt->close();

// ดึงข้อมูลผู้ใช้งานสำหรับดรอปดาวน์แบบหลายตัวเลือก
$user_query = "SELECT id, firstname, lastname FROM mable WHERE userlevel = 'm'";
$user_result = mysqli_query($conn, $user_query);

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_ids = $_POST['user_ids']; // เป็น array ของ user_id ที่เลือกไว้
    $job_title = mysqli_real_escape_string($conn, $_POST['job_title']);
    $job_description = mysqli_real_escape_string($conn, $_POST['job_description']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    $due_time = mysqli_real_escape_string($conn, $_POST['due_time']);

    // ตรวจสอบการอัปโหลดไฟล์
    $file_name = '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file = $_FILES['file'];
        $upload_directory = '../upload/';
        $file_name = uniqid() . '_' . basename($file['name']); // สร้างชื่อไฟล์ใหม่เพื่อป้องกันการชนกันของชื่อไฟล์

        // สร้างโฟลเดอร์ถ้าไม่อยู่
        if (!is_dir($upload_directory)) {
            mkdir($upload_directory, 0777, true);
        }

        // ย้ายไฟล์ไปยังโฟลเดอร์ upload
        if (!move_uploaded_file($file['tmp_name'], $upload_directory . $file_name)) {
            die('Failed to move uploaded file.');
        }
    }

    // แทรกข้อมูลลงในตาราง group_assignments เพื่อบันทึกรายละเอียดของงานกลุ่ม
    $insert_group_query = "INSERT INTO group_assignments (job_title, job_description, due_date, due_time, file_path, admin_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_group_query);
    $stmt->bind_param("sssssi", $job_title, $job_description, $due_date, $due_time, $file_name, $userid);
    $stmt->execute();
    $group_id = $stmt->insert_id;

    // แทรกข้อมูลลงในตาราง group_users สำหรับสมาชิกในกลุ่ม
    foreach ($user_ids as $user_id) {
        $insert_user_query = "INSERT INTO group_users (group_id, user_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_user_query);
        $stmt->bind_param("ii", $group_id, $user_id);
        $stmt->execute();
    }

    // ปิด statement หลังการใช้งาน
    $stmt->close();

    // หลังจากสั่งงานเสร็จให้ redirect ไปยังหน้าที่ต้องการ
    header("Location: ./admin_view_assignments.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สั่งงานกลุ่ม</title>
    <link href="../css/sidebar.css" rel="stylesheet">
    <link href="../css/navbar.css" rel="stylesheet">
    <link href="https://www.ppkhosp.go.th/images/logoppk.png" rel="icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
        }
        .container {
            margin-top: 20px;
            overflow-x: auto;
        }

        #main {
            transition: margin-left .5s;
            padding: 16px;
            margin-left: 0;
        }
        .form-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 70vh;
            flex-direction: column;
            vertical-align: middle;
        }

        .form-box {
            width: 100%;
            max-width: 500px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-control {
            background-color: transparent;
            border: none;
            border-bottom: 2px solid #727272;
            border-radius: 0;
            color: #000;
            font-size: 16px;
        }
        .form-control:focus {
            border-bottom: 2px solid #727272;
            outline: none;
            box-shadow: none;
        }
        .form-control option {
            background-color: transparent;
            color: #000;
        }
        .form-control option:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }
        .btn {
            font-size: 18px;
            padding: 10px 20px;
            margin-top: 30px;
            border-radius: 30px;
            background-color: #1dc02b;
            color: #fff;
        }
        .btn:hover {
            background: #0a840a;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="navbar navbar-expand-lg navbar-dark">
        <button class="openbtn" id="menuButton" onclick="toggleNav()">☰</button>
        <div class="container-fluid">
            <span class="navbar-brand">สั่งงานกลุ่ม</span>
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
        <div class="form-container">
            <div class="form-box">
                <form action="group_assign.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="user_ids" class="form-label">เลือกผู้ใช้งานหลายคน</label>
                        <select class="form-control" id="user_ids" name="user_ids[]" multiple required>
                            <?php while ($user = mysqli_fetch_assoc($user_result)) { ?>
                                <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['firstname']) . ' ' . htmlspecialchars($user['lastname']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="job_title" class="form-label">ชื่องาน</label>
                        <input type="text" class="form-control" id="job_title" name="job_title" required>
                    </div>
                    <div class="mb-3">
                        <label for="job_description" class="form-label">รายละเอียดงาน</label>
                        <textarea class="form-control" id="job_description" name="job_description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="due_date" class="form-label">กำหนดส่งวันที่</label>
                        <input type="date" class="form-control" id="due_date" name="due_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="due_time" class="form-label">กำหนดส่งเวลา</label>
                        <input type="time" class="form-control" id="due_time" name="due_time" required>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label">ไฟล์แนบ (เฉพาะ PDF)</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".pdf">
                    </div>
                    <button type="submit" class="btn">สั่งงานกลุ่ม</button>
                </form>
                <!-- ปุ่มเปลี่ยนไปหน้าสั่งงานเดี่ยว -->
                <a href="admin_assign.php" class="btn btn-secondary">สั่งงานเดี่ยว</a>
            </div>
        </div>
    </div>
    <script src="../js/sidebar.js"></script>
</body>
</html>
