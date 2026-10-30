<?php
require "db.php";

$type = isset($_POST["type"]) ? trim($_POST["type"]) : "";
$title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
$category = isset($_POST["category"]) ? trim($_POST["category"]) : "";
$location = isset($_POST["location"]) ? trim($_POST["location"]) : "";
$date = isset($_POST["date_occurred"]) ? trim($_POST["date_occurred"]) : "";
$name = isset($_POST["reporter_name"]) ? trim($_POST["reporter_name"]) : "";
$email = isset($_POST["reporter_email"]) ? trim($_POST["reporter_email"]) : "";
$desc = isset($_POST["description"]) ? trim($_POST["description"]) : "";

if ($type === "" || $title === "" || $category === "" || $location === "" || $date === "" || $name === "" || $email === "" || $desc === "") {
    echo "All fields are required. <a href=\"index.php\">Go back</a>";
    exit;
}

$sql = "INSERT INTO reports (type, title, category, location, date_occurred, description, reporter_name, reporter_email)
        VALUES (:type, :title, :category, :location, :date, :desc, :name, :email)";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":type" => $type,
        ":title" => $title,
        ":category" => $category,
        ":location" => $location,
        ":date" => $date,
        ":desc" => $desc,
        ":name" => $name,
        ":email" => $email,
    ]);
} catch (PDOException $e) {
    echo "Could not save report. <a href=\"index.php\">Go back</a>";
    exit;
}

header("Location: index.php");
exit;
?>
