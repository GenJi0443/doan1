<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
    <div class="container">
        <a class="navbar-brand" href="index.php">dirEngine.</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav"
            aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="oi oi-menu"></span> Menu
        </button>

        <div class="collapse navbar-collapse" id="ftco-nav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                    <a href="index.php" class="nav-link">Home</a>
                </li>
                <li class="nav-item <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">
                    <a href="about.php" class="nav-link">About</a>
                </li>
                <li class="nav-item <?php echo ($current_page == 'tour.php') ? 'active' : ''; ?>">
                    <a href="tour.php" class="nav-link">Tour</a>
                </li>
                <li class="nav-item <?php echo ($current_page == 'hotel.php') ? 'active' : ''; ?>">
                    <a href="hotel.php" class="nav-link">Hotels</a>
                </li>
                <li class="nav-item <?php echo ($current_page == 'blog.php') ? 'active' : ''; ?>">
                    <a href="blog.php" class="nav-link">Blog</a>
                </li>
                <li class="nav-item <?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">
                    <a href="contact.php" class="nav-link">Contact</a>
                </li>
                <li class="nav-item cta">
                    <a href="contact.php" class="nav-link"><span>Add listing</span></a>
                </li>
            </ul>
        </div>
    </div>
</nav>