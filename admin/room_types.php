<?php
require_once 'auth.php';
requireLogin();

// Get hotel info
$hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;
if (!$hotel_id) {
    header('Location: hotels.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
$stmt->execute([$hotel_id]);
$hotel = $stmt->fetch();

if (!$hotel) {
    header('Location: hotels.php');
    exit();
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM room_types WHERE id = ? AND hotel_id = ?");
        $stmt->execute([$_GET['id'], $hotel_id]);
        header("Location: room_types.php?hotel_id=$hotel_id&success=1");
        exit();
    } catch (Exception $e) {
        $error = "Failed to delete room type";
    }
}

// Fetch all room types for this hotel
$stmt = $pdo->prepare("
    SELECT id, name, description, price, capacity, 
           COALESCE(available_rooms, 0) as available_rooms,
           image 
    FROM room_types 
    WHERE hotel_id = ? 
    ORDER BY price ASC
");
$stmt->execute([$hotel_id]);
$room_types = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Room Types - <?php echo htmlspecialchars($hotel['name']); ?></title>
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
                    <div>
                        <h1 class="h2">Room Types</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="hotels.php">Hotels</a></li>
                                <li class="breadcrumb-item active"><?php echo htmlspecialchars($hotel['name']); ?></li>
                            </ol>
                        </nav>
                    </div>
                    <a href="room_type_form.php?hotel_id=<?php echo $hotel_id; ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Room Type
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

                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Price/Night</th>
                                <th>Capacity</th>
                                <th>Available Rooms</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($room_types as $room): ?>
                                <tr>
                                    <td><?php echo $room['id']; ?></td>
                                    <td>
                                        <?php if ($room['image']): ?>
                                            <img src="../images/rooms/<?php echo htmlspecialchars($room['image']); ?>"
                                                alt="<?php echo htmlspecialchars($room['name']); ?>"
                                                style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($room['name']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($room['description'], 0, 50)) . '...'; ?></td>
                                    <td>$<?php echo number_format($room['price'], 2); ?></td>
                                    <td>
                                        <i class="fas fa-user"></i> <?php echo $room['capacity']; ?> persons
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo (int)$room['available_rooms'] > 0 ? 'success' : 'danger'; ?>">
                                            <?php echo (int)$room['available_rooms']; ?> rooms
                                        </span>
                                    </td>
                                    <td>
                                        <a href="room_type_form.php?id=<?php echo $room['id']; ?>&hotel_id=<?php echo $hotel_id; ?>"
                                            class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="room_types.php?action=delete&id=<?php echo $room['id']; ?>&hotel_id=<?php echo $hotel_id; ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this room type?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($room_types)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No room types found</td>
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