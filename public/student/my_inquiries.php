<?php
session_start();
require_once('../../config/db.php');

if (!isset($_SESSION['user_id'])) {
    die("Access denied!");
}

$student_id = $_SESSION['user_id'];

// Fetch inquiries + check if owner replied
$sql = "SELECT b.*, h.name AS hostel_name,
        (SELECT COUNT(*) FROM inquiry_replies r 
         WHERE r.inquiry_id = b.id AND r.sender='owner') AS owner_replied
        FROM bookings b
        JOIN hostels h ON b.hostel_id = h.hostel_id
        WHERE b.id = $student_id
        ORDER BY b.created_at DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Inquiries</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0px 2px 8px rgba(0,0,0,0.2);
        }
        .inq-item {
            padding: 15px;
            border-radius: 10px;
            background: #fafafa;
            margin-bottom: 15px;
            border-left: 5px solid #0462a1;
        }
        .link {
            display: inline-block;
            margin-top: 8px;
            background: #0462a1;
            color: white;
            padding: 7px 14px;
            border-radius: 8px;
            text-decoration: none;
        }
        .status {
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 5px;
        }
        .pending { color: orange; }
        .approved { color: green; }
        .rejected { color: red; }
    </style>
</head>
<body>

<div class="container">
    <h2>My Inquiries</h2>
    <hr><br>

    <?php 
    if (mysqli_num_rows($result) == 0) {
        echo "<p>No inquiries found.</p>";
    }

    while ($inq = mysqli_fetch_assoc($result)) { 
    ?>

        <div class="inq-item">
            <strong><?= htmlspecialchars($inq['hostel_name']) ?></strong><br>
            <span style="color:gray;"><?= $inq['created_at'] ?></span>

            <p><?= nl2br(htmlspecialchars($inq['message'])) ?></p>

            <!-- Status -->
            <span class="status <?= $inq['status'] ?>">
                <?= ucfirst($inq['status']) ?>
            </span>

            <br><br>

            <!-- Show owner reply notice -->
            <?php if ($inq['owner_replied'] > 0): ?>
                <span style="color:green; font-weight:bold;">Owner Replied ✔</span>
            <?php else: ?>
                <span style="color:gray;">Waiting for reply…</span>
            <?php endif; ?>

            <br><br>

            <!-- Open button -->
            <a class="link" href="view_inquiry.php?id=<?= $inq['id'] ?>">Open</a>
        </div>

    <?php } ?>

</div>

</body>
</html>
