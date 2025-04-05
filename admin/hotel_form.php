<?php
require_once 'auth.php';
requireLogin();

$hotel = [
    'id' => '',
    'name' => '',
    'description' => '',
    'location' => '',
    'address' => '',
    'rating' => '0',
    'amenities' => '',
    'image' => ''
];

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $hotel = $stmt->fetch();
    if (!$hotel) {
        header('Location: hotels.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hotel = array_merge($hotel, $_POST);
    $errors = [];

    // Validate required fields
    $required_fields = ['name', 'location', 'address'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst($field) . " is required";
        }
    }

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];

        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Invalid image format. Allowed formats: JPG, PNG, GIF";
        } else {
            $file_name = uniqid() . '_' . $_FILES['image']['name'];
            $upload_path = "../images/hotels/" . $file_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $hotel['image'] = $file_name;
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    }

    if (empty($errors)) {
        try {
            if ($hotel['id']) {
                // Update existing hotel
                $stmt = $pdo->prepare("
                    UPDATE hotels 
                    SET name = ?, description = ?, location = ?, address = ?, 
                        rating = ?, amenities = ?" .
                    ($hotel['image'] ? ", image = ?" : "") . "
                    WHERE id = ?
                ");

                $params = [
                    $hotel['name'],
                    $hotel['description'],
                    $hotel['location'],
                    $hotel['address'],
                    $hotel['rating'],
                    $hotel['amenities']
                ];

                if ($hotel['image']) {
                    $params[] = $hotel['image'];
                }
                $params[] = $hotel['id'];

                $stmt->execute($params);
            } else {
                // Insert new hotel
                $stmt = $pdo->prepare("
                    INSERT INTO hotels (name, description, location, address, rating, amenities, image)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $hotel['name'],
                    $hotel['description'],
                    $hotel['location'],
                    $hotel['address'],
                    $hotel['rating'],
                    $hotel['amenities'],
                    $hotel['image']
                ]);
            }

            header('Location: hotels.php?success=1');
            exit();
        } catch (Exception $e) {
            $errors[] = "Failed to save hotel: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $hotel['id'] ? 'Edit' : 'Add'; ?> Hotel - Admin</title>
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
                    <h1 class="h2"><?php echo $hotel['id'] ? 'Edit' : 'Add'; ?> Hotel</h1>
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
                    <input type="hidden" name="id" value="<?php echo $hotel['id']; ?>">

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Hotel Name *</label>
                                <input type="text" name="name" class="form-control"
                                    value="<?php echo htmlspecialchars($hotel['name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="4"><?php
                                                                                            echo htmlspecialchars($hotel['description']);
                                                                                            ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Location *</label>
                                        <input type="text" name="location" class="form-control"
                                            value="<?php echo htmlspecialchars($hotel['location']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Address *</label>
                                        <input type="text" name="address" class="form-control"
                                            value="<?php echo htmlspecialchars($hotel['address']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Rating</label>
                                        <input type="number" name="rating" class="form-control"
                                            value="<?php echo $hotel['rating']; ?>"
                                            min="0" max="5" step="0.1">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Amenities</label>
                                        <input type="text" name="amenities" class="form-control"
                                            value="<?php echo htmlspecialchars($hotel['amenities']); ?>"
                                            placeholder="Separate with commas">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Hotel Image</label>
                                        <?php if ($hotel['image']): ?>
                                            <div class="mb-2">
                                                <img src="../images/hotels/<?php echo htmlspecialchars($hotel['image']); ?>"
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
                                            <i class="fas fa-save"></i> Save Hotel
                                        </button>
                                        <a href="hotels.php" class="btn btn-secondary btn-block">
                                            <i class="fas fa-arrow-left"></i> Back to Hotels
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