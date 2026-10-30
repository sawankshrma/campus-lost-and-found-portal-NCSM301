<?php
require "db.php";

// handle search/filter
$where = [];
$params = [];

if (isset($_GET["search"]) && trim($_GET["search"]) !== "") {
    $s = "%" . trim($_GET["search"]) . "%";
    $where[] = "(title LIKE :s OR description LIKE :s2 OR location LIKE :s3)";
    $params[":s"] = $s;
    $params[":s2"] = $s;
    $params[":s3"] = $s;
}
if (isset($_GET["type"]) && $_GET["type"] !== "") {
    $where[] = "type = :type";
    $params[":type"] = $_GET["type"];
}
if (isset($_GET["category"]) && $_GET["category"] !== "") {
    $where[] = "category = :cat";
    $params[":cat"] = $_GET["category"];
}
if (isset($_GET["status"]) && $_GET["status"] !== "") {
    $where[] = "status = :status";
    $params[":status"] = $_GET["status"];
}

$sql = "SELECT * FROM reports";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Campus Lost &amp; Found Portal</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>

<h1>Campus Lost &amp; Found Portal</h1>

<!-- Search / Filter -->
<form method="get" action="index.php">
    <input type="text" name="search" placeholder="Search..."
           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
    <select name="type">
        <option value="">All Types</option>
        <option value="lost" <?php if(($_GET['type']??'')==='lost') echo 'selected';?>>Lost</option>
        <option value="found" <?php if(($_GET['type']??'')==='found') echo 'selected';?>>Found</option>
    </select>
    <select name="category">
        <option value="">All Categories</option>
        <?php
        $cats = ["Electronics","Documents & ID","Bags & Luggage","Books & Notes","Accessories","Personal Items","Keys","Clothing","Other"];
        foreach ($cats as $c):
        ?>
            <option value="<?php echo htmlspecialchars($c); ?>"
                <?php if(($_GET['category']??'')===$c) echo 'selected';?>>
                <?php echo htmlspecialchars($c); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select name="status">
        <option value="">All Status</option>
        <option value="Open" <?php if(($_GET['status']??'')==='Open') echo 'selected';?>>Open</option>
        <option value="Claim Requested" <?php if(($_GET['status']??'')==='Claim Requested') echo 'selected';?>>Claim Requested</option>
        <option value="Resolved" <?php if(($_GET['status']??'')==='Resolved') echo 'selected';?>>Resolved</option>
    </select>
    <button type="submit">Search</button>
    <a href="index.php">Clear</a>
</form>

<hr>

<!-- New Report Form -->
<h2>Submit a Report</h2>
<form action="add.php" method="post">
    <select name="type" required>
        <option value="">-- Lost or Found? --</option>
        <option value="lost">Lost</option>
        <option value="found">Found</option>
    </select>
    <input type="text" name="title" placeholder="Title" required>
    <select name="category" required>
        <option value="">-- Category --</option>
        <?php foreach ($cats as $c): ?>
            <option value="<?php echo htmlspecialchars($c); ?>"><?php echo htmlspecialchars($c); ?></option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="location" placeholder="Location" required>
    <input type="date" name="date_occurred" required>
    <input type="text" name="reporter_name" placeholder="Your Name" required>
    <input type="email" name="reporter_email" placeholder="Your Email" required>
    <textarea name="description" placeholder="Description" required></textarea>
    <button type="submit">Submit Report</button>
</form>

<hr>

<!-- Reports List -->
<h2>Reports (<?php echo count($reports); ?>)</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>Type</th><th>Title</th><th>Category</th><th>Location</th>
        <th>Date</th><th>Description</th><th>Reporter</th><th>Status</th><th>Actions</th>
    </tr>
    <?php foreach ($reports as $r): ?>
    <tr>
        <td><?php echo htmlspecialchars($r["type"]); ?></td>
        <td><?php echo htmlspecialchars($r["title"]); ?></td>
        <td><?php echo htmlspecialchars($r["category"]); ?></td>
        <td><?php echo htmlspecialchars($r["location"]); ?></td>
        <td><?php echo htmlspecialchars($r["date_occurred"]); ?></td>
        <td><?php echo htmlspecialchars($r["description"]); ?></td>
        <td><?php echo htmlspecialchars($r["reporter_name"]); ?></td>
        <td id="status-<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r["status"]); ?></td>
        <td>
            <?php if ($r["status"] !== "Resolved"): ?>
                <button class="claim-btn" data-id="<?php echo $r['id']; ?>">Claim</button>
            <?php endif; ?>
            <?php if ($r["status"] === "Claim Requested"): ?>
                <button class="resolve-btn" data-id="<?php echo $r['id']; ?>">Resolve</button>
            <?php endif; ?>
            <?php if ($r["status"] === "Resolved"): ?>
                -
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- Claim Modal (hidden) -->
<div id="claim-modal" style="display:none; background:rgba(0,0,0,0.5); position:fixed; top:0; left:0; width:100%; height:100%;">
    <div style="background:#fff; margin:10% auto; padding:20px; width:400px;">
        <h3>Submit Claim</h3>
        <form id="claim-form">
            <input type="hidden" id="claim-report-id" name="report_id">
            <input type="text" id="claim-name" name="claimer_name" placeholder="Your Name" required><br><br>
            <input type="email" id="claim-email" name="claimer_email" placeholder="Your Email" required><br><br>
            <textarea id="claim-message" name="message" placeholder="Why is this yours?" required></textarea><br><br>
            <button type="submit">Submit Claim</button>
            <button type="button" id="claim-cancel">Cancel</button>
        </form>
    </div>
</div>

<script>
// Claim modal
$(".claim-btn").click(function(){
    $("#claim-report-id").val($(this).data("id"));
    $("#claim-modal").show();
});
$("#claim-cancel").click(function(){
    $("#claim-modal").hide();
});

// Submit claim via AJAX
$("#claim-form").submit(function(e){
    e.preventDefault();
    $.post("claim.php", $(this).serialize(), function(resp){
        alert(resp);
        $("#claim-modal").hide();
        location.reload();
    });
});

// Resolve via AJAX (no full page reload)
$(".resolve-btn").click(function(){
    var id = $(this).data("id");
    var btn = $(this);
    var email = prompt("Only the reporter can resolve this report. Please enter the reporter's email:");
    if (!email) return;
    $.post("status.php", {id: id, status: "Resolved", email: email}, function(resp){
        if (resp.indexOf("updated") !== -1) {
            $("#status-" + id).text("Resolved");
            btn.replaceWith("-");
        }
        alert(resp);
    });
});
</script>

</body>
</html>
