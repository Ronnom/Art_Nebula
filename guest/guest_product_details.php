<?php
session_start();
require "../admin/config.php";


function getProductById($product_id) {
    $conn = connection();
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return false;
    }
}

function getRelatedProducts($category_id, $current_product_id) {
    $conn = connection();
    $sql = "SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 3";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $category_id, $current_product_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Get the product ID from the URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = getProductById($product_id);

// If product not found, redirect to main shop page
if (!$product) {
    header("Location: guest_category.php");
    exit;
}

// Get related products
$related_products = getRelatedProducts($product['category_id'], $product_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Art Nebula</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
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

        /* Breadcrumb */
        .breadcrumb {
            display: flex;
            list-style: none;
            padding: 20px 0;
            margin: 0;
        }

        .breadcrumb-item {
            display: flex;
            align-items: center;
        }

        .breadcrumb-item a {
            color: var(--primary-color);
            font-weight: 500;
            transition: var(--transition);
        }

        .breadcrumb-item a:hover {
            color: var(--secondary-color);
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: '/';
            margin: 0 10px;
            color: #ccc;
        }

        .breadcrumb-item.active {
            color: var(--text-color);
            font-weight: 500;
        }

        /* Product Details */
        .product-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 40px;
        }

        .product-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .product-image-container {
            padding: 20px;
        }

        .product-image {
            width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: contain;
            border-radius: 10px;
        }

        .product-details {
            padding: 30px 20px;
        }

        .product-title {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .category-tag {
            display: inline-block;
            padding: 5px 15px;
            background-color: var(--accent-color);
            color: var(--dark-color);
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .price {
            font-size: 1.8rem;
            color: var(--secondary-color);
            font-weight: 700;
            margin-bottom: 20px;
        }

        .description {
            margin-bottom: 30px;
        }

        .description h4 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .description p {
            line-height: 1.6;
            color: #666;
        }

        .quantity-form {
            display: flex;
            align-items: center;
            margin-top: 20px;
        }

        .quantity-selector {
            display: flex;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            margin-right: 15px;
        }

        .quantity-selector span {
            padding: 10px 15px;
            background-color: #f5f5f5;
            display: flex;
            align-items: center;
        }

        .quantity-input {
            width: 60px;
            border: none;
            text-align: center;
            padding: 10px;
            font-family: 'Poppins', sans-serif;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
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
        }

        .btn:hover {
            background-color: var(--primary-color);
            transform: translateY(-3px);
        }

        /* Related Products Section */
        .related-section {
            margin-top: 50px;
        }

        .section-title {
            text-align: center;
            position: relative;
            margin-bottom: 40px;
            font-size: 2rem;
            color: var(--primary-color);
        }

        .section-title::after {
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

        .related-products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        .related-product-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        .related-product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .related-product-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .related-product-content {
            padding: 20px;
        }

        .related-product-title {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .related-product-price {
            font-size: 1.2rem;
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 15px;
        }

        /* Footer */
        .footer {
            background-color: var(--dark-color);
            color: var(--light-color);
            padding: 60px 0 20px;
            margin-top: 60px;
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

        .learn-more p {
            cursor: pointer;
            transition: var(--transition);
        }

        .learn-more p:hover {
            color: var(--secondary-color);
            transform: translateX(5px);
        }

        .follow {
            display: flex;
            flex-direction: column;
        }

        .follow .social-icons {
            display: flex;
            gap: 15px;
        }

        .follow a {
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

        .follow a:hover {
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

            .product-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .footer .container {
                grid-template-columns: 1fr;
            }

            .related-products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="nav">
        <div class="nav-menu flex-row">
            <div class="nav-brand">
                <a href="index.html">Art Nebula</a>
            </div>
            <div class="toggle-collapse">
                <div class="toggle-icons">
                    <i class="fas fa-bars"></i>
                </div>
            </div>
            <div class="collapse">
                <ul class="nav-items">
                    <li class="nav-link">
                        <a href="../index.html">Home</a>
                    </li>
                    <li class="nav-link">
                        <a href="guest_category.php">Category</a>
                    </li>
                    <li class="nav-link">
                        <a href="#about">About Us</a>
                    </li>
                </ul>
            </div>
             <!-- Add the search bar here -->
        <div class="search-bar">
            <form action="guest_search.php" method="GET">
                <input type="text" name="query" placeholder="Search for art materials..." required>
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
        </div>
            <div class="icons-items">
                <a href="../cart.php" title="Shopping Cart"><i class="fa-solid fa-cart-shopping"></i></a>
                <a href="../profile.php" title="User Account"><i class="fa-solid fa-user"></i></a>
                <a href="../logout.php" title="Logout"><i class="fa-solid fa-sign-out-alt"></i></a>
            </div>
        </div>
    </div>

    <main class="container">
        <nav>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="shop.php">Shop</a></li>
                <li class="breadcrumb-item"><a href="categories.php?id=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
            </ul>
        </nav>

        <div class="product-container">
            <div class="product-row">
                <div class="product-image-container">
                    <?php 
                    $imagePath = !empty($product['image_path']) ? '../admin/'. htmlspecialchars($product['image_path']) : 'images/placeholder.jpg';
                    ?>
                    <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                </div>
                <div class="product-details">
                    <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <span class="category-tag">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </span>
                    
                    <p class="price">₱<?php echo number_format($product['price'], 2); ?></p>
                    
                    <div class="description">
                        <h4>Description</h4>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>
                    
                    <div class="quantity-form"> <!--WILL ADD DESIGN LATERRRR-->
                        <h4>Stocks</h4> <span>
                        <p><?php echo nl2br(number_format($product['stock'])); ?></p>
                        </span>
                    </div>

                    <form action="../login.php" method="post" class="quantity-form">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <div class="quantity-selector">
                            <span>Qty</span>
                            <input type="number" name="quantity" class="quantity-input" value="1" min="1" max="10">
                        </div>
                        <button type="submit" class="btn">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($related_products && $related_products->num_rows > 0): ?>
        <div class="related-section">
            <h2 class="section-title">Related Products</h2>
            <div class="related-products-grid">
                <?php while ($related = $related_products->fetch_assoc()): ?>
                    <?php $relatedImagePath = !empty($related['image_path']) ? '../admin/' . htmlspecialchars($related['image_path']) : 'images/placeholder.jpg'; ?>
                    <div class="related-product-card">
                        <img src="<?php echo $relatedImagePath; ?>" alt="<?php echo htmlspecialchars($related['name']); ?>" class="related-product-img">
                        <div class="related-product-content">
                            <h3 class="related-product-title"><?php echo htmlspecialchars($related['name']); ?></h3>
                            <p class="related-product-price">₱<?php echo number_format($related['price'], 2); ?></p>
                            <a href="guest_product_details.php?id=<?php echo $related['id']; ?>" class="btn">View Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
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