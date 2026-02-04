<?php
// classes/Course.php

class Course {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function find(int $id): ?array {
        return $this->db->fetch(
            "SELECT c.*, cat.name as category_name, u.full_name as instructor_name 
             FROM courses c 
             LEFT JOIN categories cat ON c.category_id = cat.id 
             LEFT JOIN users u ON c.instructor_id = u.id 
             WHERE c.id = ?",
            [$id]
        );
    }
    
    public function findBySlug(string $slug): ?array {
        return $this->db->fetch(
            "SELECT c.*, cat.name as category_name, u.full_name as instructor_name 
             FROM courses c 
             LEFT JOIN categories cat ON c.category_id = cat.id 
             LEFT JOIN users u ON c.instructor_id = u.id 
             WHERE c.slug = ?",
            [$slug]
        );
    }
    
    public function getAll(bool $publishedOnly = true, int $limit = 50): array {
        $where = $publishedOnly ? "WHERE c.is_published = 1" : "";
        
        return $this->db->fetchAll(
            "SELECT c.*, cat.name as category_name, u.full_name as instructor_name,
             (SELECT COUNT(*) FROM modules WHERE course_id = c.id) as total_modules,
             (SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id = m.id WHERE m.course_id = c.id) as total_lessons
             FROM courses c 
             LEFT JOIN categories cat ON c.category_id = cat.id 
             LEFT JOIN users u ON c.instructor_id = u.id 
             {$where}
             ORDER BY c.created_at DESC 
             LIMIT ?",
            [$limit]
        );
    }
    
    public function getFeatured(int $limit = 6): array {
        return $this->db->fetchAll(
            "SELECT c.*, cat.name as category_name 
             FROM courses c 
             LEFT JOIN categories cat ON c.category_id = cat.id 
             WHERE c.is_published = 1 
             ORDER BY c.total_students DESC, c.average_rating DESC 
             LIMIT ?",
            [$limit]
        );
    }
    
    public function create(array $data): int {
        $data['slug'] = $this->generateSlug($data['title']);
        return $this->db->insert('courses', $data);
    }
    
    public function update(int $id, array $data): bool {
        if (isset($data['title'])) {
            $data['slug'] = $this->generateSlug($data['title'], $id);
        }
        return $this->db->update('courses', $data, 'id = :id', ['id' => $id]) > 0;
    }
    
    public function delete(int $id): bool {
        return $this->db->delete('courses', 'id = ?', [$id]) > 0;
    }
    
    public function getModules(int $courseId): array {
        return $this->db->fetchAll(
            "SELECT m.*, 
             (SELECT COUNT(*) FROM lessons WHERE module_id = m.id) as total_lessons
             FROM modules m 
             WHERE m.course_id = ? 
             ORDER BY m.order_index",
            [$courseId]
        );
    }
    
    public function getLessons(int $moduleId): array {
        return $this->db->fetchAll(
            "SELECT * FROM lessons WHERE module_id = ? ORDER BY order_index",
            [$moduleId]
        );
    }
    
    public function getCategories(): array {
        return $this->db->fetchAll("SELECT * FROM categories ORDER BY name");
    }
    
    public function isEnrolled(int $userId, int $courseId): bool {
        $enrollment = $this->db->fetch(
            "SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?",
            [$userId, $courseId]
        );
        return $enrollment !== null;
    }
    
    public function enroll(int $userId, int $courseId): bool {
        if ($this->isEnrolled($userId, $courseId)) {
            return false;
        }
        
        $this->db->insert('enrollments', [
            'user_id' => $userId,
            'course_id' => $courseId
        ]);
        
        // Incrementar contador de estudantes
        $this->db->query(
            "UPDATE courses SET total_students = total_students + 1 WHERE id = ?",
            [$courseId]
        );
        
        return true;
    }
    
    public function getUserCourses(int $userId): array {
        return $this->db->fetchAll(
            "SELECT c.*, e.progress_percentage, e.started_at, e.completed_at,
             cat.name as category_name
             FROM enrollments e 
             JOIN courses c ON e.course_id = c.id 
             LEFT JOIN categories cat ON c.category_id = cat.id
             WHERE e.user_id = ? 
             ORDER BY e.last_accessed DESC",
            [$userId]
        );
    }
    
    public function count(): int {
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM courses");
        return (int) $result['total'];
    }
    
    private function generateSlug(string $title, ?int $excludeId = null): string {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $where = $excludeId ? "slug = ? AND id != ?" : "slug = ?";
            $params = $excludeId ? [$slug, $excludeId] : [$slug];
            
            $existing = $this->db->fetch("SELECT id FROM courses WHERE {$where}", $params);
            
            if (!$existing) {
                break;
            }
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}