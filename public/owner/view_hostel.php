<?php
session_start();
require_once('../../config/db.php');

// OWNER AUTH
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../login.php");
    exit;
}

// Accept both ?id= and ?hostel_id=
if (isset($_GET['id'])) {
    $hostel_id = (int)$_GET['id'];
} elseif (isset($_GET['hostel_id'])) {
    $hostel_id = (int)$_GET['hostel_id'];
} else {
    die("Hostel ID missing!");
}

$owner_id = (int)$_SESSION['user_id'];

// Fetch hostel
$sql = "SELECT * FROM hostels WHERE hostel_id = $hostel_id AND owner_id = $owner_id LIMIT 1";
$res = mysqli_query($conn, $sql);

if (mysqli_num_rows($res) == 0) {
    die("Hostel not found or unauthorized!");
}

$hostel = mysqli_fetch_assoc($res);

// Fetch images
$img_sql = "SELECT image_path FROM hostel_images WHERE hostel_id = $hostel_id";
$img_res = mysqli_query($conn, $img_sql);
$images = mysqli_fetch_all($img_res, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Hostel</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
    body {
        margin: 0;
        font-family: Poppins, Arial;
        background: #f4f7fb;
    }
    .container {
        max-width: 900px;
        margin: 40px auto;
        padding: 20px;
    }
    .card {
        background: white;
        padding: 25px;
        border-radius: 14px;
        box-shadow: 0 8px 22px rgba(0,0,0,0.09);
    }

    h2 {
        color: #0462a1;
        margin-top: 0;
    }

    .gallery {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        margin: 20px 0;
    }

    .gallery img {
        width: 350px;
        height: 350px;
        object-fit: cover;
        border-radius: 12px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .section-title {
        font-weight: 600;
        margin-top: 20px;
        color: #0462a1;
    }

    .badge {
        display: inline-block;
        background: #e7f3ff;
        padding: 6px 12px;
        border-radius: 20px;
        margin: 4px;
        font-size: 13px;
        color: #0462a1;
    }

    .btn {
        background: #0462a1;
        padding: 10px 18px;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        margin-right: 10px;
        font-weight: 600;
    }

    .btn.delete {
        background: #c62828;
    }
</style>
</head>

<body>
<div class="container">
    <div class="card">

        <h2><?= htmlspecialchars($hostel['name']) ?></h2>
        <p style="color:#555;">📍 <?= htmlspecialchars($hostel['location']) ?></p>

        <!-- IMAGE GALLERY -->
        <div class="gallery">
    <?php if (!empty($images)) { 
        foreach ($images as $img) { 
            $imgPath = "../../uploads/" . $img['image_path'];
    ?>
        <img src="<?= $imgPath ?>" alt="Hostel Image">
    <?php 
        } 
    } else { 
    ?>
        <p>No images available.</p>
    <?php } ?>
</div>


        <div class="section-title">💰 Price</div>
        <p>Rs <?= number_format($hostel['price']) ?> / month</p>

        <div class="section-title">🛏 Rooms</div>
        <p><?= $hostel['rooms'] ?> rooms (<?= htmlspecialchars($hostel['room_type']) ?>)</p>

        <div class="section-title">🏷 Facilities</div>
        <?php
        foreach (explode(",", $hostel['facilities']) as $f) {
            echo "<span class='badge'>" . htmlspecialchars(trim($f)) . "</span>";
        }
        ?>

        <div class="section-title">📄 Description</div>
        <p><?= nl2br(htmlspecialchars($hostel['description'])) ?></p>

        <div class="section-title">📌 Status</div>
        <p><?= htmlspecialchars($hostel['status']) ?></p>

        <br>

        <a class="btn" href="edit_hostel.php?hostel_id=<?= $hostel_id ?>">Edit Hostel</a>
        <a class="btn delete" href="delete_hostel.php?hostel_id=<?= $hostel_id ?>" 
           onclick="return confirm('Delete this hostel permanently?');">Delete</a>
        <a href="manage_hostels.php" class="btn">Back to List</a>
    </div>
</div>
</body>
</html>
