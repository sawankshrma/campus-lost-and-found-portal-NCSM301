<?php
require "db.php";

$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;
$status = isset($_POST["status"]) ? trim($_POST["status"]) : "";

$allowed = ["Open", "Claim Requested", "Resolved"];

if ($id <= 0 || !in_array($status, $allowed)) {
    echo "Invalid request.";
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE reports SET status = :status WHERE id = :id");
    $stmt->execute([":status" => $status, ":id" => $id]);
    echo "Status updated to $status.";
} catch (PDOException $e) {
    echo "Could not update status.";
}
?>
