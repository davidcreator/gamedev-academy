<?php
/**
 * GameDev Academy - Verificação de Requisitos do Sistema
 * 
 * Este script verifica se o servidor atende aos requisitos mínimos
 * para instalação e execução do sistema.
 * 
 * @package GameDev Academy
 * @subpackage Installer
 */

// Prevenir acesso direto indevido
if (!defined('INSTALLER_ACCESS') && basename($_SERVER['PHP_SELF']) === 'check-requirements.php') {
    // Permitir acesso via AJAX
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
}

/**
 * Classe para verificação de requisitos do sistema
 */
class RequirementsChecker {
    
    /**
     * Versão mínima do PHP requerida
     */
    const PHP_MIN_VERSION = '7.4.0';
    
    /**
     * Versão recomendada do PHP
     */
    const PHP_RECOMMENDED_VERSION = '8.0.0';
    
    /**
     * Extensões obrigatórias
     */
    private $requiredExtensions = [
        'pdo',
        'pdo_mysql',
        'json',
        'mbstring',
        'openssl',
        'session'
    ];
    
    /**
     * Extensões recomendadas (não obrigatórias)
     */
    private $recommendedExtensions = [
        'curl',
        'gd',
        'zip',
        'fileinfo',
        'xml',
        'intl'
    ];
    
    /**
     * Diretórios que precisam de permissão de escrita
     */
    private $writableDirectories = [
        'config',
        'uploads',
        'uploads/avatars',
        'uploads/courses',
        'uploads/news',
        'cache',
        'logs'
    ];
    
    /**
     * Caminho base do projeto
     */
    private $basePath;
    
    /**
     * Resultados da verificação
     */
    private $results = [];
    
    /**
     * Construtor
     */
    public function __construct() {
        // Definir caminho base (dois níveis acima de install/includes/)
        $this->basePath = dirname(dirname(__DIR__));
    }
    
    /**
     * Executar todas as verificações
     * 
     * @return array Resultados das verificações
     */
    public function checkAll() {
        $this->results = [
            'php_version' => $this->checkPhpVersion(),
            'extensions' => $this->checkExtensions(),
            'recommended_extensions' => $this->checkRecommendedExtensions(),
            'permissions' => $this->checkPermissions(),
            'server_info' => $this->getServerInfo(),
            'all_passed' => true,
            'warnings' => [],
            'errors' => []
        ];
        
        // Verificar se todos os requisitos obrigatórios passaram
        $this->evaluateResults();
        
        return $this->results;
    }
    
    /**
     * Verificar versão do PHP
     * 
     * @return array
     */
    public function checkPhpVersion() {
        $currentVersion = PHP_VERSION;
        $passed = version_compare($currentVersion, self::PHP_MIN_VERSION, '>=');
        $recommended = version_compare($currentVersion, self::PHP_RECOMMENDED_VERSION, '>=');
        
        return [
            'name' => 'PHP Version',
            'required' => self::PHP_MIN_VERSION,
            'recommended' => self::PHP_RECOMMENDED_VERSION,
            'current' => $currentVersion,
            'passed' => $passed,
            'is_recommended' => $recommended,
            'message' => $passed 
                ? ($recommended ? 'Versão recomendada' : 'Versão mínima atendida')
                : 'Versão do PHP muito antiga. Atualize para ' . self::PHP_MIN_VERSION . ' ou superior.'
        ];
    }
    
    /**
     * Verificar extensões obrigatórias do PHP
     * 
     * @return array
     */
    public function checkExtensions() {
        $results = [];
        
        foreach ($this->requiredExtensions as $extension) {
            $loaded = extension_loaded($extension);
            
            $results[$extension] = [
                'name' => strtoupper($extension),
                'loaded' => $loaded,
                'required' => true,
                'message' => $loaded 
                    ? 'Extensão instalada' 
                    : 'Extensão não encontrada. Instale a extensão php-' . $extension
            ];
        }
        
        return $results;
    }
    
    /**
     * Verificar extensões recomendadas
     * 
     * @return array
     */
    public function checkRecommendedExtensions() {
        $results = [];
        
        foreach ($this->recommendedExtensions as $extension) {
            $loaded = extension_loaded($extension);
            
            $results[$extension] = [
                'name' => strtoupper($extension),
                'loaded' => $loaded,
                'required' => false,
                'message' => $loaded 
                    ? 'Extensão instalada' 
                    : 'Extensão recomendada não encontrada'
            ];
        }
        
        return $results;
    }
    
    /**
     * Verificar permissões de diretórios
     * 
     * @return array
     */
    public function checkPermissions() {
        $results = [];
        
        foreach ($this->writableDirectories as $directory) {
            $fullPath = $this->basePath . DIRECTORY_SEPARATOR . $directory;
            
            // Verificar se o diretório existe
            $exists = is_dir($fullPath);
            
            // Se não existe, verificar se podemos criar
            if (!$exists) {
                $parentDir = dirname($fullPath);
                $canCreate = is_writable($parentDir) || is_writable($this->basePath);
                $writable = $canCreate;
                $status = $canCreate ? 'can_create' : 'not_exists';
            } else {
                $writable = is_writable($fullPath);
                $status = $writable ? 'writable' : 'not_writable';
            }
            
            $results[$directory] = [
                'path' => $directory,
                'full_path' => $fullPath,
                'exists' => $exists,
                'writable' => $writable,
                'status' => $status,
                'message' => $this->getPermissionMessage($status, $directory)
            ];
        }
        
        // Verificar permissão da raiz para criar config.php
        $rootWritable = is_writable($this->basePath);
        $results['root'] = [
            'path' => '/',
            'full_path' => $this->basePath,
            'exists' => true,
            'writable' => $rootWritable,
            'status' => $rootWritable ? 'writable' : 'not_writable',
            'message' => $rootWritable 
                ? 'Diretório raiz com permissão de escrita'
                : 'Sem permissão para criar arquivos na raiz do projeto'
        ];
        
        return $results;
    }
    
    /**
     * Obter mensagem de permissão
     * 
     * @param string $status Status da permissão
     * @param string $directory Nome do diretório
     * @return string
     */
    private function getPermissionMessage($status, $directory) {
        switch ($status) {
            case 'writable':
                return 'Diretório com permissão de escrita';
            case 'can_create':
                return 'Diretório será criado durante a instalação';
            case 'not_writable':
                return "Sem permissão de escrita. Execute: chmod 755 {$directory}";
            case 'not_exists':
                return 'Diretório não existe e não pode ser criado';
            default:
                return 'Status desconhecido';
        }
    }
    
    /**
     * Obter informações do servidor
     * 
     * @return array
     */
    public function getServerInfo() {
        return [
            'php_version' => PHP_VERSION,
            'php_sapi' => PHP_SAPI,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido',
            'os' => PHP_OS,
            'max_upload_size' => $this->formatBytes($this->getMaxUploadSize()),
            'max_post_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time') . ' segundos',
            'display_errors' => ini_get('display_errors') ? 'Ativado' : 'Desativado',
            'date_timezone' => date_default_timezone_get(),
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
            'base_path' => $this->basePath
        ];
    }
    
    /**
     * Obter tamanho máximo de upload
     * 
     * @return int
     */
    private function getMaxUploadSize() {
        $uploadMax = $this->parseSize(ini_get('upload_max_filesize'));
        $postMax = $this->parseSize(ini_get('post_max_size'));
        
        return min($uploadMax, $postMax);
    }
    
    /**
     * Converter tamanho em formato legível para bytes
     * 
     * @param string $size
     * @return int
     */
    private function parseSize($size) {
        $unit = strtoupper(substr($size, -1));
        $value = (int) $size;
        
        switch ($unit) {
            case 'G':
                $value *= 1024 * 1024 * 1024;
                break;
            case 'M':
                $value *= 1024 * 1024;
                break;
            case 'K':
                $value *= 1024;
                break;
        }
        
        return $value;
    }
    
    /**
     * Formatar bytes para formato legível
     * 
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * Avaliar resultados e determinar status geral
     */
    private function evaluateResults() {
        $allPassed = true;
        $warnings = [];
        $errors = [];
        
        // Verificar versão do PHP
        if (!$this->results['php_version']['passed']) {
            $allPassed = false;
            $errors[] = $this->results['php_version']['message'];
        } elseif (!$this->results['php_version']['is_recommended']) {
            $warnings[] = 'Considere atualizar para PHP ' . self::PHP_RECOMMENDED_VERSION . ' para melhor desempenho.';
        }
        
        // Verificar extensões obrigatórias
        foreach ($this->results['extensions'] as $ext => $info) {
            if (!$info['loaded']) {
                $allPassed = false;
                $errors[] = "Extensão obrigatória não encontrada: {$ext}";
            }
        }
        
        // Verificar extensões recomendadas (apenas avisos)
        foreach ($this->results['recommended_extensions'] as $ext => $info) {
            if (!$info['loaded']) {
                $warnings[] = "Extensão recomendada não encontrada: {$ext}";
            }
        }
        
        // Verificar permissões críticas
        $criticalDirs = ['config', 'root'];
        foreach ($criticalDirs as $dir) {
            if (isset($this->results['permissions'][$dir])) {
                if (!$this->results['permissions'][$dir]['writable']) {
                    // Não é erro fatal, mas é warning importante
                    $warnings[] = "Permissão de escrita necessária para: {$dir}";
                }
            }
        }
        
        $this->results['all_passed'] = $allPassed;
        $this->results['warnings'] = $warnings;
        $this->results['errors'] = $errors;
        $this->results['can_continue'] = $allPassed;
    }
    
    /**
     * Obter resumo simples para JavaScript
     * 
     * @return array
     */
    public function getSimpleSummary() {
        $full = $this->checkAll();
        
        // Formato simplificado para compatibilidade com JS existente
        $extensions = [];
        foreach ($full['extensions'] as $ext => $info) {
            $extensions[$ext] = $info['loaded'];
        }
        
        $permissions = [];
        foreach ($full['permissions'] as $dir => $info) {
            $permissions[$dir] = $info['writable'];
        }
        
        return [
            'php_version' => [
                'required' => $full['php_version']['required'],
                'current' => $full['php_version']['current'],
                'passed' => $full['php_version']['passed']
            ],
            'extensions' => $extensions,
            'permissions' => $permissions,
            'all_passed' => $full['all_passed'],
            'can_continue' => $full['can_continue'],
            'errors' => $full['errors'],
            'warnings' => $full['warnings']
        ];
    }
}

/**
 * Execução direta (chamada via AJAX)
 */
if (basename($_SERVER['PHP_SELF']) === 'check-requirements.php' || 
    (isset($_GET['action']) && $_GET['action'] === 'check')) {
    
    try {
        $checker = new RequirementsChecker();
        
        // Retornar formato completo ou simplificado
        $format = $_GET['format'] ?? 'simple';
        
        if ($format === 'full') {
            $results = $checker->checkAll();
        } else {
            $results = $checker->getSimpleSummary();
        }
        
        // Retornar JSON
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode([
            'error' => true,
            'message' => $e->getMessage(),
            'all_passed' => false,
            'can_continue' => false
        ]);
    }
    
    exit;
}