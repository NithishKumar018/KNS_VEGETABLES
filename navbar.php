<?php
$current_page = basename($_SERVER['SCRIPT_NAME'] ?? ($_SERVER['PHP_SELF'] ?? ''));
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
:root {
    --primary: #5e99f3ff;
}

.navbar-main {
    background: var(--primary);
    padding: 12px 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.navbar-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.navbar-logo {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid white;
}

.navbar-title {
    color: #fff;
    font-size: 20px;
    font-weight: 700;
    letter-spacing: .5px;
}

.nav-links {
    display: flex;
    gap: 10px;
}

/* Default appearance */
.nav-links a {
    color: #fff;
    text-decoration: none;
    font-size: 16px;
    display:flex;
    align-items:center;
    gap:6px;
    padding: 6px 10px;
    border-radius: 8px;
    transition: .2s;
}

.nav-links a i {
    font-size: 22px;
}

/* Hide labels by default */
.nav-links a span {
    display: none;
}

/* Hover + active styling */
.nav-links a:hover,
.nav-links a.active {
    background: #fff;
    color: var(--primary) !important;
}

.nav-links a:hover i,
.nav-links a.active i,
.nav-links a:hover span,
.nav-links a.active span {
    color: var(--primary) !important;
}

/* Only active shows text */
.nav-links a.active span {
    display: inline-block;
    font-weight: 600;
}
</style>

<div class="navbar-main">
    <div class="navbar-left">
        <!-- ✅ Replace logo.png with your actual logo file -->
        <img src="uploads/lion.jpg" class="navbar-logo">
        <div class="navbar-title">KNS VEGETABLES</div>
    </div>

    <div class="nav-links">
        <a href="dashboard.php" class="<?php echo ($current_page=='dashboard.php')?'active':''; ?>">
            <i class="fa-solid fa-home"></i><span>Home</span>
        </a>

        <a href="manage_products.php" class="<?php echo ($current_page=='manage_products.php')?'active':''; ?>">
            <i class="fa-solid fa-box"></i><span>Products</span>
        </a>

        <a href="add_stock.php" class="<?php echo ($current_page=='add_stock.php')?'active':''; ?>">
            <i class="fa-solid fa-plus"></i><span>Add Stock</span>
        </a>

        <a href="sales.php" class="<?php echo ($current_page=='sales.php')?'active':''; ?>">
            <i class="fa-solid fa-bag-shopping"></i><span>Sales</span>
        </a>

        <a href="manage_sales.php" class="<?php echo ($current_page=='manage_sales.php')?'active':''; ?>">
            <i class="fa-solid fa-list"></i><span>Sales List</span>
        </a>
    </div>
</div>
