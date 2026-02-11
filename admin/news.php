<?php
session_start();
require_once '../config/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
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

// Page variables
$pageTitle = "Manage News";
$currentPage = "news";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - GameDev Academy Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1><?php echo $pageTitle; ?></h1>
                <a href="news-create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Article
                </a>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total Articles</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['published']; ?></div>
                    <div class="stat-label">Published</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['draft']; ?></div>
                    <div class="stat-label">Drafts</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['featured']; ?></div>
                    <div class="stat-label">Featured</div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="admin-filters">
                <form method="GET" class="filter-form">
                    <input type="text" name="search" placeholder="Search articles..." 
                           value="<?php echo htmlspecialchars($search); ?>" class="form-input">
                    
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="published" <?php echo $filter_status === 'published' ? 'selected' : ''; ?>>Published</option>
                        <option value="draft" <?php echo $filter_status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="scheduled" <?php echo $filter_status === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                    </select>
                    
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <option value="announcement" <?php echo $filter_category === 'announcement' ? 'selected' : ''; ?>>Announcement</option>
                        <option value="update" <?php echo $filter_category === 'update' ? 'selected' : ''; ?>>Update</option>
                        <option value="tutorial" <?php echo $filter_category === 'tutorial' ? 'selected' : ''; ?>>Tutorial</option>
                        <option value="event" <?php echo $filter_category === 'event' ? 'selected' : ''; ?>>Event</option>
                        <option value="promotion" <?php echo $filter_category === 'promotion' ? 'selected' : ''; ?>>Promotion</option>
                    </select>
                    
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    
                    <?php if ($search || $filter_status || $filter_category): ?>
                        <a href="news.php" class="btn btn-outline">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Table -->
            <div class="admin-table-container">
                <table class="admin-table">
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
                                <td colspan="8" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-newspaper"></i>
                                        <p>No news articles found</p>
                                        <a href="news-create.php" class="btn btn-primary">Create First Article</a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($news as $article): ?>
                                <tr>
                                    <td>#<?php echo $article['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($article['title']); ?></strong>
                                        <?php if ($article['excerpt']): ?>
                                            <br><small><?php echo htmlspecialchars(substr($article['excerpt'], 0, 60)) . '...'; ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?php echo ucfirst($article['category'] ?? 'general'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                                            <button type="submit" class="btn-link">
                                                <?php if ($article['status'] === 'published'): ?>
                                                    <span class="badge badge-success">Published</span>
                                                <?php elseif ($article['status'] === 'draft'): ?>
                                                    <span class="badge badge-warning">Draft</span>
                                                <?php else: ?>
                                                    <span class="badge badge-info">Scheduled</span>
                                                <?php endif; ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_featured">
                                            <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                                            <button type="submit" class="btn-link">
                                                <?php if ($article['featured']): ?>
                                                    <i class="fas fa-star" style="color: #ffd700;"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star" style="color: #666;"></i>
                                                <?php endif; ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td><?php echo number_format($article['views'] ?? 0); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($article['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="news-edit.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this article?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($filter_status); ?>&category=<?php echo urlencode($filter_category); ?>" class="pagination-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($filter_status); ?>&category=<?php echo urlencode($filter_category); ?>" 
                           class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($filter_status); ?>&category=<?php echo urlencode($filter_category); ?>" class="pagination-link">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>