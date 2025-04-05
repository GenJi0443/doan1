<?php
require_once 'auth.php';
requireLogin();

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        // Get image name before delete
        $stmt = $pdo->prepare("SELECT image FROM blog_posts WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $post = $stmt->fetch();

        // Delete post
        $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->execute([$_GET['id']]);

        // Delete image file if exists
        if ($post && $post['image'] && file_exists("../images/blog/" . $post['image'])) {
            unlink("../images/blog/" . $post['image']);
        }

        header('Location: posts.php?success=1');
        exit();
    } catch (Exception $e) {
        $error = "Failed to delete post";
    }
}

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$author = isset($_GET['author']) ? trim($_GET['author']) : '';

// Build the query
$query = "SELECT * FROM blog_posts WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (title LIKE ? OR content LIKE ? OR tags LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

if ($category) {
    $query .= " AND category = ?";
    $params[] = $category;
}

if ($author) {
    $query .= " AND author = ?";
    $params[] = $author;
}

$query .= " ORDER BY created_at DESC";

// Execute query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Get unique categories and authors for filters
$categories = $pdo->query("SELECT DISTINCT category FROM blog_posts ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$authors = $pdo->query("SELECT DISTINCT author FROM blog_posts ORDER BY author")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blog Posts - Admin</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <style>
        .post-image {
            width: 100px;
            height: 60px;
            object-fit: cover;
        }

        .tags {
            font-size: 0.85rem;
        }

        .tag {
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 12px;
            margin-right: 4px;
            display: inline-block;
            margin-bottom: 4px;
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
                    <h1 class="h2">Manage Blog Posts</h1>
                    <a href="post_form.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Post
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
                            <div class="col-md-4">
                                <label>Search</label>
                                <input type="text" name="search" class="form-control"
                                    value="<?php echo htmlspecialchars($search); ?>"
                                    placeholder="Search in title, content or tags...">
                            </div>
                            <div class="col-md-3">
                                <label>Category</label>
                                <select name="category" class="form-control">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat; ?>"
                                            <?php echo $category === $cat ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Author</label>
                                <select name="author" class="form-control">
                                    <option value="">All Authors</option>
                                    <?php foreach ($authors as $auth): ?>
                                        <option value="<?php echo $auth; ?>"
                                            <?php echo $author === $auth ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($auth); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="posts.php" class="btn btn-secondary">
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
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Tags</th>
                                <th>Comments</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><?php echo $post['id']; ?></td>
                                    <td>
                                        <?php if ($post['image']): ?>
                                            <img src="../images/blog/<?php echo htmlspecialchars($post['image']); ?>"
                                                class="post-image" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                        <?php else: ?>
                                            <div class="post-image bg-secondary d-flex align-items-center justify-content-center">
                                                <i class="fas fa-image text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                        <small class="d-block text-muted">
                                            <?php echo substr(htmlspecialchars($post['content']), 0, 100) . '...'; ?>
                                        </small>
                                    </td>
                                    <td><?php echo htmlspecialchars($post['author']); ?></td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo htmlspecialchars($post['category']); ?>
                                        </span>
                                    </td>
                                    <td class="tags">
                                        <?php if ($post['tags']): ?>
                                            <?php foreach (explode(',', $post['tags']) as $tag): ?>
                                                <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?php echo $post['comments_count']; ?> comments
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="post_form.php?id=<?php echo $post['id']; ?>"
                                                class="btn btn-sm btn-info" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="comments.php?post_id=<?php echo $post['id']; ?>"
                                                class="btn btn-sm btn-success" title="View Comments">
                                                <i class="fas fa-comments"></i>
                                            </a>
                                            <a href="posts.php?action=delete&id=<?php echo $post['id']; ?>"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this post?');"
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($posts)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No posts found</td>
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