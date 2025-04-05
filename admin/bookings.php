<?php
require_once 'auth.php';
requireLogin();

// Handle status update
if (isset($_GET['action']) && $_GET['action'] === 'update_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $allowed_statuses = ['pending', 'confirmed', 'cancelled'];
    if (in_array($_GET['status'], $allowed_statuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->execute([$_GET['status'], $_GET['id']]);
            header("Location: bookings.php?success=1");
            exit();
        } catch (Exception $e) {
            $error = "Failed to update booking status";
        }
    }
}

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

// Build the query
$query = "SELECT b.*, t.name as tour_name, t.price as tour_price, t.location 
          FROM bookings b
          JOIN tours t ON b.tour_id = t.id
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (b.name LIKE ? OR b.email LIKE ? OR b.phone LIKE ? OR t.name LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if ($status) {
    $query .= " AND b.status = ?";
    $params[] = $status;
}

if ($date_from) {
    $query .= " AND DATE(b.checkin_date) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $query .= " AND DATE(b.checkout_date) <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY b.created_at DESC";

// Execute query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Get booking counts by status
$stmt = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count
FROM bookings");
$counts = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Bookings - Admin</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <style>
        .booking-stats .badge {
            font-size: 0.9rem;
            padding: 0.5em 1em;
        }

        .status-badge {
            width: 100px;
            text-align: center;
        }

        .guest-info {
            max-width: 200px;
        }
    </style>
</head>

<body>
    <?php include 'includes/admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin_sidebar.php'; ?>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Tour Bookings</h1>
                    <div class="booking-stats">
                        <span class="badge badge-primary">Total: <?php echo $counts['total']; ?></span>
                        <span class="badge badge-warning">Pending: <?php echo $counts['pending_count']; ?></span>
                        <span class="badge badge-success">Confirmed: <?php echo $counts['confirmed_count']; ?></span>
                        <span class="badge badge-danger">Cancelled: <?php echo $counts['cancelled_count']; ?></span>
                    </div>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success" role="alert">
                        Operation completed successfully!
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" class="row align-items-end">
                            <div class="col-md-3">
                                <label>Search</label>
                                <input type="text" name="search" class="form-control"
                                    value="<?php echo htmlspecialchars($search); ?>"
                                    placeholder="Search guest name, email, phone or tour...">
                            </div>
                            <div class="col-md-2">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Check-in From</label>
                                <input type="date" name="date_from" class="form-control"
                                    value="<?php echo $date_from; ?>">
                            </div>
                            <div class="col-md-3">
                                <label>Check-in To</label>
                                <input type="date" name="date_to" class="form-control"
                                    value="<?php echo $date_to; ?>">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="bookings.php" class="btn btn-secondary">
                                    <i class="fas fa-sync"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tour</th>
                                <th>Guest Info</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Guests</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?php echo $booking['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['tour_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($booking['location']); ?></small>
                                    </td>
                                    <td class="guest-info">
                                        <strong><?php echo htmlspecialchars($booking['name']); ?></strong><br>
                                        <a href="mailto:<?php echo htmlspecialchars($booking['email']); ?>">
                                            <?php echo htmlspecialchars($booking['email']); ?>
                                        </a><br>
                                        <?php if ($booking['phone']): ?>
                                            <small><?php echo htmlspecialchars($booking['phone']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($booking['checkin_date'])); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($booking['checkout_date'])); ?></td>
                                    <td><?php echo $booking['guests']; ?></td>
                                    <td>$<?php echo number_format($booking['tour_price'] * $booking['guests'], 2); ?></td>
                                    <td>
                                        <span class="badge status-badge badge-<?php
                                                                                echo $booking['status'] === 'confirmed' ? 'success' : ($booking['status'] === 'pending' ? 'warning' : 'danger');
                                                                                ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($booking['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <?php if ($booking['status'] === 'pending'): ?>
                                                <a href="bookings.php?action=update_status&id=<?php echo $booking['id']; ?>&status=confirmed"
                                                    class="btn btn-sm btn-success" title="Confirm Booking">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="bookings.php?action=update_status&id=<?php echo $booking['id']; ?>&status=cancelled"
                                                    class="btn btn-sm btn-danger" title="Cancel Booking"
                                                    onclick="return confirm('Are you sure you want to cancel this booking?');">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="mailto:<?php echo htmlspecialchars($booking['email']); ?>"
                                                class="btn btn-sm btn-info" title="Contact Guest">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="10" class="text-center">No bookings found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>