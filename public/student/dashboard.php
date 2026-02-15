<?php
session_start();
require_once('../../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
if ($_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$student_id = (int)$_SESSION['user_id'];
$student_name = $_SESSION['name'] ?? 'Student';

/* --- TOTAL HOSTELS --- */
$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM hostels");
$total_hostels = mysqli_fetch_assoc($res)['total'];

/* --- TOTAL BOOKINGS BY STUDENT --- */
$res = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM bookings 
    WHERE id = $student_id
");
$total_bookings = mysqli_fetch_assoc($res)['total'];

/* --- LATEST HOSTELS --- */
$res = mysqli_query($conn, "
    SELECT hostel_id, name, location, price, description, created_at
    FROM hostels 
    ORDER BY created_at DESC 
    LIMIT 8
");
$hostels = mysqli_fetch_all($res, MYSQLI_ASSOC);

/* --- RECENT INQUIRIES BY STUDENT --- */
$res = mysqli_query($conn, "
    SELECT b.hostel_id, b.status, b.message, b.created_at,
           h.name AS hostel_name
    FROM bookings b
    JOIN hostels h ON b.hostel_id = h.hostel_id
    WHERE b.id = $student_id
    ORDER BY b.created_at DESC
    LIMIT 8
");
$inquiries = mysqli_fetch_all($res, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Student Dashboard</title>
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
h2 { color:var(--accent); margin:0; }
.btn {
    background:var(--accent);
    color:white;
    padding:8px 14px;
    border-radius:8px;
    text-decoration:none;
    font-weight:600;
}
.link { color:var(--accent); text-decoration:none; font-weight:600; }

.grid {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:14px;
    margin-top:20px;
}

.card {
    background:white;
    padding:18px;
    border-radius:12px;
    box-shadow:0 6px 18px rgba(0,0,0,0.08);
}

.hostel-list {
    margin-top:22px;
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
    gap:15px;
}
.hostel-card {
    background:white;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 6px 18px rgba(0,0,0,0.08);
}
.hostel-card img {
    width:100%;
    height:160px;
    object-fit:cover;
}
.hostel-body {
    padding:12px 15px;
}
.hostel-body h4 {
    margin:0;
    font-size:18px;
    font-weight:600;
}
.hostel-body p {
    color:var(--muted);
    margin:5px 0;
}

.inq-item {
    background:white;
    padding:12px;
    border-radius:10px;
    border:1px solid #e4e7eb;
    margin-bottom:12px;
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
        <h2>Student Dashboard</h2>
        <span style="color:var(--muted)">Welcome, <?= htmlspecialchars($student_name) ?></span>
    </div>

    <a class="btn" href="view_hostels.php">Browse Hostels</a>
</header>


<!-- STAT CARDS -->
<div class="grid">

    <div class="card">
        <h3>Total Hostels</h3>
        <div style="font-size:28px;font-weight:700;"><?= $total_hostels ?></div>
        <div class="muted">Available hostels</div>
    </div>

    <div class="card">
        <h3>Your Inquiries</h3>
        <div style="font-size:28px;font-weight:700;"><?= $total_bookings ?></div>
        <div class="muted">Messages you have sent</div>
    </div>

    <div class="card">
        <h3>Quick Links</h3>
        <a href="view_hostels.php" class="link">View Hostels</a><br>
        <a href="my_inquiries.php" class="link">My Inquiries</a>
    </div>

</div>


<!-- LATEST HOSTELS -->
<h3 style="margin-top:30px;">Latest Hostels</h3>
<div class="hostel-list">

<?php
foreach ($hostels as $h) {

    $imgQ = mysqli_query($conn,
        "SELECT image_path FROM hostel_images WHERE hostel_id = {$h['hostel_id']} LIMIT 1"
    );
    $img = mysqli_fetch_assoc($imgQ);
    $imgPath = $img ? "../../uploads/" . $img['image_path'] : null;
?>
<div class="hostel-card">

    <?php if ($imgPath && file_exists($imgPath)) { ?>
        <img src="<?= $imgPath ?>">
    <?php } else { ?>
        <img src="https://via.placeholder.com/600x400?text=No+Image">
    <?php } ?>

    <div class="hostel-body">
        <h4><?= htmlspecialchars($h['name']) ?></h4>
        <p><?= htmlspecialchars($h['location']) ?> • Rs <?= number_format($h['price']) ?></p>

        <p style="font-size:13px;">
            <?= nl2br(substr(htmlspecialchars($h['description']), 0, 100)) ?>...
        </p>

        <a class="link" href="view_hostels.php?id=<?= $h['hostel_id'] ?>">View Details</a>
    </div>

</div>
<?php } ?>

</div>



<!-- RECENT INQUIRIES -->
<h3 style="margin-top:30px;">Recent Inquiries</h3>

<?php
if (empty($inquiries)) {
    echo '<div class="card">You have not sent any inquiries yet.</div>';
} else {
    foreach ($inquiries as $inq) {
?>
<div class="inq-item">
    <strong><?= htmlspecialchars($inq['hostel_name']) ?></strong><br>
    <span style="color:var(--muted)"><?= $inq['created_at'] ?></span>
    <p><?= nl2br(htmlspecialchars($inq['message'])) ?></p>
    Status: <strong><?= htmlspecialchars($inq['status']) ?></strong><br>
    <a class="link" href="view_inquiry.php?inq_id=<?= $inq['inq_id'] ?>">Open</a>
</div>
<?php }} ?>


<br>
<a href="../logout.php" class="link">Logout</a>

</div>
</body>
</html>
