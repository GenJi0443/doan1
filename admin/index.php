<?php
require_once 'auth.php';
requireLogin();

// Get statistics
$stmt = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM hotels) as total_hotels,
    (SELECT COUNT(*) FROM hotel_bookings WHERE status = 'pending') as pending_bookings,
    (SELECT COUNT(*) FROM hotel_bookings WHERE status = 'confirmed') as confirmed_bookings,
    (SELECT COUNT(*) FROM hotel_bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as recent_bookings");
$stats = $stmt->fetch();
?>
<!-- Code by Jonng - Contact: 0766526344 (zalo, telegram) -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/wbzv44j2k6sauv2mcbto636yjk4wd2qckqzpl88g3lk0loeo/tinymce/7/tinymce.min.js"
        referrerpolicy="origin"></script>
</head>

<body>
    <?php include 'includes/admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin_sidebar.php'; ?>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4 py-4">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="mr-3">
                                        <div class="text-white-75">Total Hotels</div>
                                        <div class="text-lg font-weight-bold"><?php echo $stats['total_hotels']; ?>
                                        </div>
                                    </div>
                                    <i class="fas fa-hotel fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-warning text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="mr-3">
                                        <div class="text-white-75">Pending Bookings</div>
                                        <div class="text-lg font-weight-bold"><?php echo $stats['pending_bookings']; ?>
                                        </div>
                                    </div>
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="mr-3">
                                        <div class="text-white-75">Confirmed Bookings</div>
                                        <div class="text-lg font-weight-bold">
                                            <?php echo $stats['confirmed_bookings']; ?></div>
                                    </div>
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="mr-3">
                                        <div class="text-white-75">Recent Bookings (7d)</div>
                                        <div class="text-lg font-weight-bold"><?php echo $stats['recent_bookings']; ?>
                                        </div>
                                    </div>
                                    <i class="fas fa-calendar-alt fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings Table -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table mr-1"></i>
                        Recent Bookings
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Guest Name</th>
                                        <th>Hotel</th>
                                        <th>Room Type</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->query("SELECT b.*, h.name as hotel_name, r.name as room_name 
                                                        FROM hotel_bookings b 
                                                        JOIN hotels h ON b.hotel_id = h.id 
                                                        JOIN room_types r ON b.room_type_id = r.id 
                                                        ORDER BY b.created_at DESC LIMIT 10");
                                    while ($booking = $stmt->fetch()) {
                                        $status_class = [
                                            'pending' => 'badge-warning',
                                            'confirmed' => 'badge-success',
                                            'cancelled' => 'badge-danger'
                                        ][$booking['status']];
                                    ?>
                                    <tr>
                                        <td><?php echo $booking['id']; ?></td>
                                        <td><?php echo htmlspecialchars($booking['name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['hotel_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                        <td><?php echo $booking['checkin_date']; ?></td>
                                        <td><?php echo $booking['checkout_date']; ?></td>
                                        <td><span
                                                class="badge <?php echo $status_class; ?>"><?php echo ucfirst($booking['status']); ?></span>
                                        </td>
                                        <td>
                                            <a href="booking_details.php?id=<?php echo $booking['id']; ?>"
                                                class="btn btn-sm btn-info">View</a>
                                            <?php if ($booking['status'] === 'pending'): ?>
                                            <a href="booking_action.php?id=<?php echo $booking['id']; ?>&action=confirm"
                                                class="btn btn-sm btn-success">Confirm</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>