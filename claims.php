<?php
require "db.php";

header("Content-Type: application/json; charset=utf-8");

$reportId = isset($_GET["report_id"]) ? (int) $_GET["report_id"] : 0;

if ($reportId <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid report ID.",
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "SELECT claimer_name, claimer_email, message, created_at
         FROM claims
         WHERE report_id = :report_id
         ORDER BY created_at DESC"
    );
    $stmt->execute([":report_id" => $reportId]);

    echo json_encode([
        "success" => true,
        "claims" => $stmt->fetchAll(),
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Could not load claims.",
    ]);
}
?>
