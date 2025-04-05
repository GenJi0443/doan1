<?php
require_once 'auth.php';
requireLogin();

$tour = [
    'id' => '',
    'name' => '',
    'description' => '',
    'price' => '',
    'duration' => '',
    'location' => '',
    'image' => '',
    'rating' => '0'
];

$errors = [];
$success = false;

// If ID is provided, fetch tour data
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    if ($row = $stmt->fetch()) {
        $tour = $row;
    } else {
        header('Location: tours.php');
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    if (empty($_POST['name'])) {
        $errors['name'] = 'Name is required';
    }

    if (empty($_POST['description'])) {
        $errors['description'] = 'Description is required';
    }

    if (empty($_POST['price']) || !is_numeric($_POST['price']) || $_POST['price'] < 0) {
        $errors['price'] = 'Valid price is required';
    }

    if (empty($_POST['duration'])) {
        $errors['duration'] = 'Duration is required';
    }

    if (empty($_POST['location'])) {
        $errors['location'] = 'Location is required';
    }

    if (!empty($_POST['rating']) && (!is_numeric($_POST['rating']) || $_POST['rating'] < 0 || $_POST['rating'] > 5)) {
        $errors['rating'] = 'Rating must be between 0 and 5';
    }

    // Handle image upload
    $image = $tour['image'];
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $errors['image'] = 'Invalid file type. Allowed: ' . implode(', ', $allowed);
        } else {
            $image = uniqid() . '.' . $ext;
            $target = "../images/tours/" . $image;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $errors['image'] = 'Failed to upload image';
            }

            // Delete old image if exists
            if ($tour['image'] && file_exists("../images/tours/" . $tour['image'])) {
                unlink("../images/tours/" . $tour['image']);
            }
        }
    }

    if (empty($errors)) {
        try {
            if ($tour['id']) {
                // Update existing tour
                $stmt = $pdo->prepare("UPDATE tours SET 
                        name = ?, 
                        description = ?, 
                        price = ?,
                        duration = ?,
                        location = ?,
                        rating = ?,
                        image = ?
                    WHERE id = ?");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['price'],
                    $_POST['duration'],
                    $_POST['location'],
                    $_POST['rating'],
                    $image,
                    $tour['id']
                ]);
            } else {
                // Insert new tour
                $stmt = $pdo->prepare("INSERT INTO tours (name, description, price, duration, location, rating, image) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['price'],
                    $_POST['duration'],
                    $_POST['location'],
                    $_POST['rating'],
                    $image
                ]);
            }

            header('Location: tours.php?success=1');
            exit();
        } catch (Exception $e) {
            $errors[] = 'Failed to save tour: ' . $e->getMessage();
        }
    }
}

// Get unique locations for suggestions
$locations = $pdo->query("SELECT DISTINCT location FROM tours ORDER BY location")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tour['id'] ? 'Edit' : 'Add'; ?> Tour - Admin</title>
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
                    <h1 class="h2"><?php echo $tour['id'] ? 'Edit' : 'Add'; ?> Tour</h1>
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

                <div class="card">
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Name *</label>
                                        <input type="text" name="name" class="form-control" required
                                            value="<?php echo htmlspecialchars($tour['name']); ?>">
                                    </div>

                                    <div class="form-group">
                                        <label>Description *</label>
                                        <textarea name="description" class="form-control" rows="5" required><?php
                                                                                                            echo htmlspecialchars($tour['description']);
                                                                                                            ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Price * ($)</label>
                                                <input type="number" name="price" class="form-control" required
                                                    min="0" step="0.01" value="<?php echo $tour['price']; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Duration *</label>
                                                <input type="text" name="duration" class="form-control" required
                                                    placeholder="e.g. 5 days 4 nights"
                                                    value="<?php echo htmlspecialchars($tour['duration']); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Location *</label>
                                                <input type="text" name="location" class="form-control" required
                                                    list="locations"
                                                    value="<?php echo htmlspecialchars($tour['location']); ?>">
                                                <datalist id="locations">
                                                    <?php foreach ($locations as $loc): ?>
                                                        <option value="<?php echo htmlspecialchars($loc); ?>">
                                                        <?php endforeach; ?>
                                                </datalist>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Rating (0-5)</label>
                                                <input type="number" name="rating" class="form-control"
                                                    min="0" max="5" step="0.1"
                                                    value="<?php echo $tour['rating']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tour Image</label>
                                        <?php if ($tour['image']): ?>
                                            <div class="mb-2">
                                                <img src="../images/tours/<?php echo htmlspecialchars($tour['image']); ?>"
                                                    class="img-thumbnail" alt="Current tour image">
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" name="image" class="form-control-file" accept="image/*">
                                        <small class="form-text text-muted">
                                            Allowed formats: JPG, JPEG, PNG, GIF
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Tour
                                </button>
                                <a href="tours.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>