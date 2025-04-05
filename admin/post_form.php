<?php
require_once 'auth.php';
requireLogin();

$post = [
    'id' => '',
    'title' => '',
    'content' => '',
    'category' => '',
    'author' => '',
    'tags' => '',
    'image' => '',
    'comments_count' => 0
];

$errors = [];
$success = false;

// If ID is provided, fetch post data
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    if ($row = $stmt->fetch()) {
        $post = $row;
    } else {
        header('Location: posts.php');
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    if (empty($_POST['title'])) {
        $errors['title'] = 'Title is required';
    }

    if (empty($_POST['content'])) {
        $errors['content'] = 'Content is required';
    }

    if (empty($_POST['category'])) {
        $errors['category'] = 'Category is required';
    }

    if (empty($_POST['author'])) {
        $errors['author'] = 'Author is required';
    }

    // Handle image upload
    $image = $post['image'];
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $errors['image'] = 'Invalid file type. Allowed: ' . implode(', ', $allowed);
        } else {
            $image = uniqid() . '.' . $ext;
            $target = "../images/blog/" . $image;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $errors['image'] = 'Failed to upload image';
            }

            // Delete old image if exists
            if ($post['image'] && file_exists("../images/blog/" . $post['image'])) {
                unlink("../images/blog/" . $post['image']);
            }
        }
    }

    if (empty($errors)) {
        try {
            if ($post['id']) {
                // Update existing post
                $stmt = $pdo->prepare("UPDATE blog_posts SET 
                        title = ?, 
                        content = ?, 
                        category = ?,
                        author = ?,
                        tags = ?,
                        image = ?
                    WHERE id = ?");
                $stmt->execute([
                    $_POST['title'],
                    $_POST['content'],
                    $_POST['category'],
                    $_POST['author'],
                    $_POST['tags'],
                    $image,
                    $post['id']
                ]);
            } else {
                // Insert new post
                $stmt = $pdo->prepare("INSERT INTO blog_posts (title, content, category, author, tags, image) 
                                     VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['title'],
                    $_POST['content'],
                    $_POST['category'],
                    $_POST['author'],
                    $_POST['tags'],
                    $image
                ]);
            }

            header('Location: posts.php?success=1');
            exit();
        } catch (Exception $e) {
            $errors[] = 'Failed to save post: ' . $e->getMessage();
        }
    }
}

// Get unique categories and authors for suggestions
$categories = $pdo->query("SELECT DISTINCT category FROM blog_posts ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$authors = $pdo->query("SELECT DISTINCT author FROM blog_posts ORDER BY author")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post['id'] ? 'Edit' : 'Add'; ?> Blog Post - Admin</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <!-- Include TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/wbzv44j2k6sauv2mcbto636yjk4wd2qckqzpl88g3lk0loeo/tinymce/7/tinymce.min.js"
        referrerpolicy="origin"></script>
    <script>
    tinymce.init({
        selector: '#content',
        height: 400,
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount'
        ],
        toolbar: 'undo redo | formatselect | bold italic backcolor | \
                     alignleft aligncenter alignright alignjustify | \
                     bullist numlist outdent indent | removeformat | help'
    });
    </script>
</head>

<body>
    <?php include 'includes/admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin_sidebar.php'; ?>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4 py-4">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo $post['id'] ? 'Edit' : 'Add'; ?> Blog Post</h1>
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
                                        <label>Title *</label>
                                        <input type="text" name="title" class="form-control" required
                                            value="<?php echo htmlspecialchars($post['title']); ?>">
                                    </div>

                                    <div class="form-group">
                                        <label>Content *</label>
                                        <textarea id="content" name="content" class="form-control" rows="10" required><?php
                                                        echo htmlspecialchars($post['content']);
                                                        ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Category *</label>
                                                <input type="text" name="category" class="form-control" required
                                                    list="categories"
                                                    value="<?php echo htmlspecialchars($post['category']); ?>">
                                                <datalist id="categories">
                                                    <?php foreach ($categories as $cat): ?>
                                                    <option value="<?php echo htmlspecialchars($cat); ?>">
                                                        <?php endforeach; ?>
                                                </datalist>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Author *</label>
                                                <input type="text" name="author" class="form-control" required
                                                    list="authors"
                                                    value="<?php echo htmlspecialchars($post['author']); ?>">
                                                <datalist id="authors">
                                                    <?php foreach ($authors as $auth): ?>
                                                    <option value="<?php echo htmlspecialchars($auth); ?>">
                                                        <?php endforeach; ?>
                                                </datalist>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Tags</label>
                                        <input type="text" name="tags" class="form-control"
                                            value="<?php echo htmlspecialchars($post['tags']); ?>"
                                            placeholder="Enter tags separated by commas">
                                        <small class="form-text text-muted">
                                            Separate tags with commas (e.g. travel, adventure, tips)
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Featured Image</label>
                                        <?php if ($post['image']): ?>
                                        <div class="mb-2">
                                            <img src="../images/blog/<?php echo htmlspecialchars($post['image']); ?>"
                                                class="img-thumbnail" alt="Current post image">
                                        </div>
                                        <?php endif; ?>
                                        <input type="file" name="image" class="form-control-file" accept="image/*">
                                        <small class="form-text text-muted">
                                            Allowed formats: JPG, JPEG, PNG, GIF
                                        </small>
                                    </div>

                                    <?php if ($post['id']): ?>
                                    <div class="card bg-light mt-3">
                                        <div class="card-body">
                                            <h6 class="card-title">Post Statistics</h6>
                                            <p class="card-text">
                                                <strong>Comments:</strong> <?php echo $post['comments_count']; ?><br>
                                                <strong>Created:</strong>
                                                <?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Post
                                </button>
                                <a href="posts.php" class="btn btn-secondary">
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