<?php
session_start();
include 'includes/header.php';
require_once '../config/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if user is admin (with safe check)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // For development, you can temporarily comment this out
    // header('Location: ../login.php');
    // exit();
    
    // Or set temporary admin access for testing
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'admin';
    $_SESSION['user_name'] = 'Admin';
}

// Database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create news table if it doesn't exist
$createTableSQL = "
CREATE TABLE IF NOT EXISTS `news` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL UNIQUE,
    `content` text NOT NULL,
    `excerpt` text,
    `category` varchar(50) DEFAULT 'general',
    `image` varchar(255),
    `status` enum('draft','published','scheduled') DEFAULT 'draft',
    `featured` tinyint(1) DEFAULT 0,
    `author_id` int(11) DEFAULT 1,
    `views` int(11) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_status` (`status`),
    KEY `idx_featured` (`featured`),
    KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    $pdo->exec($createTableSQL);
} catch(PDOException $e) {
    // Table might already exist, that's ok
}

// Rest of your news.php code continues here...
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = $_POST['title'];
                $slug = $_POST['slug'] ?: generateSlug($title);
                $content = $_POST['content'];
                $excerpt = $_POST['excerpt'] ?? '';
                $category = $_POST['category'] ?? 'general';
                $status = $_POST['status'] ?? 'draft';
                $featured = isset($_POST['featured']) ? 1 : 0;
                $author_id = $_SESSION['user_id'];
                
                // Handle image upload
                $image_path = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $upload_dir = '../uploads/news/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $file_name = uniqid() . '.' . $file_extension;
                    $target_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                        $image_path = 'uploads/news/' . $file_name;
                    }
                }
                
                $stmt = $pdo->prepare("INSERT INTO news (title, slug, content, excerpt, category, image, status, featured, author_id) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $slug, $content, $excerpt, $category, $image_path, $status, $featured, $author_id]);
                
                $_SESSION['success'] = "News article added successfully!";
                header('Location: news.php');
                exit();
                break;
                
            case 'update':
                $id = $_POST['id'];
                $title = $_POST['title'];
                $slug = $_POST['slug'];
                $content = $_POST['content'];
                $excerpt = $_POST['excerpt'] ?? '';
                $category = $_POST['category'] ?? 'general';
                $status = $_POST['status'] ?? 'draft';
                $featured = isset($_POST['featured']) ? 1 : 0;
                
                // Get current image
                $stmt = $pdo->prepare("SELECT image FROM news WHERE id = ?");
                $stmt->execute([$id]);
                $current_image = $stmt->fetchColumn();
                
                // Handle image upload
                $image_path = $current_image;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $upload_dir = '../uploads/news/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $file_name = uniqid() . '.' . $file_extension;
                    $target_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                        // Delete old image if exists
                        if ($current_image && file_exists('../' . $current_image)) {
                            unlink('../' . $current_image);
                        }
                        $image_path = 'uploads/news/' . $file_name;
                    }
                }
                
                $stmt = $pdo->prepare("UPDATE news SET title = ?, slug = ?, content = ?, excerpt = ?, 
                                      category = ?, image = ?, status = ?, featured = ?, updated_at = NOW() 
                                      WHERE id = ?");
                $stmt->execute([$title, $slug, $content, $excerpt, $category, $image_path, $status, $featured, $id]);
                
                $_SESSION['success'] = "News article updated successfully!";
                header('Location: news.php');
                exit();
                break;
                
            case 'delete':
                $id = $_POST['id'];
                
                // Get image path before deletion
                $stmt = $pdo->prepare("SELECT image FROM news WHERE id = ?");
                $stmt->execute([$id]);
                $image = $stmt->fetchColumn();
                
                // Delete the record
                $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
                $stmt->execute([$id]);
                
                // Delete image file if exists
                if ($image && file_exists('../' . $image)) {
                    unlink('../' . $image);
                }
                
                $_SESSION['success'] = "News article deleted successfully!";
                header('Location: news.php');
                exit();
                break;
                
            case 'toggle_status':
                $id = $_POST['id'];
                $stmt = $pdo->prepare("UPDATE news SET status = IF(status = 'published', 'draft', 'published'), updated_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                
                $_SESSION['success'] = "Status updated successfully!";
                header('Location: news.php');
                exit();
                break;
                
            case 'toggle_featured':
                $id = $_POST['id'];
                $stmt = $pdo->prepare("UPDATE news SET featured = IF(featured = 1, 0, 1), updated_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                
                $_SESSION['success'] = "Featured status updated successfully!";
                header('Location: news.php');
                exit();
                break;
        }
    }
}

// Function to generate slug
function generateSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Search and filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_category = isset($_GET['category']) ? $_GET['category'] : '';

// Build WHERE clause
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(title LIKE ? OR content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter_status) {
    $where_conditions[] = "status = ?";
    $params[] = $filter_status;
}

if ($filter_category) {
    $where_conditions[] = "category = ?";
    $params[] = $filter_category;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total records for pagination
$stmt = $pdo->prepare("SELECT COUNT(*) FROM news $where_clause");
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Fetch news articles  
$sql = "SELECT n.* FROM news n $where_clause ORDER BY n.created_at DESC LIMIT $offset, $records_per_page";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$news = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn(),
    'published' => $pdo->query("SELECT COUNT(*) FROM news WHERE status = 'published'")->fetchColumn(),
    'draft' => $pdo->query("SELECT COUNT(*) FROM news WHERE status = 'draft'")->fetchColumn(),
    'featured' => $pdo->query("SELECT COUNT(*) FROM news WHERE featured = 1")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage News - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Main Content (sem sidebar por enquanto) -->
            <main class="col-12 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage News</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNewsModal">
                            <i class="bi bi-plus-circle"></i> Add News Article
                        </button>
                    </div>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Total Articles</h5>
                                <h2 class="text-primary"><?php echo $stats['total']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Published</h5>
                                <h2 class="text-success"><?php echo $stats['published']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Drafts</h5>
                                <h2 class="text-warning"><?php echo $stats['draft']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Featured</h5>
                                <h2 class="text-danger"><?php echo $stats['featured']; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Search news..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="published" <?php echo $filter_status === 'published' ? 'selected' : ''; ?>>Published</option>
                                    <option value="draft" <?php echo $filter_status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="scheduled" <?php echo $filter_status === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    <option value="announcement" <?php echo $filter_category === 'announcement' ? 'selected' : ''; ?>>Announcement</option>
                                    <option value="update" <?php echo $filter_category === 'update' ? 'selected' : ''; ?>>Update</option>
                                    <option value="tutorial" <?php echo $filter_category === 'tutorial' ? 'selected' : ''; ?>>Tutorial</option>
                                    <option value="event" <?php echo $filter_category === 'event' ? 'selected' : ''; ?>>Event</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                            <div class="col-md-2">
                                <a href="news.php" class="btn btn-secondary w-100">Clear</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- News Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Featured</th>
                                <th>Views</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($news)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">No news articles found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($news as $article): ?>
                                    <tr>
                                        <td><?php echo $article['id']; ?></td>
                                        <td><?php echo htmlspecialchars($article['title']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo ucfirst($article['category'] ?? 'general'); ?></span></td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-link p-0">
                                                    <span class="badge bg-<?php echo $article['status'] === 'published' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($article['status']); ?>
                                                    </span>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_featured">
                                                <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                                                <button type="submit" class="btn btn-link p-0">
                                                    <i class="bi bi-star-fill <?php echo $article['featured'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td><?php echo number_format($article['views']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($article['created_at'])); ?></td>
                                        <td>
                                            <a href="news-edit.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Add News Modal -->
    <div class="modal fade" id="addNewsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add News Article</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug">
                        </div>
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Excerpt</label>
                            <textarea class="form-control" id="excerpt" name="excerpt" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Content *</label>
                            <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="general">General</option>
                                    <option value="announcement">Announcement</option>
                                    <option value="update">Update</option>
                                    <option value="tutorial">Tutorial</option>
                                    <option value="event">Event</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1">
                            <label class="form-check-label" for="featured">Featured Article</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Article</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>