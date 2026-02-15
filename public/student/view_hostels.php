<?php
session_start();
require_once('../../config/db.php');

// Only logged-in students allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$student_name = $_SESSION['name'] ?? "Student";

// Fetch all hostels
$sql = "SELECT hostel_id, name, location, price, description FROM hostels ORDER BY created_at DESC";
$res = mysqli_query($conn, $sql);
$hostels = mysqli_fetch_all($res, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>View Hostels</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {
    margin:0;
    background:#f4f7fb;
    font-family:Poppins, sans-serif;
}
.container {
    max-width:1100px;
    margin:30px auto;
    padding:20px;
}

/* Header */
.header {
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.header h2 {
    margin:0;
    color:#0462a1;
}
.header .welcome {
    color:#6b7280;
}

/* Grid Cards */
.grid {
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));
    gap:20px;
    margin-top:20px;
}
.card {
    background:white;
    border-radius:14px;
    overflow:hidden;
    box-shadow:0 6px 16px rgba(0,0,0,0.07);
    transition:0.2s;
}
.card:hover {
    transform:translateY(-4px);
}

/* Image */
.card img {
    width:100%;
    height:180px;
    object-fit:cover;
    background:#dbeafe;
}

/* Content */
.card-body {
    padding:15px;
}
.card-body h3 {
    margin:0;
    color:#0462a1;
    font-size:18px;
}
.card-body p {
    margin:6px 0;
    color:#4b5563;
    font-size:14px;
    line-height:1.4;
}

/* Button */
.btn {
    display:inline-block;
    margin-top:10px;
    background:#0462a1;
    color:white;
    padding:8px 14px;
    border-radius:8px;
    text-decoration:none;
    font-weight:600;
}
</style>
</head>

<body>

<div class="container">

    <div class="header">
        <h2>Available Hostels</h2>
        <div class="welcome">Welcome, <?= htmlspecialchars($student_name) ?></div>
    </div>

    <div class="grid">

        <?php if (empty($hostels)) { ?>
            <p>No hostels available.</p>
        <?php } ?>

        <?php foreach ($hostels as $h): ?>

        <?php
            // Fetch thumbnail
            $imgRes = mysqli_query($conn,
                "SELECT image_path FROM hostel_images WHERE hostel_id = {$h['hostel_id']} LIMIT 1"
            );
            $img = mysqli_fetch_assoc($imgRes);
            $thumb = $img ? "../../uploads/" . $img['image_path'] : null;
        ?>
        <div class="card">
            <?php if ($thumb && file_exists($thumb)) { ?>
                <img src="<?= $thumb ?>" alt="Hostel Image">
            <?php } else { ?>
                <img src="https://via.placeholder.com/400x250?text=No+Image">
            <?php } ?>

            <div class="card-body">
                <h3><?= htmlspecialchars($h['name']) ?></h3>
                <p>📍 <?= htmlspecialchars($h['location']) ?></p>
                <p>💰 Rs <?= number_format($h['price']) ?> / month</p>
                <p><?= nl2br(substr(htmlspecialchars($h['description']), 0, 90)) ?>...</p>

                <a class="btn" href="view_detail.php?hostel_id=<?= $h['hostel_id'] ?>">

                    View Details
                </a>
            </div>
        </div>

        <?php endforeach; ?>

    </div>
 
</div><br>
<a class="btn" href="dashboard.php">Back to dashboard</a>
</body>
</html>
