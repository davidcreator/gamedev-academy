<?php
// classes/User.php

class User {
    private $db;
    private $data = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function find(int $id): ?array {
        return $this->db->fetch("SELECT * FROM users WHERE id = ?", [$id]);
    }
    
    public function findByEmail(string $email): ?array {
        return $this->db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
    }
    
    public function findByUsername(string $username): ?array {
        return $this->db->fetch("SELECT * FROM users WHERE username = ?", [$username]);
    }
    
    public function create(array $data): int {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $this->db->insert('users', $data);
    }
    
    public function update(int $id, array $data): bool {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return $this->db->update('users', $data, 'id = :id', ['id' => $id]) > 0;
    }
    
    public function delete(int $id): bool {
        return $this->db->delete('users', 'id = ?', [$id]) > 0;
    }
    
    public function getAll(int $limit = 50, int $offset = 0): array {
        return $this->db->fetchAll(
            "SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }
    
    public function count(): int {
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM users");
        return (int) $result['total'];
    }
    
    public function updateLastActivity(int $userId): void {
        $this->db->query(
            "UPDATE users SET last_activity = CURDATE() WHERE id = ?",
            [$userId]
        );
    }
    
    public function getLevel(int $xp): array {
        return $this->db->fetch(
            "SELECT * FROM levels WHERE xp_required <= ? ORDER BY level_number DESC LIMIT 1",
            [$xp]
        ) ?? ['level_number' => 1, 'title' => 'Iniciante', 'badge_icon' => '🌱', 'xp_required' => 0];
    }
    
    public function getNextLevel(int $currentLevel): ?array {
        return $this->db->fetch(
            "SELECT * FROM levels WHERE level_number = ?",
            [$currentLevel + 1]
        );
    }
    
    public function getStats(int $userId): array {
        $user = $this->find($userId);
        
        $coursesEnrolled = $this->db->fetch(
            "SELECT COUNT(*) as total FROM enrollments WHERE user_id = ?",
            [$userId]
        )['total'];
        
        $coursesCompleted = $this->db->fetch(
            "SELECT COUNT(*) as total FROM enrollments WHERE user_id = ? AND completed_at IS NOT NULL",
            [$userId]
        )['total'];
        
        $lessonsCompleted = $this->db->fetch(
            "SELECT COUNT(*) as total FROM lesson_progress WHERE user_id = ? AND is_completed = 1",
            [$userId]
        )['total'];
        
        $achievements = $this->db->fetch(
            "SELECT COUNT(*) as total FROM user_achievements WHERE user_id = ?",
            [$userId]
        )['total'];
        
        return [
            'xp_total' => $user['xp_total'] ?? 0,
            'level' => $user['level'] ?? 1,
            'streak_days' => $user['streak_days'] ?? 0,
            'coins' => $user['coins'] ?? 0,
            'courses_enrolled' => $coursesEnrolled,
            'courses_completed' => $coursesCompleted,
            'lessons_completed' => $lessonsCompleted,
            'achievements' => $achievements
        ];
    }
    
    public function getLeaderboard(int $limit = 10): array {
        return $this->db->fetchAll(
            "SELECT id, username, full_name, avatar, xp_total, level, streak_days 
             FROM users 
             WHERE role = 'student' AND is_active = 1 
             ORDER BY xp_total DESC 
             LIMIT ?",
            [$limit]
        );
    }
}