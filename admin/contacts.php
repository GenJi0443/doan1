<?php
require_once 'auth.php';
requireLogin();

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->execute([$_GET['id']]);

        header('Location: contacts.php?success=1');
        exit();
    } catch (Exception $e) {
        $error = "Failed to delete contact";
    }
}

// Handle mark as read/unread action
if (isset($_GET['action']) && $_GET['action'] === 'toggle_read' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE contacts SET is_read = NOT is_read WHERE id = ?");
        $stmt->execute([$_GET['id']]);

        header('Location: contacts.php?success=1');
        exit();
    } catch (Exception $e) {
        $error = "Failed to update contact status";
    }
}

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$is_read = isset($_GET['is_read']) ? $_GET['is_read'] : '';

// Build the query
$query = "SELECT * FROM contacts WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if ($date_from) {
    $query .= " AND DATE(created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $query .= " AND DATE(created_at) <= ?";
    $params[] = $date_to;
}

if ($is_read !== '') {
    $query .= " AND is_read = ?";
    $params[] = $is_read;
}

$query .= " ORDER BY created_at DESC";

// Execute query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$contacts = $stmt->fetchAll();

// Get counts
$stmt = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(is_read = 1) as read_count,
    SUM(is_read = 0) as unread_count
FROM contacts");
$counts = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contacts - Admin</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <style>
        .message-content {
            white-space: pre-line;
            max-height: 100px;
            overflow-y: auto;
        }

        .unread {
            font-weight: bold;
            background-color: rgba(0, 123, 255, 0.05);
        }

        .contact-stats {
            font-size: 0.9rem;
        }

        .contact-stats .badge {
            font-size: 0.8rem;
            padding: 0.4em 0.8em;
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
                    <h1 class="h2">Manage Contacts</h1>
                    <div class="contact-stats">
                        <span class="badge badge-primary">Total: <?php echo $counts['total']; ?></span>
                        <span class="badge badge-success">Read: <?php echo $counts['read_count']; ?></span>
                        <span class="badge badge-warning">Unread: <?php echo $counts['unread_count']; ?></span>
                    </div>
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
                                    placeholder="Search in name, email, subject or message...">
                            </div>
                            <div class="col-md-2">
                                <label>Status</label>
                                <select name="is_read" class="form-control">
                                    <option value="">All Messages</option>
                                    <option value="1" <?php echo $is_read === '1' ? 'selected' : ''; ?>>Read</option>
                                    <option value="0" <?php echo $is_read === '0' ? 'selected' : ''; ?>>Unread</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Date From</label>
                                <input type="date" name="date_from" class="form-control"
                                    value="<?php echo $date_from; ?>">
                            </div>
                            <div class="col-md-2">
                                <label>Date To</label>
                                <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="contacts.php" class="btn btn-secondary">
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
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contacts as $contact): ?>
                                <tr class="<?php echo !$contact['is_read'] ? 'unread' : ''; ?>">
                                    <td><?php echo $contact['id']; ?></td>
                                    <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>">
                                            <?php echo htmlspecialchars($contact['email']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($contact['subject']); ?></td>
                                    <td>
                                        <div class="message-content">
                                            <?php echo nl2br(htmlspecialchars($contact['message'])); ?>
                                        </div>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($contact['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="contacts.php?action=toggle_read&id=<?php echo $contact['id']; ?>"
                                                class="btn btn-sm <?php echo $contact['is_read'] ? 'btn-warning' : 'btn-success'; ?>"
                                                title="<?php echo $contact['is_read'] ? 'Mark as Unread' : 'Mark as Read'; ?>">
                                                <i
                                                    class="fas <?php echo $contact['is_read'] ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                            </a>
                                            <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>?subject=Re: <?php echo htmlspecialchars($contact['subject']); ?>"
                                                class="btn btn-sm btn-info" title="Reply">
                                                <i class="fas fa-reply"></i>
                                            </a>
                                            <a href="contacts.php?action=delete&id=<?php echo $contact['id']; ?>"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this message?');"
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($contacts)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No contacts found</td>
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