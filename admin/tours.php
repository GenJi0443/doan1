<?php
require_once 'auth.php';
requireLogin();

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        // Get image name before delete
        $stmt = $pdo->prepare("SELECT image FROM tours WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $tour = $stmt->fetch();

        // Delete tour
        $stmt = $pdo->prepare("DELETE FROM tours WHERE id = ?");
        $stmt->execute([$_GET['id']]);

        // Delete image file if exists
        if ($tour && $tour['image'] && file_exists("../images/tours/" . $tour['image'])) {
            unlink("../images/tours/" . $tour['image']);
        }

        header('Location: tours.php?success=1');
        exit();
    } catch (Exception $e) {
        $error = "Failed to delete tour";
    }
}

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : '';
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : '';

// Build the query
$query = "SELECT * FROM tours WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (name LIKE ? OR description LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param]);
}

if ($location) {
    $query .= " AND location LIKE ?";
    $params[] = "%$location%";
}

if ($min_price !== '') {
    $query .= " AND price >= ?";
    $params[] = $min_price;
}

if ($max_price !== '') {
    $query .= " AND price <= ?";
    $params[] = $max_price;
}

$query .= " ORDER BY created_at DESC";

// Execute query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tours = $stmt->fetchAll();

// Get unique locations for filter dropdown
$locations = $pdo->query("SELECT DISTINCT location FROM tours ORDER BY location")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tours - Admin</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <style>
        .tour-image {
            width: 100px;
            height: 60px;
            object-fit: cover;
        }

        .rating-stars {
            color: #ffc107;
        }
    </style>
</head>

<body>
    <?php include 'includes/admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin_sidebar.php'; ?>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4 py-4">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Tours</h1>
                    <a href="tour_form.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Tour
                    </a>
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
                                    placeholder="Search by name or description...">
                            </div>
                            <div class="col-md-3">
                                <label>Location</label>
                                <select name="location" class="form-control">
                                    <option value="">All Locations</option>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?php echo $loc; ?>"
                                            <?php echo $location === $loc ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($loc); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Min Price</label>
                                <input type="number" name="min_price" class="form-control"
                                    value="<?php echo $min_price; ?>" min="0" step="0.01">
                            </div>
                            <div class="col-md-2">
                                <label>Max Price</label>
                                <input type="number" name="max_price" class="form-control"
                                    value="<?php echo $max_price; ?>" min="0" step="0.01">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="tours.php" class="btn btn-secondary">
                                    <i class="fas fa-sync"></i> Reset
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
                                <th>Image</th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Duration</th>
                                <th>Price</th>
                                <th>Rating</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tours as $tour): ?>
                                <tr>
                                    <td><?php echo $tour['id']; ?></td>
                                    <td>
                                        <?php if ($tour['image']): ?>
                                            <img src="../images/tours/<?php echo htmlspecialchars($tour['image']); ?>"
                                                class="tour-image" alt="<?php echo htmlspecialchars($tour['name']); ?>">
                                        <?php else: ?>
                                            <div
                                                class="tour-image bg-secondary d-flex align-items-center justify-content-center">
                                                <i class="fas fa-image text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($tour['name']); ?></strong>
                                        <small class="d-block text-muted">
                                            <?php echo substr(htmlspecialchars($tour['description']), 0, 100) . '...'; ?>
                                        </small>
                                    </td>
                                    <td><?php echo htmlspecialchars($tour['location']); ?></td>
                                    <td><?php echo htmlspecialchars($tour['duration']); ?></td>
                                    <td>$<?php echo number_format($tour['price'], 2); ?></td>
                                    <td>
                                        <div class="rating-stars">
                                            <?php
                                            $rating = round($tour['rating']);
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $rating) {
                                                    echo '<i class="fas fa-star"></i>';
                                                } else {
                                                    echo '<i class="far fa-star"></i>';
                                                }
                                            }
                                            ?>
                                            <small class="text-muted ml-1">(<?php echo $tour['rating']; ?>)</small>
                                        </div>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($tour['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="tour_form.php?id=<?php echo $tour['id']; ?>"
                                                class="btn btn-sm btn-info" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="bookings.php?tour_id=<?php echo $tour['id']; ?>"
                                                class="btn btn-sm btn-success" title="View Bookings">
                                                <i class="fas fa-calendar-check"></i>
                                            </a>
                                            <a href="tours.php?action=delete&id=<?php echo $tour['id']; ?>"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this tour?');"
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($tours)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No tours found</td>
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