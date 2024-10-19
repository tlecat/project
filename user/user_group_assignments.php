<?php
session_start();
include('../connection.php');

// ตรวจสอบการเข้าสู่ระบบและระดับผู้ใช้
if (!isset($_SESSION['userid']) || $_SESSION['userlevel'] != 'm') {
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


// ดึงข้อมูลงานกลุ่มที่ผู้ใช้งานได้รับมอบหมาย
$query = "
    SELECT ga.group_id, ga.job_title, ga.job_description, ga.due_date, ga.due_time, ga.file_path, gu.status
    FROM group_assignments ga
    JOIN group_users gu ON ga.group_id = gu.group_id
    WHERE gu.user_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>งานกลุ่มที่ได้รับ</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <div class="navbar navbar-expand-lg navbar-dark">
        <button class="openbtn" id="menuButton" onclick="toggleNav()">☰</button>
        <div class="container-fluid">
            <span class="navbar-brand">งานกลุ่มที่ได้รับ</span>
        </div>
    </div>

    <div id="mySidebar" class="sidebar">
        <div class="user-info">
            <h1><?php echo htmlspecialchars($user['firstname']) . " " . htmlspecialchars($user['lastname']); ?></h1>
        </div>
        <a href="user_page.php"><i class="fa-regular fa-clipboard"></i> แดชบอร์ด</a>
        <a href="user_group_assignments.php"><i class="fa-solid fa-users"></i> งานกลุ่มที่ได้รับ</a>
        <a href="user_completed.php"><i class="fa-solid fa-check-circle"></i> งานที่ส่งแล้ว</a>
        <a href="edit_profile_page.php"><i class="fa-solid fa-user-edit"></i> แก้ไขข้อมูลส่วนตัว</a>
        <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> ออกจากระบบ</a>
    </div>

    <div id="main">
        <div class="container">
            <h2>งานกลุ่มที่ได้รับ</h2>
            <table>
                <thead>
                    <tr>
                        <th>ชื่องาน</th>
                        <th>รายละเอียด</th>
                        <th>กำหนดส่ง</th>
                        <th>สถานะ</th>
                        <th>ไฟล์แนบ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['job_title']); ?></td>
                            <td><?php echo htmlspecialchars($row['job_description']); ?></td>
                            <td><?php echo htmlspecialchars($row['due_date']) . ' ' . htmlspecialchars($row['due_time']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td>
                                <?php if (!empty($row['file_path'])) { ?>
                                    <a href="../upload/<?php echo htmlspecialchars($row['file_path']); ?>" download>ดาวน์โหลด</a>
                                <?php } else { ?>
                                    ไม่มีไฟล์แนบ
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../js/sidebar.js"></script>
</body>
</html>

<?php
$stmt->close();
?>
