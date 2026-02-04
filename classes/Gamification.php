<?php
// classes/Gamification.php

class Gamification {
    private $db;
    private $user;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->user = new User();
    }
    
    public function addXP(int $userId, int $amount, string $actionType, string $description = '', ?int $referenceId = null, ?string $referenceType = null): void {
        // Adicionar ao histórico
        $this->db->insert('xp_history', [
            'user_id' => $userId,
            'xp_amount' => $amount,
            'action_type' => $actionType,
            'description' => $description,
            'reference_id' => $referenceId,
            'reference_type' => $referenceType
        ]);
        
        // Atualizar XP total do usuário
        $this->db->query(
            "UPDATE users SET xp_total = xp_total + ? WHERE id = ?",
            [$amount, $userId]
        );
        
        // Verificar level up
        $this->checkLevelUp($userId);
        
        // Verificar conquistas
        $this->checkAchievements($userId);
        
        // Atualizar leaderboard semanal
        $this->updateWeeklyLeaderboard($userId, $amount);
    }
    
    public function addCoins(int $userId, int $amount): void {
        $this->db->query(
            "UPDATE users SET coins = coins + ? WHERE id = ?",
            [$amount, $userId]
        );
    }
    
    public function checkLevelUp(int $userId): ?array {
        $user = $this->user->find($userId);
        $currentLevel = $this->user->getLevel($user['xp_total']);
        
        if ($currentLevel['level_number'] > $user['level']) {
            $this->user->update($userId, ['level' => $currentLevel['level_number']]);
            
            return [
                'leveled_up' => true,
                'new_level' => $currentLevel
            ];
        }
        
        return null;
    }
    
    public function checkAchievements(int $userId): array {
        $unlockedAchievements = [];
        $stats = $this->user->getStats($userId);
        $user = $this->user->find($userId);
        
        // Buscar conquistas não desbloqueadas
        $achievements = $this->db->fetchAll(
            "SELECT a.* FROM achievements a 
             WHERE a.id NOT IN (SELECT achievement_id FROM user_achievements WHERE user_id = ?)",
            [$userId]
        );
        
        foreach ($achievements as $achievement) {
            $unlocked = false;
            
            switch ($achievement['requirement_type']) {
                case 'lessons_completed':
                    $unlocked = $stats['lessons_completed'] >= $achievement['requirement_value'];
                    break;
                case 'courses_completed':
                    $unlocked = $stats['courses_completed'] >= $achievement['requirement_value'];
                    break;
                case 'streak':
                    $unlocked = $user['streak_days'] >= $achievement['requirement_value'];
                    break;
                case 'xp_earned':
                    $unlocked = $user['xp_total'] >= $achievement['requirement_value'];
                    break;
            }
            
            if ($unlocked) {
                $this->unlockAchievement($userId, $achievement['id']);
                $unlockedAchievements[] = $achievement;
            }
        }
        
        return $unlockedAchievements;
    }
    
    public function unlockAchievement(int $userId, int $achievementId): void {
        // Inserir conquista
        $this->db->insert('user_achievements', [
            'user_id' => $userId,
            'achievement_id' => $achievementId
        ]);
        
        // Dar recompensas
        $achievement = $this->db->fetch("SELECT * FROM achievements WHERE id = ?", [$achievementId]);
        
        if ($achievement['xp_reward'] > 0) {
            $this->addXP($userId, $achievement['xp_reward'], 'achievement', "Conquista: {$achievement['name']}");
        }
        
        if ($achievement['coin_reward'] > 0) {
            $this->addCoins($userId, $achievement['coin_reward']);
        }
    }
    
    public function getUserAchievements(int $userId): array {
        return $this->db->fetchAll(
            "SELECT a.*, ua.unlocked_at 
             FROM achievements a 
             LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ?
             ORDER BY ua.unlocked_at DESC, a.id ASC",
            [$userId]
        );
    }
    
    public function getXPHistory(int $userId, int $limit = 20): array {
        return $this->db->fetchAll(
            "SELECT * FROM xp_history WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }
    
    public function getProgressToNextLevel(int $userId): array {
        $user = $this->user->find($userId);
        $currentLevel = $this->user->getLevel($user['xp_total']);
        $nextLevel = $this->user->getNextLevel($currentLevel['level_number']);
        
        if (!$nextLevel) {
            return [
                'current_xp' => $user['xp_total'],
                'level_xp' => $currentLevel['xp_required'],
                'next_level_xp' => $currentLevel['xp_required'],
                'progress' => 100,
                'xp_needed' => 0,
                'is_max_level' => true
            ];
        }
        
        $xpInCurrentLevel = $user['xp_total'] - $currentLevel['xp_required'];
        $xpNeededForNext = $nextLevel['xp_required'] - $currentLevel['xp_required'];
        $progress = ($xpInCurrentLevel / $xpNeededForNext) * 100;
        
        return [
            'current_xp' => $user['xp_total'],
            'level_xp' => $currentLevel['xp_required'],
            'next_level_xp' => $nextLevel['xp_required'],
            'progress' => min(100, $progress),
            'xp_needed' => $nextLevel['xp_required'] - $user['xp_total'],
            'is_max_level' => false
        ];
    }
    
    private function updateWeeklyLeaderboard(int $userId, int $xpAmount): void {
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        
        $existing = $this->db->fetch(
            "SELECT id FROM weekly_leaderboard WHERE user_id = ? AND week_start = ?",
            [$userId, $weekStart]
        );
        
        if ($existing) {
            $this->db->query(
                "UPDATE weekly_leaderboard SET xp_earned = xp_earned + ? WHERE id = ?",
                [$xpAmount, $existing['id']]
            );
        } else {
            $this->db->insert('weekly_leaderboard', [
                'user_id' => $userId,
                'week_start' => $weekStart,
                'xp_earned' => $xpAmount
            ]);
        }
    }
    
    public function getWeeklyLeaderboard(int $limit = 10): array {
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        
        return $this->db->fetchAll(
            "SELECT u.id, u.username, u.full_name, u.avatar, u.level, wl.xp_earned 
             FROM weekly_leaderboard wl 
             JOIN users u ON wl.user_id = u.id 
             WHERE wl.week_start = ? 
             ORDER BY wl.xp_earned DESC 
             LIMIT ?",
            [$weekStart, $limit]
        );
    }
}