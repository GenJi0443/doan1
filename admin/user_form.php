<?php
require_once 'auth.php';
requireLogin();

$user = [
    'id' => '',
    'name' => '',
    'email' => '',
    'phone' => '',
    'role' => 'user',
    'status' => 1,
    'avatar' => ''
];

$errors = [];
$success = false;

// If ID is provided, fetch user data
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    if ($row = $stmt->fetch()) {
        $user = $row;
    } else {
        header('Location: users.php');
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    if (empty($_POST['name'])) {
        $errors['name'] = 'Name is required';
    }

    if (empty($_POST['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } else {
        // Check if email exists (for new users or when changing email)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$_POST['email'], $user['id'] ?: 0]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Email already exists';
        }
    }

    if (!empty($_POST['password']) && strlen($_POST['password']) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }

    // Handle avatar upload
    $avatar = $user['avatar'];
    if (!empty($_FILES['avatar']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $errors['avatar'] = 'Invalid file type. Allowed: ' . implode(', ', $allowed);
        } else {
            $avatar = uniqid() . '.' . $ext;
            $target = "../images/avatars/" . $avatar;

            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
                $errors['avatar'] = 'Failed to upload image';
            }

            // Delete old avatar if exists
            if ($user['avatar'] && file_exists("../images/avatars/" . $user['avatar'])) {
                unlink("../images/avatars/" . $user['avatar']);
            }
        }
    }

    if (empty($errors)) {
        try {
            if ($user['id']) {
                // Update existing user
                $sql = "UPDATE users SET 
                        name = ?, 
                        email = ?, 
                        phone = ?,
                        role = ?,
                        status = ?,
                        avatar = ?";
                $params = [
                    $_POST['name'],
                    $_POST['email'],
                    $_POST['phone'],
                    $_POST['role'],
                    isset($_POST['status']) ? 1 : 0,
                    $avatar
                ];

                if (!empty($_POST['password'])) {
                    $sql .= ", password = ?";
                    $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }

                $sql .= " WHERE id = ?";
                $params[] = $user['id'];

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            } else {
                // Insert new user
                if (empty($_POST['password'])) {
                    $errors['password'] = 'Password is required for new users';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role, status, avatar, created_at) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([
                        $_POST['name'],
                        $_POST['email'],
                        $_POST['phone'],
                        password_hash($_POST['password'], PASSWORD_DEFAULT),
                        $_POST['role'],
                        isset($_POST['status']) ? 1 : 0,
                        $avatar
                    ]);
                }
            }

            if (empty($errors)) {
                header('Location: users.php?success=1');
                exit();
            }
        } catch (Exception $e) {
            $errors[] = 'Failed to save user: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $user['id'] ? 'Edit' : 'Add'; ?> User - Admin</title>
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
                    <h1 class="h2"><?php echo $user['id'] ? 'Edit' : 'Add'; ?> User</h1>
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
                                            value="<?php echo htmlspecialchars($user['name']); ?>">
                                    </div>

                                    <div class="form-group">
                                        <label>Email *</label>
                                        <input type="email" name="email" class="form-control" required
                                            value="<?php echo htmlspecialchars($user['email']); ?>">
                                    </div>

                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="tel" name="phone" class="form-control"
                                            value="<?php echo htmlspecialchars($user['phone']); ?>">
                                    </div>

                                    <div class="form-group">
                                        <label><?php echo $user['id'] ? 'New Password (leave blank to keep current)' : 'Password *'; ?></label>
                                        <input type="password" name="password" class="form-control"
                                            <?php echo $user['id'] ? '' : 'required'; ?>>
                                    </div>

                                    <div class="form-group">
                                        <label>Role</label>
                                        <select name="role" class="form-control">
                                            <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="statusSwitch"
                                                name="status" <?php echo $user['status'] ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="statusSwitch">Active</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Avatar</label>
                                        <?php if ($user['avatar']): ?>
                                            <div class="mb-2">
                                                <img src="../images/avatars/<?php echo htmlspecialchars($user['avatar']); ?>"
                                                    class="img-thumbnail" alt="Current avatar">
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" name="avatar" class="form-control-file" accept="image/*">
                                        <small class="form-text text-muted">
                                            Allowed formats: JPG, JPEG, PNG, GIF
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save User
                                </button>
                                <a href="users.php" class="btn btn-secondary">
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