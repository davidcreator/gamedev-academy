<?php
// classes/Setting.php

require_once __DIR__ . '/Database.php';

class Setting
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function get(string $key, $default = null)
    {
        try {
            $setting = $this->db->fetch(
                "SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1",
                [$key]
            );
        } catch (Throwable $e) {
            return $default;
        }

        if (!$setting) {
            return $default;
        }

        return $setting['setting_value'];
    }

    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default ? '1' : '0');

        if (is_bool($value)) {
            return $value;
        }

        return in_array((string) $value, ['1', 'true', 'yes', 'on'], true);
    }

    public function getFloat(string $key, float $default = 0.0): float
    {
        $value = $this->get($key, $default);

        if ($value === null || $value === '') {
            return $default;
        }

        return (float) str_replace(',', '.', (string) $value);
    }

    public function getInt(string $key, int $default = 0): int
    {
        return (int) round($this->getFloat($key, (float) $default));
    }

    public function getJson(string $key, array $default = []): array
    {
        $value = $this->get($key);
        if ($value === null || $value === '') {
            return $default;
        }

        $decoded = json_decode((string) $value, true);
        return is_array($decoded) ? $decoded : $default;
    }
}
