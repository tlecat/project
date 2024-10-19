<?php
session_start();
include('../connection.php');

// ตรวจสอบการเข้าสู่ระบบและระดับผู้ใช้
$userid = $_SESSION['userid'];
$userlevel = $_SESSION['userlevel'];
if ($userlevel != 'a') {
    header("Location: ../logout.php");
    exit();
}

// ดึงข้อมูลผู้ใช้
$query = "SELECT firstname, lastname, img_path FROM mable WHERE id = '$userid'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);
$uploadedImage = !empty($user['img_path']) ? '../imgs/' . htmlspecialchars($user['img_path']) : '../imgs/default.jpg';

// ดึงข้อมูลงานกลุ่มที่ส่งกลับมา
$query = "
    SELECT ga.group_id, ga.job_title, ga.job_description, ga.due_date, ga.due_time, ga.file_path, gu.user_id, gu.status
    FROM group_assignments ga
    JOIN group_users gu ON ga.group_id = gu.group_id
    WHERE gu.status = 'review' OR gu.status = 'completed'
    ORDER BY ga.due_date ASC, ga.due_time ASC
";
$result = mysqli_query($conn, $query);
$assignment_count = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจสอบงานที่ตอบกลับ</title>
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
            background-color: #21a42e;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
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
            background: #229224 !important;
            color: #fff !important;
        }

        .btn {
            font-size: 20px;
            background-color: #1dc02b;
            color: #fff;
        }

        .btn:hover {
            background: #0a840a;
            color: #fff;
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
    </style>
    <script>
        let lastAssignmentCount = <?php echo $assignment_count; ?>;

        function checkNewAssignments() {
            fetch('./check_new_assignments.php')
                .then(response => response.json())
                .then(data => {
                    if (data.newAssignments > lastAssignmentCount) {
                        alert('มีงานใหม่ที่ได้รับ!');
                        lastAssignmentCount = data.newAssignments;
                        location.reload(); // รีเฟรชหน้าเพื่อแสดงงานใหม่
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        setInterval(checkNewAssignments, 60000); // ตรวจสอบงานใหม่ทุกๆ 60 วินาที

        // Function to open the modal and load assignment details
        function openReviewModal(groupId, userId) {
            fetch(`get_assignment_details.php?group_id=${groupId}&user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    // ตั้งค่าข้อมูลใน modal
                    document.getElementById('modalJobTitle').textContent = data.job_title;
                    document.getElementById('modalJobDescription').textContent = data.job_description;
                    document.getElementById('modalJobStatus').textContent = data.status;
                    document.getElementById('modalJobFile').innerHTML = data.file_path ? `<a href="../upload/${data.file_path}" download>ดาวน์โหลด</a>` : 'ไม่มีไฟล์แนบ';

                    // ตั้งค่า action ของฟอร์มให้ส่งไปที่ process_review.php
                    var form = document.getElementById('reviewForm');
                    form.action = `process_review.php?group_id=${groupId}&user_id=${userId}`;

                    // เปิด modal
                    var reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
                    reviewModal.show();
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</head>
<body>
    <div class="navbar navbar-expand-lg navbar-dark">
        <button class="openbtn" id="menuButton" onclick="toggleNav()">☰</button>
        <div class="container-fluid">
            <span class="navbar-brand">ตรวจสอบงานที่ตอบกลับ</span>
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
            <h2>ตรวจสอบงานกลุ่มที่สั่ง</h2>
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>ชื่องาน</th>
                        <th>รายละเอียด</th>
                        <th>กำหนดส่ง</th>
                        <th>สถานะ</th>
                        <th>ไฟล์แนบ</th>
                        <th>ผู้ใช้ที่ส่งงาน</th>
                        <th>ดำเนินการ</th>
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
                            <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                            <td>
                                <button class="btn btn-primary" onclick="openReviewModal('<?php echo $row['group_id']; ?>', '<?php echo $row['user_id']; ?>')">ตรวจสอบ</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap Modal สำหรับแสดงการตรวจสอบงาน -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reviewModalLabel">รายละเอียดงาน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="reviewForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="job_id">

                        <!-- Job Title -->
                        <div class="mb-3">
                            <label for="job_title" class="form-label">ชื่องาน</label>
                            <input type="text" class="form-control" id="modalJobTitle" name="job_title" readonly>
                        </div>

                        <!-- Job Description -->
                        <div class="mb-3">
                            <label for="job_description" class="form-label">รายละเอียดงาน</label>
                            <textarea class="form-control" id="modalJobDescription" name="job_description" rows="3" readonly></textarea>
                        </div>

                        <!-- Due Date -->
                        <div class="mb-3">
                            <label for="due_date" class="form-label">กำหนดส่งวันที่</label>
                            <input type="date" class="form-control" id="modalDueDate" name="due_date" readonly>
                        </div>

                        <!-- Due Time -->
                        <div class="mb-3">
                            <label for="due_time" class="form-label">กำหนดส่งเวลา</label>
                            <input type="time" class="form-control" id="modalDueTime" name="due_time" readonly>
                        </div>

                        <!-- Original File -->
                        <div class="mb-3">
                            <label for="original_file" class="form-label">ไฟล์ที่ส่งตอนแรก</label>
                            <div id="modalJobFile"></div>
                        </div>

                        <!-- Buttons -->
                        <button type="submit" name="action" value="approve" class="btn btn-success">อนุมัติ</button>
                        <button type="submit" name="action" value="reject" class="btn btn-danger">ตีกลับ</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/sidebar.js"></script>
</body>
</html>
