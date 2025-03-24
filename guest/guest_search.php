<?php
session_start();
require "../admin/config.php";


$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($searchQuery)) {
    $_SESSION['error'] = "Please enter a search term.";
    header("Location: guest_category.php");
    exit();
}

function searchProducts($query) {
    $conn = connection();
    $sql = "SELECT p.*, c.name AS category_name 
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.name LIKE ? OR p.description LIKE ? 
            ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $searchTerm = "%$query%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);

    if (!$stmt->execute()) {
        throw new Exception("Error executing search: " . $stmt->error);
    }

    return $stmt->get_result();
}

try {
    $products = searchProducts($searchQuery);
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    $products = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Art Nebula</title>
    <!-- Include the same styles as in category.php -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3a506b;
            --secondary-color: #ff6b6b;
            --accent-color: #6fffe9;
            --light-color: #f8f9fa;
            --dark-color: #1f2833;
            --text-color: #333;
            --text-light: #f8f9fa;
            --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style-type: none;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .flex-row {
            display: flex;
            flex-wrap: wrap;
        }

        /* Navigation */
        .nav {
            background-color: white;
            box-shadow: var(--box-shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-menu {
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            width: 90%;
            margin: 0 auto;
        }

        .nav-brand a {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            letter-spacing: 1px;
        }

        .nav-items {
            display: flex;
            margin: 0;
        }

        .nav-link {
            padding: 0 15px;
        }

        .nav-link a {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--dark-color);
            transition: var(--transition);
        }

        .nav-link a:hover {
            color: var(--secondary-color);
        }

        .icons-items {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .icons-items a {
            font-size: 1.3rem;
            color: var(--dark-color);
            transition: var(--transition);
        }

        .icons-items a:hover {
            color: var(--secondary-color);
        }

        .toggle-collapse {
            display: none;
        }

        /* Search Bar Styles */
.search-bar {
    display: flex;
    align-items: center;
    margin-left: 20px;
    flex-grow: 1;
    max-width: 400px;
}

.search-bar form {
    display: flex;
    width: 100%;
}

.search-bar input[type="text"] {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 30px 0 0 30px;
    font-size: 1rem;
    outline: none;
    transition: var(--transition);
}

.search-bar input[type="text"]:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 5px rgba(58, 80, 107, 0.3);
}

.search-bar button {
    padding: 10px 15px;
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 0 30px 30px 0;
    cursor: pointer;
    transition: var(--transition);
}

.search-bar button:hover {
    background-color: var(--secondary-color);
}

/* Responsive Design for Search Bar */
@media (max-width: 991px) {
    .search-bar {
        margin: 20px 0;
        max-width: 100%;
    }
}

        /* Main content */
        main {
            padding: 40px 0;
        }

        /* Breadcrumbs */
        .breadcrumb {
            display: flex;
            margin-bottom: 30px;
            align-items: center;
        }

        .breadcrumb-item {
            margin-right: 10px;
        }

        .breadcrumb-item:after {
            content: '/';
            margin-left: 10px;
            color: #999;
        }

        .breadcrumb-item:last-child:after {
            content: '';
        }

        .breadcrumb-item a {
            color: var(--primary-color);
            transition: var(--transition);
        }

        .breadcrumb-item a:hover {
            color: var(--secondary-color);
        }

        .breadcrumb-item.active {
            color: var(--text-color);
        }

        /* Alert messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            position: relative;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .btn-close {
            position: absolute;
            right: 15px;
            top: 15px;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 1.2rem;
        }

        /* Category styles */
        .heading {
            text-align: center;
            position: relative;
            margin-bottom: 40px;
            font-size: 2.5rem;
            color: var(--primary-color);
        }

        .heading::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background-color: var(--secondary-color);
            border-radius: 2px;
        }

        .category-filters {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 40px;
            overflow-x: auto;
            padding-bottom: 10px;
        }

        .category-filter {
            padding: 8px 16px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 30px;
            cursor: pointer;
            transition: var(--transition);
            white-space: nowrap;
        }

        .category-filter:hover, .category-filter.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .count-badge {
            background-color: var(--accent-color);
            color: var(--dark-color);
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: 8px;
        }

        .category-filter.active .count-badge {
            background-color: white;
            color: var(--primary-color);
        }

        .category-section {
            margin-bottom: 60px;
        }

        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .category-title {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .view-all {
            color: var(--secondary-color);
            font-weight: 500;
            transition: var(--transition);
        }

        .view-all:hover {
            color: var(--primary-color);
        }

        /* Product grid */
        .box-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        .box {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        .box:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .product-img-container {
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .box:hover .product-img {
            transform: scale(1.1);
        }

        .featured-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: var(--secondary-color);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: 500;
            z-index: 1;
        }

        .box-content {
            padding: 20px;
        }

        .category-tag {
            display: inline-block;
            padding: 3px 10px;
            background-color: var(--accent-color);
            color: var(--dark-color);
            border-radius: 20px;
            font-size: 0.75rem;
            margin-bottom: 10px;
        }

        .box h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--primary-color);
            height: 2.8rem;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .box p.product-description {
            font-size: 0.9rem;
            margin-bottom: 15px;
            color: #666;
            height: 4rem;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        .product-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            transition: var(--transition);
            box-shadow: var(--box-shadow);
            width: 100%;
            text-align: center;
        }

        .btn:hover {
            background-color: var(--primary-color);
            transform: translateY(-3px);
        }

        /* No products message */
        .no-products {
            text-align: center;
            padding: 60px 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            margin-top: 30px;
        }

        .no-products i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .no-products h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .no-products p {
            color: #666;
            margin-bottom: 20px;
        }

        /* Footer */
        .footer {
            background-color: var(--dark-color);
            color: var(--light-color);
            padding: 60px 0 20px;
        }

        .footer .container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }

        .footer h2 {
            position: relative;
            margin-bottom: 25px;
            font-size: 1.5rem;
        }

        .footer h2::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--secondary-color);
            border-radius: 2px;
        }

        .footer p {
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .footer-links p {
            cursor: pointer;
            transition: var(--transition);
        }

        .footer-links p:hover {
            color: var(--secondary-color);
            transform: translateX(5px);
        }

        .social-icons {
            display: flex;
            gap: 15px;
        }

        .social-icons a {
            display: inline-block;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: var(--transition);
        }

        .social-icons a:hover {
            background-color: var(--secondary-color);
            transform: translateY(-5px);
        }

        .rights {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            justify-content: center;
            text-align: center;
        }

        .rights h4 {
            font-weight: 400;
            font-size: 0.9rem;
        }

        /* Responsive Design */
        @media (max-width: 991px) {
            .nav-menu {
                flex-direction: column;
                position: relative;
            }

            .toggle-collapse {
                display: block;
                position: absolute;
                top: 20px;
                right: 20px;
                cursor: pointer;
            }

            .toggle-collapse i {
                font-size: 1.5rem;
            }

            .collapse {
                height: 0;
                overflow: hidden;
                transition: var(--transition);
            }

            .collapse.show {
                height: auto;
            }

            .nav-items {
                flex-direction: column;
                text-align: center;
                padding: 20px 0;
                width: 100%;
            }

            .nav-link {
                padding: 10px 0;
            }

            .icons-items {
                margin: 20px 0;
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .product-img-container {
                height: 180px;
            }
            
            .box-content {
                padding: 15px;
            }
            
            .box h3 {
                height: auto;
                -webkit-line-clamp: 1;
            }
            
            .box p.product-description {
                height: 3rem;
                -webkit-line-clamp: 2;
            }
            
            .footer .container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="nav">
    <div class="nav-menu flex-row">
        <div class="nav-brand">
            <a href="index_customers.php">Art Nebula</a>
        </div>
        <div class="toggle-collapse">
            <div class="toggle-icons">
                <i class="fas fa-bars"></i>
            </div>
        </div>
        <div class="collapse">
            <ul class="nav-items">
                <li class="nav-link">
                    <a href="index_customers.html">Home</a>
                </li>
                <li class="nav-link">
                    <a href="category.php" style="color: var(--secondary-color);">Category</a>
                </li>
                <li class="nav-link">
                    <a href="#about">About Us</a>
                </li>
            </ul>
        </div>
        <!-- Add the search bar here -->
        <div class="search-bar">
            <form action="search.php" method="GET">
                <input type="text" name="query" placeholder="Search for art materials..." required>
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
        </div>
        <div class="icons-items">
            <a href="cart.php" title="Shopping Cart"><i class="fa-solid fa-cart-shopping"></i></a>
            <a href="profile.php" title="User Account"><i class="fa-solid fa-user"></i></a>
            <a href="logout.php" title="Logout"><i class="fa-solid fa-sign-out-alt"></i></a>
        </div>
    </div>
</div>

    <main>
        <div class="container">
            <!-- Breadcrumbs -->
            <div class="breadcrumb">
                <div class="breadcrumb-item"><a href="index.php">Home</a></div>
                <div class="breadcrumb-item active">Search Results</div>
            </div>

            <!-- Feedback Messages -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fa-solid fa-circle-check" style="margin-right: 8px;"></i>
                    <?= htmlspecialchars($_SESSION['message']) ?>
                    <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fa-solid fa-circle-exclamation" style="margin-right: 8px;"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <h1 class="heading">Search Results for "<?= htmlspecialchars($searchQuery) ?>"</h1>

            <?php if ($products && $products->num_rows > 0): ?>
                <div class="box-container">
                    <?php while ($product = $products->fetch_assoc()): ?>
                        <div class="box">
                            <?php if (isset($product['featured']) && $product['featured'] == 1): ?>
                                <span class="featured-badge">Featured</span>
                            <?php endif; ?>
                            <div class="product-img-container">
                                <?php 
                                $imagePath = !empty($product['image_path']) 
                                    ? '../admin/'. htmlspecialchars($product['image_path']) 
                                    : 'images/placeholder.jpg';
                                ?>
                                <img src="<?= $imagePath ?>" class="product-img" alt="<?= htmlspecialchars($product['name']) ?>">
                            </div>
                            <div class="box-content">
                                <span class="category-tag"><?= htmlspecialchars($product['category_name']) ?></span>
                                <h3><?= htmlspecialchars($product['name']) ?></h3>
                                <?php if (!empty($product['description'])): ?>
                                    <p class="product-description">
                                        <?= htmlspecialchars($product['description']) ?>
                                    </p>
                                <?php endif; ?>
                                <p class="product-price">₱<?= number_format($product['price'], 2) ?></p>
                                <a href="guest_product_details.php?id=<?= $product['id'] ?>" class="btn">
                                    View Details
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-products">
                    <i class="fa-solid fa-box-open"></i>
                    <h3>No Products Found</h3>
                    <p>We couldn't find any products matching your search.</p>
                    <a href="category.php" class="btn" style="width: auto; display: inline-block; margin-top: 15px;">
                        <i class="fa-solid fa-arrow-left" style="margin-right: 5px;"></i> Back to All Categories
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
    <div class="container">
        <!-- About Us Section -->
        <div class="about-us">
            <h2>About Us</h2>
            <p>
                Art Nebula PH is a premier online retailer and authorized distributor of art materials in the Philippines. Established in 2015, we aim to bring the world's finest art supplies to both hobbyists and professional artists, reigniting the passion for art and painting.
            </p>
        </div>

        <!-- Contact Us Section -->
        <div class="footer-links">
            <h2>Contact Us</h2>
            <ul>
                <li><i class="fas fa-map-marker-alt"></i> Visit Us — 1 Art Nebula, Cotabato</li>
                <li><i class="fas fa-phone"></i> Call Us — +63 988 7768 554</li>
                <li><i class="fas fa-clock"></i> Business Hours — Monday - Saturday, 10 AM - 9:30 PM</li>
                <li><i class="fas fa-envelope"></i> Email Us — <a href="mailto:artnebula@gmail.com">artnebula@gmail.com</a></li>
            </ul>
        </div>

        <!-- Follow Us Section -->
        <div class="follow">
            <h2>Follow Us</h2>
            <div class="social-icons">
                <a href="https://web.facebook.com/artnebulaph" aria-label="Facebook" target="_blank" rel="noopener noreferrer">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <!-- Add more social media links here if needed -->
            </div>
        </div>
    </div>

    <!-- Copyright Section -->
    <div class="rights">
        <h4>&copy; <?= date('Y') ?> Art Nebula | All rights reserved | Made by Ronald and Team</h4>
    </div>
</footer>
    <script>
        // Toggle navigation menu for mobile
        const toggleBar = document.querySelector(".toggle-collapse");
        const navCollapse = document.querySelector(".collapse");

        toggleBar.addEventListener('click', function() {
            navCollapse.classList.toggle('show');
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>