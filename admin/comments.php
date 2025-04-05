<?php
require_once 'auth.php';
requireLogin();

// Handle status update action
if (isset($_GET['action']) && $_GET['action'] === 'update_status' && isset($_GET['id']) && isset($_GET['status'])) {
    try {
        $stmt = $pdo->prepare("UPDATE comments SET status = ? WHERE id = ?");
        $stmt->execute([$_GET['status'], $_GET['id']]);

        header('Location: ' . $_SERVER['HTTP_REFERER'] . '&success=1');
        exit();
    } catch (Exception $e) {
        $error = "Failed to update comment status";
    }
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$_GET['id']]);

        header('Location: ' . $_SERVER['HTTP_REFERER'] . '&success=1');
        exit();
    } catch (Exception $e) {
        $error = "Failed to delete comment";
    }
}

// Get filter parameters
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

// Build the query
$query = "SELECT c.*, p.title as post_title 
          FROM comments c 
          LEFT JOIN blog_posts p ON c.post_id = p.id 
          WHERE 1=1";
$params = [];

if ($post_id) {
    $query .= " AND c.post_id = ?";
    $params[] = $post_id;
}

if ($search) {
    $query .= " AND (c.name LIKE ? OR c.email LIKE ? OR c.content LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

if ($status) {
    $query .= " AND c.status = ?";
    $params[] = $status;
}

if ($date_from) {
    $query .= " AND DATE(c.created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $query .= " AND DATE(c.created_at) <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY c.created_at DESC";

// Execute query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$comments = $stmt->fetchAll();

// Get post title if post_id is provided
$post_title = '';
if ($post_id) {
    $stmt = $pdo->prepare("SELECT title FROM blog_posts WHERE id = ?");
    $stmt->execute([$post_id]);
    if ($row = $stmt->fetch()) {
        $post_title = $row['title'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Comments - Admin</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <style>
        .comment-content {
            white-space: pre-line;
        }

        .status-badge {
            min-width: 80px;
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
                    <h1 class="h2">
                        Manage Comments
                        <?php if ($post_title): ?>
                            <small class="text-muted">for "<?php echo htmlspecialchars($post_title); ?>"</small>
                        <?php endif; ?>
                    </h1>
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
                            <?php if ($post_id): ?>
                                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                            <?php endif; ?>

                            <div class="col-md-3">
                                <label>Search</label>
                                <input type="text" name="search" class="form-control"
                                    value="<?php echo htmlspecialchars($search); ?>"
                                    placeholder="Search in name, email or content...">
                            </div>
                            <div class="col-md-2">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>
                                        Pending
                                    </option>
                                    <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>
                                        Approved
                                    </option>
                                    <option value="spam" <?php echo $status === 'spam' ? 'selected' : ''; ?>>
                                        Spam
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Date From</label>
                                <input type="date" name="date_from" class="form-control"
                                    value="<?php echo $date_from; ?>">
                            </div>
                            <div class="col-md-2">
                                <label>Date To</label>
                                <input type="date" name="date_to" class="form-control"
                                    value="<?php echo $date_to; ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="<?php echo $post_id ? "comments.php?post_id=$post_id" : 'comments.php'; ?>"
                                    class="btn btn-secondary">
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
                                <?php if (!$post_id): ?>
                                    <th>Post</th>
                                <?php endif; ?>
                                <th>Author</th>
                                <th>Content</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comments as $comment): ?>
                                <tr>
                                    <td><?php echo $comment['id']; ?></td>
                                    <?php if (!$post_id): ?>
                                        <td>
                                            <a href="comments.php?post_id=<?php echo $comment['post_id']; ?>">
                                                <?php echo htmlspecialchars($comment['post_title']); ?>
                                            </a>
                                        </td>
                                    <?php endif; ?>
                                    <td>
                                        <strong><?php echo htmlspecialchars($comment['name']); ?></strong>
                                        <small class="d-block text-muted">
                                            <?php echo htmlspecialchars($comment['email']); ?>
                                        </small>
                                    </td>
                                    <td class="comment-content">
                                        <?php echo htmlspecialchars($comment['content']); ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = [
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'spam' => 'danger'
                                        ][$comment['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?php echo $status_class; ?> status-badge">
                                            <?php echo ucfirst($comment['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($comment['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <?php if ($comment['status'] !== 'approved'): ?>
                                                <a href="comments.php?action=update_status&id=<?php echo $comment['id']; ?>&status=approved<?php echo $post_id ? "&post_id=$post_id" : ''; ?>"
                                                    class="btn btn-sm btn-success" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($comment['status'] !== 'spam'): ?>
                                                <a href="comments.php?action=update_status&id=<?php echo $comment['id']; ?>&status=spam<?php echo $post_id ? "&post_id=$post_id" : ''; ?>"
                                                    class="btn btn-sm btn-warning" title="Mark as Spam">
                                                    <i class="fas fa-ban"></i>
                                                </a>
                                            <?php endif; ?>

                                            <a href="comments.php?action=delete&id=<?php echo $comment['id']; ?><?php echo $post_id ? "&post_id=$post_id" : ''; ?>"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this comment?');"
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($comments)): ?>
                                <tr>
                                    <td colspan="<?php echo $post_id ? '6' : '7'; ?>" class="text-center">
                                        No comments found
                                    </td>
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