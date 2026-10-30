<?php
require "db.php";

$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;
$status = isset($_POST["status"]) ? trim($_POST["status"]) : "";
$email = isset($_POST["email"]) ? trim($_POST["email"]) : "";

$allowed = ["Open", "Claim Requested", "Resolved"];

if ($id <= 0 || !in_array($status, $allowed)) {
    echo "Invalid request.";
    exit;
}

if ($status === "Resolved" && $email === "") {
    echo "Reporter email is required to resolve this report.";
    exit;
}

try {
    if ($status === "Resolved") {
        $stmt = $pdo->prepare(
            "UPDATE reports
             SET status = :status
             WHERE id = :id
               AND status = 'Claim Requested'
               AND LOWER(TRIM(reporter_email)) = LOWER(TRIM(:email))"
        );
        $stmt->execute([
            ":status" => $status,
            ":id" => $id,
            ":email" => $email,
        ]);

        if ($stmt->rowCount() !== 1) {
            echo "Could not resolve report. The email does not match the reporter's email.";
            exit;
        }
    } else {
        $stmt = $pdo->prepare("UPDATE reports SET status = :status WHERE id = :id");
        $stmt->execute([":status" => $status, ":id" => $id]);
    }

    echo "Status updated to $status.";
} catch (PDOException $e) {
    echo "Could not update status.";
}
?>
