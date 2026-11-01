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

$sql = "SELECT reports.*,
               (SELECT COUNT(*) FROM claims WHERE claims.report_id = reports.id) AS claim_count
        FROM reports";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();
$cats = ["Electronics", "Documents & ID", "Bags & Luggage", "Books & Notes", "Accessories", "Personal Items", "Keys", "Clothing", "Other"];
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
<p class="page-tagline">Find lost items, report found belongings, and help return them to their owners.</p>

<!-- New Report Form -->
<h2>Submit a new Report</h2>
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
<h2>Recent Reports (<?php echo count($reports); ?>)</h2>
<!-- Search / Filter -->
<form method="get" action="index.php">
    <h2>Search Reports</h2>
    <p class="search-help">Search by keyword or narrow the results using the filters below.</p>
    <div class="search-controls">
        <input type="text" name="search" placeholder="Search title, description, or location..."
               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        <select name="type">
            <option value="">All Types</option>
            <option value="lost" <?php if(($_GET['type']??'')==='lost') echo 'selected';?>>Lost</option>
            <option value="found" <?php if(($_GET['type']??'')==='found') echo 'selected';?>>Found</option>
        </select>
        <select name="category">
            <option value="">All Categories</option>
            <?php
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
    </div>
</form>

<table border="1" cellpadding="5">
    <tr>
        <th>Type</th><th>Title</th><th>Category</th><th>Location</th>
        <th>Date</th><th>Description</th><th>Reporter</th><th>Status</th><th>Actions</th>
    </tr>
    <?php foreach ($reports as $r): ?>
    <tr>
        <td class="item-type">
            <span class="type-marker" title="<?php echo $r["type"] === "lost" ? "Lost item" : "Found item"; ?>" aria-label="<?php echo $r["type"] === "lost" ? "Lost item" : "Found item"; ?>">
                <?php echo $r["type"] === "lost" ? "🔴" : "🟢"; ?>
            </span>
            <?php echo htmlspecialchars($r["type"]); ?>
        </td>
        <td><?php echo htmlspecialchars($r["title"]); ?></td>
        <td><?php echo htmlspecialchars($r["category"]); ?></td>
        <td><?php echo htmlspecialchars($r["location"]); ?></td>
        <td><?php echo htmlspecialchars($r["date_occurred"]); ?></td>
        <td><?php echo htmlspecialchars($r["description"]); ?></td>
        <td><?php echo htmlspecialchars($r["reporter_name"]); ?></td>
        <td id="status-<?php echo $r['id']; ?>" class="status-cell">
            <span class="status-text"><?php echo htmlspecialchars($r["status"]); ?></span>
            <?php if ((int) $r["claim_count"] > 0): ?>
                <button class="view-claims-btn" data-id="<?php echo $r['id']; ?>"
                        data-title="<?php echo htmlspecialchars($r["title"], ENT_QUOTES, 'UTF-8'); ?>">
                    View Claims
                </button>
            <?php endif; ?>
        </td>
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

<!-- Claims Modal (hidden) -->
<div id="claims-modal" style="display:none; background:rgba(0,0,0,0.5); position:fixed; top:0; left:0; width:100%; height:100%;">
    <div style="background:#fff; margin:10% auto; padding:20px; width:520px;">
        <h3 id="claims-modal-title">Claims</h3>
        <div id="claims-content">
            <p>Loading claims...</p>
        </div>
        <button type="button" id="claims-close">Close</button>
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

// View claims via AJAX
$(".view-claims-btn").click(function(){
    var id = $(this).data("id");
    var title = $(this).data("title");

    $("#claims-modal-title").text("Claims for " + title);
    $("#claims-content").html("<p>Loading claims...</p>");
    $("#claims-modal").show();

    $.getJSON("claims.php", {report_id: id})
        .done(function(data){
            if (!data.success || data.claims.length === 0) {
                $("#claims-content").html("<p class='no-claims'>No claims have been submitted for this report yet.</p>");
                return;
            }

            var cards = $("<div>");
            $.each(data.claims, function(_, claim){
                var card = $("<article class='claim-card'>");
                $("<h4>").text(claim.claimer_name).appendTo(card);
                $("<p>").append($("<strong>").text("Email: "), $("<span>").text(claim.claimer_email)).appendTo(card);
                $("<p>").append($("<strong>").text("Message: "), $("<span>").text(claim.message)).appendTo(card);
                $("<small>").text("Submitted: " + claim.created_at).appendTo(card);
                card.appendTo(cards);
            });
            $("#claims-content").empty().append(cards);
        })
        .fail(function(){
            $("#claims-content").html("<p class='claims-error'>Could not load claims. Please try again.</p>");
        });
});

$("#claims-close").click(function(){
    $("#claims-modal").hide();
});

$("#claims-modal").click(function(e){
    if (e.target === this) {
        $(this).hide();
    }
});

// Resolve via AJAX (no full page reload)
$(".resolve-btn").click(function(){
    var id = $(this).data("id");
    var btn = $(this);
    var email = prompt("Only the reporter can resolve this report. Please enter the reporter's email:");
    if (!email) return;
    $.post("status.php", {id: id, status: "Resolved", email: email}, function(resp){
        if (resp.indexOf("updated") !== -1) {
            $("#status-" + id + " .status-text").text("Resolved");
            btn.replaceWith("-");
        }
        alert(resp);
    });
});
</script>

</body>
</html>
