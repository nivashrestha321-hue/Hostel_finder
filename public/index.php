<?php
session_start();
require_once('../config/db.php');

$sql = "SELECT * FROM hostels";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hostel Finder</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f7f7f7;
            color: #333;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        nav .logo {
            font-size: 22px;
            font-weight: bold;
            color: #0088ff;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 25px;
        }

        nav ul li a {
            text-decoration: none;
            color: #333;
            font-size: 16px;
        }

        .hero {
            height: 75vh;
            background: url("https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1600&q=80")
                        center/cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            padding: 20px;
        }

        .hero h1 {
            font-size: 48px;
            max-width: 800px;
        }

        .search-box {
            margin-top: 25px;
            display: flex;
            gap: 10px;
            width: 100%;
            max-width: 500px;
        }

        .search-box input {
            flex: 1;
            padding: 12px;
        }

        .search-box button {
            padding: 12px 20px;
            background: #0088ff;
            color: white;
            border: none;
        }

        .features {
            padding: 60px 40px;
            text-align: center;
        }

        footer {
            background: #222;
            padding: 20px;
            color: white;
            text-align: center;
            margin-top: 40px;
        }
    </style>
</head>

<body>

<!-- 🔒 BLOCK ACCESS IF NOT LOGGED IN -->
<script>
    if (localStorage.getItem("loggedIn") !== "true") {
        window.location.href = "login.php";
    }
</script>

<!-- Navigation -->
<nav>
    <div class="logo">HostelFinder</div>
    <ul>
        <li><a href="#">Home</a></li>
        <li><a href="#">Hostels</a></li>

        <!-- 🔓 Logout Button -->
        <li><a href="#" onclick="logout()">Logout</a></li>
    </ul>
</nav>

<!-- Hero Section -->
<section class="hero">
    <h1>Find the Best Hostels Near You</h1>
    <p>Search, compare and book affordable student and traveler hostels in seconds.</p>
    
    <div class="search-box">
        <input type="text" placeholder="Enter city or location..." />
        <button>Search</button>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <h2>Why Choose Us?</h2>

    <div class="feature-grid">
        <div class="feature">
            <h3>🏨 Wide Variety</h3>
            <p>Browse hundreds of hostels with detailed amenities and pricing.</p>
        </div>
        <div class="feature">
            <h3>💸 Affordable Rates</h3>
            <p>Compare prices and choose the most budget-friendly option.</p>
        </div>
        <div class="feature">
            <h3>⭐ Verified Reviews</h3>
            <p>Real reviews from real users to help you make the right choice.</p>
        </div>
    </div>
</section>

<footer>
    © 2025 HostelFinder. All Rights Reserved.
</footer>

<!-- 🔓 LOGOUT SCRIPT -->
<script>
    function logout() {
        localStorage.removeItem("loggedIn");
        window.location.href = "login.html";
    }
</script>

</body>
</html>