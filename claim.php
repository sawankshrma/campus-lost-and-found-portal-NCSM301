<?php
require "db.php";

$reportId = isset($_POST["report_id"]) ? (int) $_POST["report_id"] : 0;
$name = isset($_POST["claimer_name"]) ? trim($_POST["claimer_name"]) : "";
$email = isset($_POST["claimer_email"]) ? trim($_POST["claimer_email"]) : "";
$message = isset($_POST["message"]) ? trim($_POST["message"]) : "";

if ($reportId <= 0 || $name === "" || $email === "" || $message === "") {
    echo "All fields are required.";
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO claims (report_id, claimer_name, claimer_email, message) VALUES (:rid, :name, :email, :msg)");
    $stmt->execute([":rid" => $reportId, ":name" => $name, ":email" => $email, ":msg" => $message]);

    $pdo->prepare("UPDATE reports SET status = 'Claim Requested' WHERE id = :id AND status = 'Open'")->execute([":id" => $reportId]);

    echo "Claim submitted successfully.";
} catch (PDOException $e) {
    echo "Could not submit claim.";
}
?>
