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

$room_type = [
    'id' => '',
    'hotel_id' => $hotel_id,
    'name' => '',
    'description' => '',
    'price' => '',
    'capacity' => '2',
    'available_rooms' => '0',
    'amenities' => '',
    'image' => ''
];

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM room_types WHERE id = ? AND hotel_id = ?");
    $stmt->execute([$_GET['id'], $hotel_id]);
    $fetched_room = $stmt->fetch();
    if ($fetched_room) {
        $room_type = array_merge($room_type, $fetched_room);
    } else {
        header("Location: room_types.php?hotel_id=$hotel_id");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_type = array_merge($room_type, $_POST);
    $errors = [];

    // Validate required fields
    $required_fields = ['name', 'price', 'capacity', 'available_rooms'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst($field) . " is required";
        }
    }

    // Validate numeric fields
    if (!is_numeric($room_type['price']) || $room_type['price'] <= 0) {
        $errors[] = "Price must be a positive number";
    }
    if (!is_numeric($room_type['capacity']) || $room_type['capacity'] <= 0) {
        $errors[] = "Capacity must be a positive number";
    }
    if (!is_numeric($room_type['available_rooms']) || $room_type['available_rooms'] < 0) {
        $errors[] = "Available rooms must be zero or positive";
    }

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];

        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Invalid image format. Allowed formats: JPG, PNG, GIF";
        } else {
            $file_name = uniqid() . '_' . $_FILES['image']['name'];
            $upload_path = "../images/rooms/" . $file_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $room_type['image'] = $file_name;
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    }

    if (empty($errors)) {
        try {
            if ($room_type['id']) {
                // Update existing room type
                $stmt = $pdo->prepare("
                    UPDATE room_types 
                    SET name = ?, description = ?, price = ?, capacity = ?, 
                        available_rooms = ?, amenities = ?" .
                    ($room_type['image'] ? ", image = ?" : "") . "
                    WHERE id = ? AND hotel_id = ?
                ");

                $params = [
                    $room_type['name'],
                    $room_type['description'],
                    $room_type['price'],
                    $room_type['capacity'],
                    $room_type['available_rooms'],
                    $room_type['amenities']
                ];

                if ($room_type['image']) {
                    $params[] = $room_type['image'];
                }
                $params[] = $room_type['id'];
                $params[] = $hotel_id;

                $stmt->execute($params);
            } else {
                // Insert new room type
                $stmt = $pdo->prepare("
                    INSERT INTO room_types (hotel_id, name, description, price, capacity, 
                                         available_rooms, amenities, image)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $hotel_id,
                    $room_type['name'],
                    $room_type['description'],
                    $room_type['price'],
                    $room_type['capacity'],
                    $room_type['available_rooms'],
                    $room_type['amenities'],
                    $room_type['image']
                ]);
            }

            header("Location: room_types.php?hotel_id=$hotel_id&success=1");
            exit();
        } catch (Exception $e) {
            $errors[] = "Failed to save room type: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $room_type['id'] ? 'Edit' : 'Add'; ?> Room Type - <?php echo htmlspecialchars($hotel['name']); ?></title>
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
                        <h1 class="h2"><?php echo $room_type['id'] ? 'Edit' : 'Add'; ?> Room Type</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="hotels.php">Hotels</a></li>
                                <li class="breadcrumb-item"><a href="room_types.php?hotel_id=<?php echo $hotel_id; ?>"><?php echo htmlspecialchars($hotel['name']); ?></a></li>
                                <li class="breadcrumb-item active"><?php echo $room_type['id'] ? 'Edit' : 'Add'; ?> Room Type</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $room_type['id']; ?>">
                    <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Room Type Name *</label>
                                <input type="text" name="name" class="form-control"
                                    value="<?php echo htmlspecialchars($room_type['name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="4"><?php
                                                                                            echo htmlspecialchars($room_type['description']);
                                                                                            ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Price per Night *</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">$</span>
                                            </div>
                                            <input type="number" name="price" class="form-control"
                                                value="<?php echo $room_type['price']; ?>"
                                                min="0" step="0.01" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Capacity (persons) *</label>
                                        <input type="number" name="capacity" class="form-control"
                                            value="<?php echo $room_type['capacity']; ?>"
                                            min="1" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Available Rooms *</label>
                                        <input type="number" name="available_rooms" class="form-control"
                                            value="<?php echo $room_type['available_rooms']; ?>"
                                            min="0" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Amenities</label>
                                <input type="text" name="amenities" class="form-control"
                                    value="<?php echo htmlspecialchars($room_type['amenities']); ?>"
                                    placeholder="Separate with commas">
                                <small class="form-text text-muted">
                                    Example: TV, Air Conditioning, Mini Bar, Free WiFi
                                </small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Room Image</label>
                                        <?php if ($room_type['image']): ?>
                                            <div class="mb-2">
                                                <img src="../images/rooms/<?php echo htmlspecialchars($room_type['image']); ?>"
                                                    alt="Current Image" class="img-fluid">
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" name="image" class="form-control-file">
                                        <small class="form-text text-muted">
                                            Allowed formats: JPG, PNG, GIF
                                        </small>
                                    </div>

                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-save"></i> Save Room Type
                                        </button>
                                        <a href="room_types.php?hotel_id=<?php echo $hotel_id; ?>" class="btn btn-secondary btn-block">
                                            <i class="fas fa-arrow-left"></i> Back to Room Types
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>