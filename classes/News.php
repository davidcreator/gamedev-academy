<?php
// classes/News.php

class News {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function find(int $id): ?array {
        return $this->db->fetch(
            "SELECT n.*, u.full_name as author_name, u.avatar as author_avatar 
             FROM news n 
             LEFT JOIN users u ON n.author_id = u.id 
             WHERE n.id = ?",
            [$id]
        );
    }
    
    public function findBySlug(string $slug): ?array {
        return $this->db->fetch(
            "SELECT n.*, u.full_name as author_name, u.avatar as author_avatar 
             FROM news n 
             LEFT JOIN users u ON n.author_id = u.id 
             WHERE n.slug = ?",
            [$slug]
        );
    }
    
    public function getAll(bool $publishedOnly = true, int $limit = 20): array {
        $where = $publishedOnly ? "WHERE n.is_published = 1" : "";
        
        return $this->db->fetchAll(
            "SELECT n.*, u.full_name as author_name 
             FROM news n 
             LEFT JOIN users u ON n.author_id = u.id 
             {$where}
             ORDER BY n.published_at DESC, n.created_at DESC 
             LIMIT ?",
            [$limit]
        );
    }
    
    public function getFeatured(int $limit = 3): array {
        return $this->db->fetchAll(
            "SELECT n.*, u.full_name as author_name 
             FROM news n 
             LEFT JOIN users u ON n.author_id = u.id 
             WHERE n.is_published = 1 AND n.is_featured = 1 
             ORDER BY n.published_at DESC 
             LIMIT ?",
            [$limit]
        );
    }
    
    public function getLatest(int $limit = 5): array {
        return $this->db->fetchAll(
            "SELECT n.*, u.full_name as author_name 
             FROM news n 
             LEFT JOIN users u ON n.author_id = u.id 
             WHERE n.is_published = 1 
             ORDER BY n.published_at DESC 
             LIMIT ?",
            [$limit]
        );
    }
    
    public function create(array $data): int {
        $data['slug'] = $this->generateSlug($data['title']);
        if (!empty($data['is_published']) && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        return $this->db->insert('news', $data);
    }
    
    public function update(int $id, array $data): bool {
        if (isset($data['title'])) {
            $data['slug'] = $this->generateSlug($data['title'], $id);
        }
        return $this->db->update('news', $data, 'id = :id', ['id' => $id]) > 0;
    }
    
    public function delete(int $id): bool {
        return $this->db->delete('news', 'id = ?', [$id]) > 0;
    }
    
    public function incrementViews(int $id): void {
        $this->db->query("UPDATE news SET views = views + 1 WHERE id = ?", [$id]);
    }
    
    public function count(): int {
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM news");
        return (int) $result['total'];
    }
    
    private function generateSlug(string $title, ?int $excludeId = null): string {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $where = $excludeId ? "slug = ? AND id != ?" : "slug = ?";
            $params = $excludeId ? [$slug, $excludeId] : [$slug];
            
            $existing = $this->db->fetch("SELECT id FROM news WHERE {$where}", $params);
            
            if (!$existing) {
                break;
            }
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}