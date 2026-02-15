<?php
session_start();
require "../../config/db.php";

if (!isset($_GET['id'])) {
    die("Hostel ID missing!");
}

$hostel_id = intval($_GET['id']);

// Fetch hostel details
$stmt = mysqli_prepare($conn, "SELECT * FROM hostels WHERE hostel_id = ?");
mysqli_stmt_bind_param($stmt, "i", $hostel_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$hostel = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$hostel) {
    die("Hostel not found!");
}

// Fetch existing images
$stmt = mysqli_prepare($conn, "SELECT * FROM hostel_images WHERE hostel_id = ?");
mysqli_stmt_bind_param($stmt, "i", $hostel_id);
mysqli_stmt_execute($stmt);
$img_result = mysqli_stmt_get_result($stmt);
$images = mysqli_fetch_all($img_result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Handle form submission (update hostel)
if (isset($_POST['update_hostel'])) {

    $name = $_POST['name'];
    $location = $_POST['location'];
    $price = $_POST['price'];
    $rooms = $_POST['rooms'];
    $room_type = $_POST['room_type'];
    $description = $_POST['description'];
    $facilities = $_POST['facilities'];
    $status = $_POST['status'];

    $update_sql = "
        UPDATE hostels SET 
        name = ?, 
        location = ?, 
        price = ?, 
        rooms = ?, 
        room_type = ?, 
        description = ?, 
        facilities = ?, 
        status = ?
        WHERE id = ?
    ";

    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "ssdiisssi", 
        $name, $location, $price, $rooms, $room_type, 
        $description, $facilities, $status, $hostel_id
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Upload new images
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = "../../uploads/";

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $fileName = time() . "_" . basename($_FILES['images']['name'][$key]);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($tmp_name, $targetPath)) {
                mysqli_query($conn, "INSERT INTO hostel_images (hostel_id, image_path) VALUES ($hostel_id, '$fileName')");
            }
        }
    }

    echo "<script>alert('Hostel updated successfully!'); window.location='manage_hostels.php';</script>";
}

// Delete single image
if (isset($_GET['delete_image'])) {
    $img_id = intval($_GET['delete_image']);

    // Fetch file name
    $q = mysqli_query($conn, "SELECT image_path FROM hostel_images WHERE id = $img_id");
    $img = mysqli_fetch_assoc($q);

    if ($img) {
        unlink("../../uploads/" . $img['image_path']);
        mysqli_query($conn, "DELETE FROM hostel_images WHERE id = $img_id");
    }

    header("Location: edit_hostel.php?id=$hostel_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Hostel</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .gallery img { width: 150px; height: 120px; object-fit: cover; margin: 10px; border-radius: 8px; }
        .gallery a { display: block; margin-top: 5px; color: red; text-decoration: none; }
        .box { background: #f4f4f4; padding: 20px; width: 450px; border-radius: 8px; }
    </style>
</head>
<body>

<h2>Edit Hostel</h2>

<form method="POST" enctype="multipart/form-data" class="box">

    <label>Name:</label><br>
    <input type="text" name="name" value="<?= $hostel['name'] ?>" required><br><br>

    <label>Location:</label><br>
    <input type="text" name="location" value="<?= $hostel['location'] ?>" required><br><br>

    <label>Price:</label><br>
    <input type="number" name="price" value="<?= $hostel['price'] ?>" required><br><br>

    <label>Rooms:</label><br>
    <input type="number" name="rooms" value="<?= $hostel['rooms'] ?>" required><br><br>

    <label>Room Type:</label><br>
    <input type="text" name="room_type" value="<?= $hostel['room_type'] ?>" required><br><br>

    <label>Facilities:</label><br>
    <textarea name="facilities" required><?= $hostel['facilities'] ?></textarea><br><br>

    <label>Description:</label><br>
    <textarea name="description" required><?= $hostel['description'] ?></textarea><br><br>

    <label>Status:</label><br>
    <select name="status" required>
        <option value="Available" <?= $hostel['status'] == "Available" ? "selected" : "" ?>>Available</option>
        <option value="Not Available" <?= $hostel['status'] == "Not Available" ? "selected" : "" ?>>Not Available</option>
    </select><br><br>

    <label>Add New Images:</label><br>
    <input type="file" name="images[]" multiple><br><br>

    <button type="submit" name="update_hostel">Update Hostel</button>
</form>

<h3>Existing Images</h3>
<div class="gallery">
    <?php foreach ($images as $img): ?>
        <div>
            <img src="../../uploads/<?= $img['image_path'] ?>">
            <a href="edit_hostel.php?id=<?= $hostel_id ?>&delete_image=<?= $img['id'] ?>" onclick="return confirm('Delete this image?')">Delete</a>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
