<?php
require_once 'auth.php';
requireLogin();

// Get filter parameters
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'all';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01'); // First day of current month
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d'); // Today
$group_by = isset($_GET['group_by']) ? $_GET['group_by'] : 'daily';

// Function to get tour bookings revenue
function getTourBookingsRevenue($pdo, $date_from, $date_to, $group_by)
{
    $groupFormat = $group_by === 'monthly' ? '%Y-%m' : '%Y-%m-%d';

    $query = "SELECT 
        DATE_FORMAT(b.created_at, '$groupFormat') as date,
        COUNT(*) as booking_count,
        SUM(t.price * b.guests) as total_revenue
    FROM bookings b
    JOIN tours t ON b.tour_id = t.id
    WHERE b.status = 'confirmed'
    AND DATE(b.created_at) BETWEEN ? AND ?
    GROUP BY DATE_FORMAT(b.created_at, '$groupFormat')
    ORDER BY date ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$date_from, $date_to]);
    return $stmt->fetchAll();
}

// Function to get hotel bookings revenue
function getHotelBookingsRevenue($pdo, $date_from, $date_to, $group_by)
{
    $groupFormat = $group_by === 'monthly' ? '%Y-%m' : '%Y-%m-%d';

    $query = "SELECT 
        DATE_FORMAT(hb.created_at, '$groupFormat') as date,
        COUNT(*) as booking_count,
        SUM(hb.total_price) as total_revenue
    FROM hotel_bookings hb
    WHERE hb.status = 'confirmed'
    AND DATE(hb.created_at) BETWEEN ? AND ?
    GROUP BY DATE_FORMAT(hb.created_at, '$groupFormat')
    ORDER BY date ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$date_from, $date_to]);
    return $stmt->fetchAll();
}

// Get revenue data based on report type
$tourRevenue = $report_type !== 'hotels' ? getTourBookingsRevenue($pdo, $date_from, $date_to, $group_by) : [];
$hotelRevenue = $report_type !== 'tours' ? getHotelBookingsRevenue($pdo, $date_from, $date_to, $group_by) : [];

// Calculate totals
$totalTourRevenue = array_sum(array_column($tourRevenue, 'total_revenue'));
$totalTourBookings = array_sum(array_column($tourRevenue, 'booking_count'));
$totalHotelRevenue = array_sum(array_column($hotelRevenue, 'total_revenue'));
$totalHotelBookings = array_sum(array_column($hotelRevenue, 'booking_count'));

// Prepare chart data
$dates = [];
$tourData = [];
$hotelData = [];

if ($report_type !== 'hotels') {
    foreach ($tourRevenue as $row) {
        $dates[] = $row['date'];
        $tourData[] = floatval($row['total_revenue']);
    }
}

if ($report_type !== 'tours') {
    foreach ($hotelRevenue as $row) {
        if (!in_array($row['date'], $dates)) {
            $dates[] = $row['date'];
        }
        $hotelData[] = floatval($row['total_revenue']);
    }
}

sort($dates);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revenue Reports - Admin</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .revenue-card {
            border-left: 4px solid;
            border-radius: 4px;
        }

        .revenue-card.tours {
            border-left-color: #007bff;
        }

        .revenue-card.hotels {
            border-left-color: #28a745;
        }

        .revenue-card .icon {
            font-size: 2.5rem;
            opacity: 0.1;
            position: absolute;
            right: 1rem;
            top: 1rem;
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
                    <h1 class="h2">Revenue Reports</h1>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" class="row align-items-end">
                            <div class="col-md-3">
                                <label>Report Type</label>
                                <select name="report_type" class="form-control">
                                    <option value="all" <?php echo $report_type === 'all' ? 'selected' : ''; ?>>All</option>
                                    <option value="tours" <?php echo $report_type === 'tours' ? 'selected' : ''; ?>>Tours Only</option>
                                    <option value="hotels" <?php echo $report_type === 'hotels' ? 'selected' : ''; ?>>Hotels Only</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Group By</label>
                                <select name="group_by" class="form-control">
                                    <option value="daily" <?php echo $group_by === 'daily' ? 'selected' : ''; ?>>Daily</option>
                                    <option value="monthly" <?php echo $group_by === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Date From</label>
                                <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                            </div>
                            <div class="col-md-2">
                                <label>Date To</label>
                                <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                                <a href="revenue_reports.php" class="btn btn-secondary">
                                    <i class="fas fa-sync"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <?php if ($report_type !== 'hotels'): ?>
                        <div class="col-md-6">
                            <div class="card revenue-card tours">
                                <div class="card-body">
                                    <i class="fas fa-route icon"></i>
                                    <h5 class="card-title text-muted">Tour Bookings Revenue</h5>
                                    <h2 class="mb-2">$<?php echo number_format($totalTourRevenue, 2); ?></h2>
                                    <p class="mb-0">
                                        <span class="text-muted">Total Bookings:</span>
                                        <strong><?php echo $totalTourBookings; ?></strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($report_type !== 'tours'): ?>
                        <div class="col-md-6">
                            <div class="card revenue-card hotels">
                                <div class="card-body">
                                    <i class="fas fa-hotel icon"></i>
                                    <h5 class="card-title text-muted">Hotel Bookings Revenue</h5>
                                    <h2 class="mb-2">$<?php echo number_format($totalHotelRevenue, 2); ?></h2>
                                    <p class="mb-0">
                                        <span class="text-muted">Total Bookings:</span>
                                        <strong><?php echo $totalHotelBookings; ?></strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Revenue Chart -->
                <div class="card mb-4">
                    <div class="card-body">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <!-- Detailed Tables -->
                <?php if ($report_type !== 'hotels' && !empty($tourRevenue)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Tour Bookings Revenue Details</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Bookings</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tourRevenue as $row): ?>
                                        <tr>
                                            <td><?php echo $row['date']; ?></td>
                                            <td><?php echo $row['booking_count']; ?></td>
                                            <td>$<?php echo number_format($row['total_revenue'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($report_type !== 'tours' && !empty($hotelRevenue)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Hotel Bookings Revenue Details</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Bookings</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($hotelRevenue as $row): ?>
                                        <tr>
                                            <td><?php echo $row['date']; ?></td>
                                            <td><?php echo $row['booking_count']; ?></td>
                                            <td>$<?php echo number_format($row['total_revenue'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize revenue chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [
                    <?php if ($report_type !== 'hotels'): ?> {
                            label: 'Tour Revenue',
                            data: <?php echo json_encode($tourData); ?>,
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            fill: true
                        },
                    <?php endif; ?>
                    <?php if ($report_type !== 'tours'): ?> {
                            label: 'Hotel Revenue',
                            data: <?php echo json_encode($hotelData); ?>,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            fill: true
                        }
                    <?php endif; ?>
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Revenue Over Time'
                    }
                }
            }
        });
    </script>
</body>

</html>