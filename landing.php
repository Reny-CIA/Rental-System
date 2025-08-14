<?php
// landing.php

session_start();
if (isset($_SESSION['login_id'])) {
    header("Location: index.php?page=home");
    exit;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jovic Park - Welcome</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<style>
/* Full-screen carousel */
.hero-carousel {
    position: relative;
    height: 100vh;
    overflow: hidden;
}

.carousel-item {
    height: 100vh;
    background-size: cover;
    background-position: center;
    position: relative;
}

.carousel-item::after {
    content: '';
    position: absolute;
    top:0; left:0; right:0; bottom:0;
    background: rgba(0, 0, 0, 0.4); /* dark overlay for text */
}

/* Overlay text container */
.carousel-caption {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    text-align: center;
    z-index: 10;
}

.carousel-caption h1 {
    font-size: 3rem;
    font-weight: bold;
    color: #e0f0ff;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
}

.carousel-caption p {
    font-size: 1.5rem;
    color: #ffffff;
    text-shadow: 1px 1px 6px rgba(0,0,0,0.6);
    margin-bottom: 2rem;
}

/* Neon login button */
.btn-neon {
    font-weight: bold;
    font-size: 1.2rem;
    color: #e0f0ff;
    background-color: transparent;
    border: 2px solid #e0f0ff;
    padding: 0.75rem 2rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    position: relative;
    transition: all 0.3s ease;
}

.btn-neon::before, .btn-neon::after {
    content: '';
    position: absolute;
    top:0; left:0; right:0; bottom:0;
    border: 2px solid #e0f0ff;
    opacity: 0;
    transition: all 0.3s ease;
}

.btn-neon:hover {
    color: #fff;
    box-shadow: 0 0 10px #e0f0ff, 0 0 20px #e0f0ff, 0 0 40px #e0f0ff;
    text-decoration: none;
}
.btn-neon:hover::before, .btn-neon:hover::after {
    opacity: 1;
}

/* Responsive adjustments */
@media (max-width: 768px){
    .carousel-caption h1 { font-size: 2rem; }
    .carousel-caption p { font-size: 1rem; }
    .btn-neon { font-size: 1rem; padding: 0.5rem 1.5rem; }
}
</style>
</head>
<body>

<!-- Carousel -->
<div id="heroCarousel" class="carousel slide hero-carousel" data-ride="carousel" data-interval="4000">
    <ol class="carousel-indicators">
        <li data-target="#heroCarousel" data-slide-to="0" class="active"></li>
        <li data-target="#heroCarousel" data-slide-to="1"></li>
        <li data-target="#heroCarousel" data-slide-to="2"></li>
    </ol>

    <div class="carousel-inner">
        <div class="carousel-item active" style="background-image: url('assets/uploads/single_room.jpg');">
            <div class="carousel-caption">
                <h1>Cozy Single Rooms</h1>
                <p>Affordable & comfortable living</p>
                <a href="login.php" class="btn btn-neon">Login</a>
            </div>
        </div>
        <div class="carousel-item" style="background-image: url('assets/uploads/double_room.jpg');">
            <div class="carousel-caption">
                <h1>Spacious Double Rooms</h1>
                <p>Perfect for families or roommates</p>
                <a href="login.php" class="btn btn-neon">Login</a>
            </div>
        </div>
        <div class="carousel-item" style="background-image: url('assets/uploads/luxury_house.jpg');">
            <div class="carousel-caption">
                <h1>Luxury Houses</h1>
                <p>Premium living spaces for everyone</p>
                <a href="login.php" class="btn btn-neon">Login</a>
            </div>
        </div>
    </div>

    <a class="carousel-control-prev" href="#heroCarousel" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    </a>
    <a class="carousel-control-next" href="#heroCarousel" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
    </a>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
