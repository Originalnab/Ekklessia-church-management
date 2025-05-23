<?php
header('Content-Type: application/json');
include "../../../config/db.php";

if (isset($_GET['member_id'])) {
    $member_id = $_GET['member_id'];
    $stmt = $pdo->prepare("SELECT temp_username, temp_password FROM temp_credentials WHERE member_id = ?");
    $stmt->execute([$member_id]);
    $credentials = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($credentials ? $credentials : ['error' => 'Credentials not found']);
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>