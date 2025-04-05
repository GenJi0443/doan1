<?php
require_once 'auth.php';
requireLogin();

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM hotels WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        header('Location: hotels.php?success=1');
        exit();
    } catch (Exception $e) {
        $error = "Failed to delete hotel";
    }
}

// Fetch all hotels
$stmt = $pdo->query("SELECT * FROM hotels ORDER BY created_at DESC");
$hotels = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hotels - Admin</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin_sidebar.php'; ?>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Hotels</h1>
                    <a href="hotel_form.php" class="btn btn-primary">Add New Hotel</a>
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

                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Rating</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hotels as $hotel): ?>
                                <tr>
                                    <td><?php echo $hotel['id']; ?></td>
                                    <td>
                                        <?php if ($hotel['image']): ?>
                                            <img src="../images/hotels/<?php echo htmlspecialchars($hotel['image']); ?>"
                                                alt="<?php echo htmlspecialchars($hotel['name']); ?>"
                                                style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($hotel['name']); ?></td>
                                    <td><?php echo htmlspecialchars($hotel['location']); ?></td>
                                    <td>
                                        <div class="text-warning">
                                            <?php
                                            $rating = floatval($hotel['rating']);
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $rating) {
                                                    echo '<i class="fas fa-star"></i>';
                                                } elseif ($i - 0.5 <= $rating) {
                                                    echo '<i class="fas fa-star-half-alt"></i>';
                                                } else {
                                                    echo '<i class="far fa-star"></i>';
                                                }
                                            }
                                            ?>
                                            <span class="ml-1"><?php echo number_format($rating, 1); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($hotel['created_at'])); ?></td>
                                    <td>
                                        <a href="hotel_form.php?id=<?php echo $hotel['id']; ?>"
                                            class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="room_types.php?hotel_id=<?php echo $hotel['id']; ?>"
                                            class="btn btn-sm btn-success">
                                            <i class="fas fa-bed"></i> Rooms
                                        </a>
                                        <a href="hotels.php?action=delete&id=<?php echo $hotel['id']; ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this hotel?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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