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
    
    // Insert article
    try {
        $stmt = $pdo->prepare("INSERT INTO news (title, slug, content, excerpt, category, image, status, featured, author_id) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $content, $excerpt, $category, $image_path, $status, $featured, $author_id]);
        
        $_SESSION['success'] = "News article created successfully!";
        header('Location: news.php');
        exit();
    } catch(PDOException $e) {
        $error = "Error creating article: " . $e->getMessage();
    }
}

// Page variables
$pageTitle = "Create News Article";
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
                <a href="news.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to News
                </a>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="admin-form">
                <div class="form-grid">
                    <div class="form-main">
                        <div class="form-card">
                            <div class="form-card-header">
                                <h3>Article Content</h3>
                            </div>
                            <div class="form-card-body">
                                <div class="form-group">
                                    <label for="title">Title *</label>
                                    <input type="text" class="form-input" id="title" name="title" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="slug">Slug (URL)</label>
                                    <input type="text" class="form-input" id="slug" name="slug">
                                    <small class="form-help">Leave empty to auto-generate from title</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="excerpt">Excerpt</label>
                                    <textarea class="form-textarea" id="excerpt" name="excerpt" rows="3"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="content">Content *</label>
                                    <textarea class="form-textarea" id="content" name="content" rows="15" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-sidebar">
                        <div class="form-card">
                            <div class="form-card-header">
                                <h3>Publish Settings</h3>
                            </div>
                            <div class="form-card-body">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="draft">Draft</option>
                                        <option value="published">Published</option>
                                        <option value="scheduled">Scheduled</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="category">Category</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="general">General</option>
                                        <option value="announcement">Announcement</option>
                                        <option value="update">Update</option>
                                        <option value="tutorial">Tutorial</option>
                                        <option value="event">Event</option>
                                        <option value="promotion">Promotion</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="featured" value="1">
                                        <span>Featured Article</span>
                                    </label>
                                </div>
                                
                                <div class="form-group">
                                    <label for="image">Featured Image</label>
                                    <input type="file" class="form-file" id="image" name="image" accept="image/*">
                                    <div id="imagePreview" class="image-preview"></div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i> Create Article
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
    
    <script>
    // Auto-generate slug
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
    
    // Image preview
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('imagePreview');
        
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '';
        }
    });
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>