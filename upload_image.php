<?php
session_start();
include('./connection.php');

$userid = $_SESSION['userid'];
$userlevel = $_SESSION['userlevel']; // ตรวจสอบว่ามีการเก็บค่า userlevel หรือยัง

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['img_file'])) {
    $target_dir = "./imgs/";
    $target_file = $target_dir . basename($_FILES["img_file"]["name"]);
    
    if (move_uploaded_file($_FILES["img_file"]["tmp_name"], $target_file)) {
        $img_file = basename($_FILES["img_file"]["name"]);
        $query = "UPDATE mable SET img_path='$img_file' WHERE id='$userid'";
        
        if (mysqli_query($conn, $query)) {
            // ตรวจสอบระดับผู้ใช้
            if ($userlevel == 'a') {
                header("Location: ./admin/edit_profile_admin.php"); // หากเป็น admin ให้ไปที่หน้า admin
            } else if ($userlevel == 'm') {
                header("Location: ./user/edit_profile_page.php"); // หากเป็น user ให้ไปที่หน้า user
            }
            exit();
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }
    } else {
        echo "Error uploading image.";
    }
}

?>
