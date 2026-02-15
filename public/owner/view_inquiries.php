<?php
session_start();
require_once('../../config/db.php');

// Only owners
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../login.php");
    exit;
}

$owner_id = (int) $_SESSION['user_id'];
$debug = false; // set true to see debug info

$sql = "
    SELECT 
        b.id AS inquiry_id,
        b.message,
        b.status,
        b.created_at,
        u.name AS student_name,
        h.name AS hostel_name
    FROM bookings b
    JOIN users u ON b.id = u.id
    JOIN hostels h ON b.hostel_id = h.hostel_id
    WHERE h.owner_id = $owner_id
    ORDER BY b.created_at DESC
";
$res = mysqli_query($conn, $sql);
$rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Owner — Inquiries</title></head>
<body>
<h2>Your Inquiries</h2>

<?php if ($debug) { echo '<pre>', htmlspecialchars($sql), '</pre>'; print_r($rows); } ?>

<?php if (empty($rows)) { ?>
    <p>No inquiries yet.</p>
<?php } else { ?>
    <?php foreach ($rows as $inq) : ?>
        <div style="border:1px solid #ddd; padding:12px; margin:10px 0;">
            <strong><?= htmlspecialchars($inq['student_name']) ?></strong>
            asked about <em><?= htmlspecialchars($inq['hostel_name']) ?></em><br>
            <small><?= htmlspecialchars($inq['created_at']) ?></small>
            <p><?= nl2br(htmlspecialchars($inq['message'])) ?></p>
            Status: <strong><?= htmlspecialchars($inq['status']) ?></strong><br>
            <!-- IMPORTANT: using inquiry_id exactly as selected in SQL -->
            <a href="view_inquiry.php?id=<?= $inq['inquiry_id'] ?>">Open</a>
        </div>
    <?php endforeach; ?>
<?php } ?>

</body>
</html>
