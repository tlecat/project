<?php
session_start();
include('./connection.php');

$userid = $_SESSION['userid'];
$userlevel = $_SESSION['userlevel'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $nametitle = mysqli_real_escape_string($conn, trim($_POST['nametitle']));
    $firstname = mysqli_real_escape_string($conn, trim($_POST['firstname']));
    $lastname = mysqli_real_escape_string($conn, trim($_POST['lastname']));
    $position = mysqli_real_escape_string($conn, trim($_POST['position']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));

    // Validate phone number
    if (!preg_match('/^\d{10}$/', $phone)) {
        echo "Invalid phone number";
        exit();
    }

    if (!empty($_FILES['img_file']['name'])) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($_FILES['img_file']['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types)) {
            echo "Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.";
            exit();
        }

        if ($_FILES['img_file']['size'] > 2 * 1024 * 1024) { // Limit file size to 2MB
            echo "File is too large. Maximum file size is 2MB.";
            exit();
        }
    } else {
        $query = "UPDATE mable SET nametitle='$nametitle', firstname='$firstname', lastname='$lastname', position='$position', phone='$phone', email='$email' WHERE id='$userid'";
    }

    // Execute update query
    if (mysqli_query($conn, $query)) {
        $_SESSION['firstname'] = $firstname;
        if ($userlevel == 'a') {
        header("Location: ./admin/admin_page.php"); // เปลี่ยน 'user_page.php' เป็นชื่อไฟล์หน้า user ของคุณ
        } else 
        header("location: ./user/user_page.php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }                                                                           
}
?>
