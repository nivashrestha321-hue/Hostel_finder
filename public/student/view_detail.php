<?php
session_start();
require_once('../../config/db.php');
if (!isset($_GET['hostel_id'])) {
    die("Hostel ID missing!");
}

$hostel_id = intval($_GET['hostel_id']);

// Fetch hostel
$hostel_sql = "SELECT * FROM hostels WHERE hostel_id = $hostel_id";
$hostel_result = mysqli_query($conn, $hostel_sql);
$hostel = mysqli_fetch_assoc($hostel_result);

if (!$hostel) {
    die("Hostel not found!");
}

// Fetch hostel images
$img_sql = "SELECT image_path FROM hostel_images WHERE hostel_id = $hostel_id";
$img_result = mysqli_query($conn, $img_sql);
$images = [];
while ($row = mysqli_fetch_assoc($img_result)) {
    $images[] = $row['image_path'];
}

// Handle inquiry submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_SESSION['user_id'];
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $insert = "INSERT INTO bookings (id, hostel_id, message, status) 
               VALUES ($student_id, $hostel_id, '$message', 'pending')";

    if (mysqli_query($conn, $insert)) {
        $success = "Inquiry sent successfully!";
    } else {
        $error = "Error! Try again.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $hostel['name']; ?> - Details</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }

        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 2px 8px rgba(0,0,0,0.2);
        }

        .image-gallery {
            display: flex;
            overflow-x: auto;
            gap: 10px;
            margin-bottom: 20px;
        }

        .image-gallery img {
            width: 260px;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
        }

        .details h2 { margin-bottom: 10px; }
        .details p { font-size: 16px; margin: 8px 0; }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            background: #0462a1;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 15px;
        }

    </style>
</head>
<body>

<div class="container">

    <h2><?php echo $hostel['name']; ?></h2>

    <!-- Image Slider -->
    <div class="image-gallery">
        <?php if (!empty($images)): ?>
            <?php foreach ($images as $img): ?>
                <img src="../../uploads/<?php echo $img; ?>" alt="Hostel Image">
            <?php endforeach; ?>
        <?php else: ?>
            <img src="../../uploads/no-image.jpg" alt="No Image Available">
        <?php endif; ?>
    </div>

    <div class="details">
        <p><b>Location:</b> <?php echo $hostel['location']; ?></p>
        <p><b>Room Type:</b> <?php echo $hostel['room_type']; ?></p>
        <p><b>Price:</b> Rs. <?php echo $hostel['price']; ?></p>
        <p><b>Facilities:</b> <?php echo $hostel['facilities']; ?></p>
    </div>
        
    <a class="btn" href="view_hostels.php">Back to Hostels</a>
    

       <div style="margin-top:25px; padding:20px; background:#f8f9fc; border-radius:12px;">
    <h3>Send Inquiry</h3>

    <?php if (!empty($success)) { ?>
        <div style="color:green; margin-bottom:10px;"><?= $success ?></div>
    <?php } ?>

    <?php if (!empty($error)) { ?>
        <div style="color:red; margin-bottom:10px;"><?= $error ?></div>
    <?php } ?>

    <form method="POST">
        <textarea name="message" required 
            placeholder="Write your inquiry..."
            style="width:100%; height:100px; padding:10px; border-radius:8px; border:1px solid #ccc;">
        </textarea>

        <button style="
            margin-top:10px;
            background:#0462a1;
            padding:10px 18px;
            border:none;
            border-radius:8px;
            color:white;
            font-weight:600;
            cursor:pointer;">
            Send Inquiry
        </button>
    </form>
</div>     
</div>


</body>
</html>
