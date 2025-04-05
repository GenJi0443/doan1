<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="sidebar-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>

            <!-- Tours Management -->
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'tours.php' ? 'active' : ''; ?>" href="tours.php">
                    <i class="fas fa-route"></i>
                    Tours
                </a>
            </li>

            <!-- Hotels Management -->
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'hotels.php' ? 'active' : ''; ?>" href="hotels.php">
                    <i class="fas fa-hotel"></i>
                    Hotels
                </a>
            </li>

            <!-- Blog Management -->
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'posts.php' ? 'active' : ''; ?>" href="posts.php">
                    <i class="fas fa-blog"></i>
                    Blog Posts
                </a>
            </li>

            <!-- Comments Management -->
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'comments.php' ? 'active' : ''; ?>" href="comments.php">
                    <i class="fas fa-comments"></i>
                    Comments
                    <?php
                    // Display pending comments count if any
                    $stmt = $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'");
                    $pending_count = $stmt->fetchColumn();
                    if ($pending_count > 0):
                    ?>
                        <span class="badge badge-warning ml-2"><?php echo $pending_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- Contacts Management -->
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'contacts.php' ? 'active' : ''; ?>" href="contacts.php">
                    <i class="fas fa-envelope"></i>
                    Contacts
                    <?php
                    // Display unread messages count if any
                    $stmt = $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0");
                    $unread_count = $stmt->fetchColumn();
                    if ($unread_count > 0):
                    ?>
                        <span class="badge badge-warning ml-2"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- Bookings Management -->
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'bookings.php' ? 'active' : ''; ?>" href="bookings.php">
                    <i class="fas fa-calendar-check"></i>
                    Tour Bookings
                    <?php
                    // Display pending bookings count if any
                    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'");
                    $pending_bookings = $stmt->fetchColumn();
                    if ($pending_bookings > 0):
                    ?>
                        <span class="badge badge-warning ml-2"><?php echo $pending_bookings; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- Hotel Bookings Management -->
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'hotel_bookings.php' ? 'active' : ''; ?>"
                    href="hotel_bookings.php">
                    <i class="fas fa-bed"></i>
                    Hotel Bookings
                    <?php
                    // Display pending hotel bookings count if any
                    $stmt = $pdo->query("SELECT COUNT(*) FROM hotel_bookings WHERE status = 'pending'");
                    $pending_hotel_bookings = $stmt->fetchColumn();
                    if ($pending_hotel_bookings > 0):
                    ?>
                        <span class="badge badge-warning ml-2"><?php echo $pending_hotel_bookings; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- Users Management -->
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'users.php' ? 'active' : ''; ?>" href="users.php">
                    <i class="fas fa-users"></i>
                    Users
                </a>
            </li>
        </ul>

        <!-- Reports Section -->
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Reports</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'revenue_reports.php' ? 'active' : ''; ?>"
                    href="revenue_reports.php">
                    <i class="fas fa-chart-line"></i>
                    Revenue Reports
                </a>
            </li>
        </ul>

        <!-- Settings Section -->
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Settings</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                    <i class="fas fa-user-cog"></i>
                    Profile Settings
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
    .sidebar {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: 100;
        padding: 48px 0 0;
        box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
    }

    .sidebar-sticky {
        position: relative;
        top: 0;
        height: calc(100vh - 48px);
        padding-top: .5rem;
        overflow-x: hidden;
        overflow-y: auto;
    }

    .sidebar .nav-link {
        font-weight: 500;
        color: #333;
        padding: 0.5rem 1rem;
    }

    .sidebar .nav-link i {
        margin-right: 0.5rem;
        width: 20px;
        text-align: center;
    }

    .sidebar .nav-link.active {
        color: #007bff;
    }

    .sidebar .nav-link:hover {
        color: #007bff;
    }

    .sidebar-heading {
        font-size: .75rem;
        text-transform: uppercase;
    }

    .badge {
        font-size: 0.75rem;
    }
</style>