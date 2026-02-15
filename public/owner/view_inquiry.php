<?php
session_start();
require_once('../../config/db.php');

// Check if inquiry ID is provided
if (!isset($_GET['id'])) {
    die("Inquiry ID missing!");
}

$inq_id = intval($_GET['id']);

// Fetch inquiry details
$sql = "SELECT 
            b.id AS inquiry_id, 
            b.message, 
            b.status, 
            b.created_at,
            u.name AS student_name,
            h.name AS hostel_name
        FROM bookings b
        JOIN users u ON b.id = u.id
        JOIN hostels h ON b.hostel_id = h.hostel_id
        WHERE b.id = $inq_id
        LIMIT 1";

$result = mysqli_query($conn, $sql);
$inq = mysqli_fetch_assoc($result);

if (!$inq) {
    die("Inquiry not found!");
}

if (isset($_POST['send_reply'])) {
    $reply_msg = mysqli_real_escape_string($conn, $_POST['reply_message']);

    $reply_sql = "INSERT INTO inquiry_replies (inquiry_id, sender, message)
                  VALUES ($inq_id, 'owner', '$reply_msg')";
    mysqli_query($conn, $reply_sql);

    header("Location: view_inquiry.php?id=" . $inq_id);
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);

    $update_sql = "UPDATE bookings SET status = '$new_status' WHERE id = $inq_id";
    mysqli_query($conn, $update_sql);

    $inq['status'] = $new_status;
    $success = "Status updated!";
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Inquiry Details</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .box {
            max-width: 700px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 2px 8px rgba(0,0,0,0.2);
        }
        .btn {
            padding: 10px 14px;
            background: #0462a1;
            color: white;
            border-radius: 6px;
            text-decoration: none;
        }
        select, textarea {
            width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>Inquiry Details</h2>

    <?php if (!empty($success)): ?>
        <p style="color:green;"><strong><?= $success ?></strong></p>
    <?php endif; ?>

    <p><strong>Hostel:</strong> <?= htmlspecialchars($inq['hostel_name']) ?></p>
    <p><strong>Student:</strong> <?= htmlspecialchars($inq['student_name']) ?></p>
    <p><strong>Date:</strong> <?= $inq['created_at'] ?></p>
    <p><strong>Message:</strong><br><?= nl2br(htmlspecialchars($inq['message'])) ?></p>
        <?php
// Fetch replies
$rep_sql = "SELECT * FROM inquiry_replies WHERE inquiry_id = $inq_id ORDER BY created_at ASC";
$rep_result = mysqli_query($conn, $rep_sql);
?>
<div>
    <?php while($rep = mysqli_fetch_assoc($rep_result)) { ?>
        <div style="margin-bottom:12px;">
            <strong><?= $rep['sender'] == 'owner' ? "You (Owner)" : "Student" ?></strong><br>
            <span style="font-size:12px;color:gray"><?= $rep['created_at'] ?></span>
            <p><?= nl2br(htmlspecialchars($rep['message'])) ?></p>
            <hr>
        </div>
    <?php } ?>
</div>
<br>
<h3>Send Reply</h3>
<form method="POST">
    <textarea name="reply_message" required placeholder="Write a message..."></textarea><br>
    <button class="btn" type="submit" name="send_reply">Send Reply</button>
</form>
    <p><strong>Status:</strong></p>
    <form method="POST">
        <select name="status" required>
            <option value="pending" <?= $inq['status']=='pending' ? 'selected':'' ?>>Pending</option>
            <option value="approved" <?= $inq['status']=='approved' ? 'selected':'' ?>>Approved</option>
            <option value="rejected" <?= $inq['status']=='rejected' ? 'selected':'' ?>>Rejected</option>
        </select>

        <br><br>
        <button class="btn" type="submit">Update Status</button>
    </form><br>
    <a class="btn" href="view_inquiries.php">Back</a>
</div>

</body>
</html>
