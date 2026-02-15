<?php
session_start();
require_once('../../config/db.php');

// Check if inquiry ID is provided
if (!isset($_GET['inq_id'])) {
    die("Inquiry ID missing!");
}

$inq_id = intval($_GET['inq_id']);

// Fetch inquiry details
$sql = "SELECT b.inq_id, b.message, b.status, b.created_at,
               h.name AS hostel_name
        FROM bookings b
        JOIN hostels h ON b.hostel_id = h.hostel_id
        WHERE b.inq_id = $student_id
        ORDER BY b.created_at DESC";


$result = mysqli_query($conn, $sql);
$inq = mysqli_fetch_assoc($result);

if (!$inq) {
    die("Inquiry not found!");
}

// Fetch owner reply (latest one)
$reply_sql = "SELECT reply_message, created_at
              FROM inquiry_replies
              WHERE inquiry_id = $inq_id AND sender='owner'
              ORDER BY created_at DESC LIMIT 1";

$reply_result = mysqli_query($conn, $reply_sql);
$owner_reply = mysqli_fetch_assoc($reply_result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inquiry Details</title>
    <style>
        body { font-family: Arial; background: #f6f6f6; padding: 20px; }
        .box {
            max-width: 700px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 2px 8px rgba(0,0,0,0.2);
        }
        .status {
            padding: 8px 12px;
            border-radius: 6px;
            display: inline-block;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .pending { background: #fff4cc; color: #8a6d00; }
        .approved { background: #d9ffd9; color: #006b00; }
        .rejected { background: #ffd6d6; color: #a10000; }
    </style>
</head>
<body>

<div class="box">

    <h2>Inquiry Details</h2>

    <p><strong>Hostel:</strong> <?= htmlspecialchars($inq['hostel_name']) ?></p>
    <p><strong>Sent on:</strong> <?= $inq['created_at'] ?></p>

    <p><strong>Your Message:</strong><br><?= nl2br(htmlspecialchars($inq['message'])) ?></p>

    <!-- Status Badge -->
    <div class="status 
        <?= $inq['status']=='pending' ? 'pending':'' ?>
        <?= $inq['status']=='approved' ? 'approved':'' ?>
        <?= $inq['status']=='rejected' ? 'rejected':'' ?>
    ">
        <?= ucfirst($inq['status']) ?>
    </div>

    <hr>

    <!-- OWNER REPLY BOX -->
    <?php if ($inq['status'] == 'approved' && $owner_reply): ?>

        <div style="background:#eaffea; padding:15px; border-radius:8px;">
            <h3 style="margin-top:0;">Owner Reply</h3>
            <p><?= nl2br(htmlspecialchars($owner_reply['reply_message'])) ?></p>
            <small style="color:gray;">Replied on: <?= $owner_reply['created_at'] ?></small>
        </div>

    <?php elseif ($inq['status'] == 'approved'): ?>

        <p style="color:gray; margin-top:20px;">Hostel owner has not replied yet.</p>

    <?php elseif ($inq['status'] == 'pending'): ?>

        <p style="color:orange; margin-top:20px;">Your inquiry is still pending. Wait for approval.</p>

    <?php elseif ($inq['status'] == 'rejected'): ?>

        <p style="color:red; margin-top:20px;">Your inquiry was rejected by the hostel owner.</p>

    <?php endif; ?>

    <br><br>
    <a href="my_inquiries.php" style="text-decoration:none; background:#0462a1; color:#fff; padding:10px 14px; border-radius:6px;">
        Back
    </a>

</div>

</body>
</html>
