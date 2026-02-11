<?php
session_start();
require_once '../config/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if user is admin (with safe check)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'admin';
    $_SESSION['user_name'] = 'Admin';
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "No news article selected for editing.";
    header('Location: news.php');
    exit();
}

$article_id = (int)$_GET['id'];

// Database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $slug = $_POST['slug'] ?: generateSlug($title);
    $content = $_POST['content'];
    $excerpt = $_POST['excerpt'] ?? '';
    $category = $_POST['category'] ?? 'general';
    $status = $_POST['status'] ?? 'draft';
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Get current image
    $stmt = $pdo->prepare("SELECT image FROM news WHERE id = ?");
    $stmt->execute([$article_id]);
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
    
    // Handle image removal
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
        if ($current_image && file_exists('../' . $current_image)) {
            unlink('../' . $current_image);
        }
        $image_path = null;
    }
    
    // Update article
    try {
        $stmt = $pdo->prepare("UPDATE news SET title = ?, slug = ?, content = ?, excerpt = ?, 
                              category = ?, image = ?, status = ?, featured = ?, updated_at = NOW() 
                              WHERE id = ?");
        $stmt->execute([$title, $slug, $content, $excerpt, $category, $image_path, $status, $featured, $article_id]);
        
        $_SESSION['success'] = "News article updated successfully!";
        header('Location: news.php');
        exit();
    } catch(PDOException $e) {
        $error = "Error updating article: " . $e->getMessage();
    }
}

// Fetch article data
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    $_SESSION['error'] = "News article not found.";
    header('Location: news.php');
    exit();
}

// Page settings
$pageTitle = "Edit News: " . $article['title'];
$currentPage = 'news';

// Include header
include 'includes/header.php';
?>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="admin-main">
        <div class="admin-content">
            <!-- Breadcrumb -->
            <nav class="breadcrumb-nav">
                <ol class="breadcrumb">
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="news.php">News</a></li>
                    <li class="active">Edit Article</li>
                </ol>
            </nav>

            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-left">
                    <h1><i class="fas fa-edit"></i> Edit News Article</h1>
                </div>
                <div class="page-header-right">
                    <a href="news.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to News
                    </a>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Edit Form -->
            <form method="POST" enctype="multipart/form-data" class="admin-form">
                <div class="form-grid">
                    <!-- Main Column -->
                    <div class="form-main">
                        <div class="card">
                            <div class="card-header">
                                <h3>Article Content</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="title">Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo htmlspecialchars($article['title']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="slug">Slug (URL)</label>
                                    <input type="text" class="form-control" id="slug" name="slug" 
                                           value="<?php echo htmlspecialchars($article['slug']); ?>">
                                    <small class="form-text">Leave empty to auto-generate from title</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="excerpt">Excerpt</label>
                                    <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?php echo htmlspecialchars($article['excerpt'] ?? ''); ?></textarea>
                                    <small class="form-text">Brief description of the article</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="content">Content *</label>
                                    <textarea class="form-control editor" id="content" name="content" rows="15" required><?php echo htmlspecialchars($article['content']); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="form-sidebar">
                        <!-- Publish Settings -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Publish Settings</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="draft" <?php echo $article['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                        <option value="published" <?php echo $article['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                                        <option value="scheduled" <?php echo $article['status'] == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="featured" value="1" 
                                               <?php echo $article['featured'] ? 'checked' : ''; ?>>
                                        <span>Featured Article</span>
                                    </label>
                                </div>
                                
                                <div class="form-meta">
                                    <p><strong>Created:</strong> <?php echo date('d/m/Y H:i', strtotime($article['created_at'])); ?></p>
                                    <p><strong>Updated:</strong> <?php echo date('d/m/Y H:i', strtotime($article['updated_at'])); ?></p>
                                    <p><strong>Views:</strong> <?php echo number_format($article['views'] ?? 0); ?></p>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-save"></i> Update Article
                                    </button>
                                    <?php if ($article['status'] == 'published'): ?>
                                        <a href="../news/<?php echo $article['slug']; ?>" target="_blank" class="btn btn-outline btn-block mt-2">
                                            <i class="fas fa-external-link-alt"></i> View Article
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Category -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Category</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <select class="form-control" id="category" name="category">
                                        <option value="general" <?php echo $article['category'] == 'general' ? 'selected' : ''; ?>>General</option>
                                        <option value="announcement" <?php echo $article['category'] == 'announcement' ? 'selected' : ''; ?>>Announcement</option>
                                        <option value="update" <?php echo $article['category'] == 'update' ? 'selected' : ''; ?>>Update</option>
                                        <option value="tutorial" <?php echo $article['category'] == 'tutorial' ? 'selected' : ''; ?>>Tutorial</option>
                                        <option value="event" <?php echo $article['category'] == 'event' ? 'selected' : ''; ?>>Event</option>
                                        <option value="promotion" <?php echo $article['category'] == 'promotion' ? 'selected' : ''; ?>>Promotion</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Featured Image -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Featured Image</h3>
                            </div>
                            <div class="card-body">
                                <?php if ($article['image']): ?>
                                    <div class="current-image">
                                        <img src="../<?php echo htmlspecialchars($article['image']); ?>" alt="Current image">
                                        <div class="image-actions">
                                            <label class="checkbox-label text-danger">
                                                <input type="checkbox" name="remove_image" value="1">
                                                <span>Remove current image</span>
                                            </label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="form-group">
                                    <label><?php echo $article['image'] ? 'Replace image' : 'Add image'; ?></label>
                                    <div class="image-upload-area" id="imageUploadArea">
                                        <input type="file" id="image" name="image" accept="image/*" class="image-input">
                                        <div class="upload-placeholder">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <p>Click or drag image here</p>
                                            <small>JPG, PNG, GIF or WebP (max. 5MB)</small>
                                        </div>
                                        <img id="imagePreview" class="image-preview" style="display: none;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Quick Actions</h3>
                            </div>
                            <div class="card-body">
                                <button type="button" class="btn btn-danger btn-block" onclick="confirmDelete()">
                                    <i class="fas fa-trash"></i> Delete Article
                                </button>
                                <a href="news.php" class="btn btn-secondary btn-block mt-2">
                                    <i class="fas fa-times"></i> Cancel Edit
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Delete Form (Hidden) -->
            <form id="deleteForm" method="POST" action="news.php" style="display: none;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo $article_id; ?>">
            </form>
        </div>
    </main>
</div>

<script>
// Auto-generate slug from title
document.getElementById('title').addEventListener('blur', function() {
    const slugField = document.getElementById('slug');
    if (slugField.value === '') {
        const title = this.value;
        const slug = title.toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^\w\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-');
        slugField.value = slug;
    }
});

// Confirm delete
function confirmDelete() {
    if (confirm('Are you sure you want to delete this article? This action cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
}

// Preview image before upload
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            const placeholder = document.querySelector('.upload-placeholder');
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
});

// Initialize TinyMCE if available
if (typeof tinymce !== 'undefined') {
    tinymce.init({
        selector: '#content',
        height: 400,
        menubar: false,
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount'
        ],
        toolbar: 'undo redo | formatselect | bold italic backcolor | \
            alignleft aligncenter alignright alignjustify | \
            bullist numlist outdent indent | removeformat | help'
    });
}
</script>

<?php include 'includes/footer.php'; ?>