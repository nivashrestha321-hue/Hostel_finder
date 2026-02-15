<?php
session_start();
require_once('../../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
if ($_SESSION['role'] !== 'owner') {
    header("Location: ../login.php");
    exit;
}

$owner_id = (int)$_SESSION['user_id'];
$owner_name = $_SESSION['name'] ?? 'Owner';

/* --- TOTAL HOSTELS --- */
$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM hostels WHERE owner_id = $owner_id");
$total_hostels = mysqli_fetch_assoc($res)['total'];

/* --- TOTAL INQUIRIES --- */
$res = mysqli_query($conn, "
    SELECT COUNT(b.hostel_id) AS total 
    FROM bookings b
    JOIN hostels h ON b.hostel_id = h.hostel_id
    WHERE h.owner_id = $owner_id
");
$total_inquiries = mysqli_fetch_assoc($res)['total'];

/* --- HOSTELS LIST --- */
$res = mysqli_query($conn, "
    SELECT hostel_id, name, location, price, status, created_at, description 
    FROM hostels 
    WHERE owner_id = $owner_id 
    ORDER BY created_at DESC
");
$hostels = mysqli_fetch_all($res, MYSQLI_ASSOC);

/* --- RECENT INQUIRIES --- */
$res = mysqli_query($conn, "
    SELECT b.id, b.message, b.status, b.created_at,
           u.name AS student_name, h.name AS hostel_name
    FROM bookings b
    JOIN users u ON b.id = u.id
    JOIN hostels h ON b.hostel_id = h.hostel_id
    WHERE h.owner_id = $owner_id
    ORDER BY b.created_at DESC
    LIMIT 8
");
$inquiries = mysqli_fetch_all($res, MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Owner Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
:root {
    --accent:#0462a1;
    --muted:#6b7280;
    --card-bg:#fff;
    --background:#f4f7fb;
}
body {
    margin:0;
    background:var(--background);
    font-family:Poppins, sans-serif;
}
.container {
    max-width:1100px;
    margin:30px auto;
    padding:20px;
}
header {
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.link { color:var(--accent); font-weight:600; text-decoration:none; }
.btn {
    background:var(--accent);
    color:white;
    padding:8px 14px;
    border-radius:8px;
    text-decoration:none;
    font-weight:600;
}
.grid {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:14px;
    margin-top:20px;
}
.card {
    background:white;
    padding:16px;
    border-radius:12px;
    box-shadow:0 6px 18px rgba(0,0,0,0.08);
}
.hostel-list {
    margin-top:20px;
}
.hostel-item {
    background:white;
    border:1px solid #e4e7eb;
    border-radius:12px;
    padding:14px;
    margin-bottom:14px;
    display:flex;
    justify-content:space-between;
    transition:0.2s ease;
}
.hostel-item:hover {
    transform:translateY(-3px);
    box-shadow:0 8px 20px rgba(0,0,0,0.07);
}
.thumb {
    width:100px;
    height:75px;
    background:#dce7f5;
    border-radius:10px;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:13px;
    color:var(--muted);
    overflow:hidden;
    border:1px solid #c9d6e9;
}
.thumb img {
    width:100%;
    height:100%;
    object-fit:cover;
}
.hostel-meta h4 {
    margin:0;
    font-size:17px;
    font-weight:600;
}
.hostel-meta p {
    margin:4px 0;
    color:var(--muted);
}
.desc {
    font-size:13px;
    color:#4b5563;
    max-width:500px;
    margin-top:5px;
}
.inq-item {
    background:white;
    padding:12px;
    border:1px solid #e4e7eb;
    border-radius:10px;
    margin-bottom:10px;
}
@media(max-width:700px){
    .grid { grid-template-columns:1fr; }
}
</style>
</head>

<body>
<div class="container">

<header>
    <div>
        <h2 style="margin:0;color:var(--accent);">Owner Dashboard</h2>
        <div class="muted">Welcome, <?= htmlspecialchars($owner_name) ?></div>
    </div>
    <a class="btn" href="add_hostel.php">+ Add Hostel</a>
</header>

<div class="grid">
    <div class="card">
        <h3>Total Hostels</h3>
        <div style="font-size:28px;font-weight:700;"><?= $total_hostels ?></div>
        <div class="muted">Your listings</div>
    </div>

    <div class="card">
        <h3>Total Inquiries</h3>
        <div style="font-size:28px;font-weight:700;"><?= $total_inquiries ?></div>
        <div class="muted">Messages & booking requests</div>
    </div>

    <div class="card">
        <h3>Quick Links</h3>
        <a href="manage_hostels.php" class="link">Manage Hostels</a><br>
        <a class="link" href="view_inquiries.php">View Inquiries</a>
    </div>
</div>

<h3 style="margin-top:25px;">Your Hostels</h3>
<div class="hostel-list">

<?php
if (empty($hostels)) {
    echo '<div class="card">No hostels added yet. <a class="link" href="add_hostel.php">Add now</a></div>';
} else {
    foreach ($hostels as $h) {

        $imgQuery = mysqli_query($conn,
            "SELECT image_path FROM hostel_images WHERE hostel_id = {$h['hostel_id']} LIMIT 1"
        );
        $imgData = mysqli_fetch_assoc($imgQuery);
        $imgPath = $imgData ? "../../uploads/".$imgData['image_path'] : null;
?>
    <div class="hostel-item">

        <div style="display:flex; gap:14px;">
            <div class="thumb">
                <?php if ($imgPath && file_exists($imgPath)) { ?>
                    <img src="<?= $imgPath ?>">
                <?php } else { ?>
                    No Image
                <?php } ?>
            </div>

            <div class="hostel-meta">
                <h4><?= htmlspecialchars($h['name']) ?></h4>
                <p><?= htmlspecialchars($h['location']) ?> • Rs <?= number_format($h['price']) ?></p>

                <div class="desc">
                    <?= nl2br(substr(htmlspecialchars($h['description']), 0, 110)) ?>...
                </div>

                <div class="small" style="margin-top:6px;">
                    Status: <strong><?= $h['status'] ?></strong> |
                    Added: <?= date("M j, Y", strtotime($h['created_at'])) ?>
                </div>
            </div>
        </div>

        <div>
            <a class="link" href="edit_hostel.php?hostel_id=<?= $h['hostel_id'] ?>">Edit</a> |
            <a class="link" href="view_hostel.php?hostel_id=<?= $h['hostel_id'] ?>">View</a> |
            <a class="link" style="color:#e53e3e;" 
   onclick="return confirm('Delete this hostel?')"
   href="delete_hostel.php?hostel_id=<?= $h['hostel_id'] ?>">Delete</a>

        </div>

    </div>
<?php } } ?>

</div>

<h3 style="margin-top:25px;">Recent Inquiries</h3>

<?php
if (empty($inquiries)) {
    echo '<div class="card">No inquiries yet.</div>';
} else {
    foreach ($inquiries as $inq) {
?>
<div class="inq-item">
    <strong><?= htmlspecialchars($inq['student_name']) ?></strong> asked about 
    <strong><?= htmlspecialchars($inq['hostel_name']) ?></strong><br>
    <span class="muted"><?= $inq['created_at'] ?></span>
    <p><?= nl2br(htmlspecialchars($inq['message'])) ?></p>
    <a class="link" href="view_inquiry.php?id=<?= $inq['id'] ?>">Open</a>

</div>
<?php } } ?>

<br>
<a href="../logout.php" class="link">Logout</a>

</div>
</body>
</html>
