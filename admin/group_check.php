<?php
include('../connection.php');

$group_id = $_GET['group_id'];
$user_id = $_GET['user_id'];

$query = "
    SELECT ga.job_title, ga.job_description, ga.file_path, gu.status
    FROM group_assignments ga
    JOIN group_users gu ON ga.group_id = gu.group_id
    WHERE gu.group_id = ? AND gu.user_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $group_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$assignment = $result->fetch_assoc();

echo json_encode($assignment);
?>
