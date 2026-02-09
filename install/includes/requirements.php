<?php
/**
 * Verificação de requisitos do sistema
 */

if (!defined('INSTALLER')) {
    die('Acesso negado');
}

/**
 * Classe para verificação de requisitos
 */
class RequirementsChecker {
    
    private $requirements = [];
    private $warnings = [];
    private $errors = [];
    
    public function __construct() {
        $this->checkPHPVersion();
        $this->checkExtensions();
        $this->checkFunctions();
        $this->checkPermissions();
        $this->checkServerConfig();
    }
    
    /**
     * Verifica versão do PHP
     */
    private function checkPHPVersion() {
        $required = '7.4.0';
        $current = PHP_VERSION;
        $passed = version_compare($current, $required, '>=');
        
        $this->requirements['php_version'] = [
            'name' => 'Versão do PHP',
            'required' => '>= ' . $required,
            'current' => $current,
            'status' => $passed ? 'success' : 'error',
            'passed' => $passed
        ];
        
        if (!$passed) {
            $this->errors[] = "PHP {$required} ou superior é necessário. Versão atual: {$current}";
        }
    }
    
    /**
     * Verifica extensões necessárias
     */
    private function checkExtensions() {
        $extensions = [
            'mysqli' => [
                'name' => 'MySQLi',
                'required' => true,
                'description' => 'Necessário para conexão com banco de dados'
            ],
            'pdo' => [
                'name' => 'PDO',
                'required' => false,
                'description' => 'Recomendado para melhor performance'
            ],
            'json' => [
                'name' => 'JSON',
                'required' => true,
                'description' => 'Necessário para manipulação de dados'
            ],
            'mbstring' => [
                'name' => 'Multibyte String',
                'required' => true,
                'description' => 'Necessário para manipulação de strings UTF-8'
            ],
            'gd' => [
                'name' => 'GD Library',
                'required' => false,
                'description' => 'Necessário para manipulação de imagens'
            ],
            'curl' => [
                'name' => 'cURL',
                'required' => false,
                'description' => 'Necessário para requisições externas'
            ],
            'zip' => [
                'name' => 'ZIP',
                'required' => false,
                'description' => 'Necessário para compactação de arquivos'
            ],
            'openssl' => [
                'name' => 'OpenSSL',
                'required' => true,
                'description' => 'Necessário para segurança e criptografia'
            ]
        ];
        
        foreach ($extensions as $ext => $info) {
            $loaded = extension_loaded($ext);
            $status = 'error';
            
            if ($loaded) {
                $status = 'success';
            } elseif (!$info['required']) {
                $status = 'warning';
            }
            
            $this->requirements['ext_' . $ext] = [
                'name' => 'Extensão ' . $info['name'],
                'required' => $info['required'] ? 'Obrigatório' : 'Recomendado',
                'current' => $loaded ? 'Instalado' : 'Não instalado',
                'status' => $status,
                'passed' => $loaded || !$info['required'],
                'description' => $info['description']
            ];
            
            if (!$loaded && $info['required']) {
                $this->errors[] = "Extensão {$info['name']} é obrigatória mas não está instalada";
            } elseif (!$loaded && !$info['required']) {
                $this->warnings[] = "Extensão {$info['name']} não está instalada. {$info['description']}";
            }
        }
    }
    
    /**
     * Verifica funções necessárias
     */
    private function checkFunctions() {
        $functions = [
            'file_get_contents' => 'Leitura de arquivos',
            'file_put_contents' => 'Escrita de arquivos',
            'session_start' => 'Gerenciamento de sessões',
            'password_hash' => 'Criptografia de senhas',
            'random_bytes' => 'Geração de tokens seguros'
        ];
        
        foreach ($functions as $func => $description) {
            $exists = function_exists($func);
            
            $this->requirements['func_' . $func] = [
                'name' => 'Função ' . $func,
                'required' => 'Habilitado',
                'current' => $exists ? 'Disponível' : 'Desabilitado',
                'status' => $exists ? 'success' : 'error',
                'passed' => $exists,
                'description' => $description
            ];
            
            if (!$exists) {
                $this->errors[] = "Função {$func} não está disponível. Necessária para: {$description}";
            }
        }
    }
    
    /**
     * Verifica permissões de pastas
     */
    private function checkPermissions() {
        $paths = [
            [
                'path' => ROOT_PATH,
                'name' => 'Pasta Raiz',
                'required' => true,
                'write' => true
            ],
            [
                'path' => ROOT_PATH . '/uploads',
                'name' => 'Pasta Uploads',
                'required' => false,
                'write' => true,
                'create' => true
            ],
            [
                'path' => ROOT_PATH . '/cache',
                'name' => 'Pasta Cache',
                'required' => false,
                'write' => true,
                'create' => true
            ],
            [
                'path' => ROOT_PATH . '/logs',
                'name' => 'Pasta Logs',
                'required' => false,
                'write' => true,
                'create' => true
            ]
        ];
        
        foreach ($paths as $item) {
            $exists = file_exists($item['path']);
            $writable = $exists && is_writable($item['path']);
            
            // Tentar criar pasta se não existe e está marcado para criar
            if (!$exists && isset($item['create']) && $item['create']) {
                @mkdir($item['path'], 0755, true);
                $exists = file_exists($item['path']);
                $writable = $exists && is_writable($item['path']);
            }
            
            $status = 'error';
            if ($writable) {
                $status = 'success';
            } elseif (!$item['required']) {
                $status = 'warning';
            }
            
            $key = 'perm_' . basename($item['path']);
            $this->requirements[$key] = [
                'name' => $item['name'],
                'required' => $item['write'] ? 'Gravável' : 'Legível',
                'current' => !$exists ? 'Não existe' : ($writable ? 'Gravável' : 'Somente leitura'),
                'status' => $status,
                'passed' => $writable || !$item['required']
            ];
            
            if (!$writable && $item['required']) {
                $this->errors[] = "{$item['name']} precisa ter permissão de escrita";
            } elseif (!$writable && !$item['required']) {
                $this->warnings[] = "{$item['name']} sem permissão de escrita. Alguns recursos podem não funcionar";
            }
        }
    }
    
    /**
     * Verifica configurações do servidor
     */
    private function checkServerConfig() {
        // Verificar limite de memória
        $memory_limit = ini_get('memory_limit');
        $memory_bytes = $this->convertToBytes($memory_limit);
        $required_memory = 128 * 1024 * 1024; // 128MB
        
        $this->requirements['memory_limit'] = [
            'name' => 'Limite de Memória',
            'required' => '>= 128M',
            'current' => $memory_limit,
            'status' => $memory_bytes >= $required_memory ? 'success' : 'warning',
            'passed' => true // Não é crítico
        ];
        
        if ($memory_bytes < $required_memory) {
            $this->warnings[] = "Limite de memória baixo ({$memory_limit}). Recomendado: 128M ou mais";
        }
        
        // Verificar tempo de execução
        $max_execution = ini_get('max_execution_time');
        $this->requirements['max_execution'] = [
            'name' => 'Tempo Máximo de Execução',
            'required' => '>= 30s',
            'current' => $max_execution . 's',
            'status' => $max_execution >= 30 || $max_execution == 0 ? 'success' : 'warning',
            'passed' => true
        ];
        
        // Verificar tamanho de upload
        $upload_max = ini_get('upload_max_filesize');
        $this->requirements['upload_max'] = [
            'name' => 'Tamanho Máximo de Upload',
            'required' => '>= 2M',
            'current' => $upload_max,
            'status' => $this->convertToBytes($upload_max) >= 2097152 ? 'success' : 'warning',
            'passed' => true
        ];
        
        // Verificar mod_rewrite (Apache)
        $mod_rewrite = false;
        if (function_exists('apache_get_modules')) {
            $mod_rewrite = in_array('mod_rewrite', apache_get_modules());
        }
        
        $this->requirements['mod_rewrite'] = [
            'name' => 'URL Amigável (mod_rewrite)',
            'required' => 'Recomendado',
            'current' => $mod_rewrite ? 'Habilitado' : 'Não detectado',
            'status' => $mod_rewrite ? 'success' : 'warning',
            'passed' => true
        ];
    }
    
    /**
     * Converte valores de php.ini para bytes
     */
    private function convertToBytes($value) {
        $value = trim($value);
        $last = strtolower($value[strlen($value)-1]);
        $value = (int)$value;
        
        switch($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
    
    /**
     * Retorna todos os requisitos
     */
    public function getRequirements() {
        return $this->requirements;
    }
    
    /**
     * Retorna erros encontrados
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Retorna avisos
     */
    public function getWarnings() {
        return $this->warnings;
    }
    
    /**
     * Verifica se pode continuar
     */
    public function canContinue() {
        return count($this->errors) === 0;
    }
    
    /**
     * Retorna resumo
     */
    public function getSummary() {
        $total = count($this->requirements);
        $passed = count(array_filter($this->requirements, function($r) { 
            return $r['status'] === 'success'; 
        }));
        $warnings = count(array_filter($this->requirements, function($r) { 
            return $r['status'] === 'warning'; 
        }));
        $errors = count(array_filter($this->requirements, function($r) { 
            return $r['status'] === 'error'; 
        }));
        
        return [
            'total' => $total,
            'passed' => $passed,
            'warnings' => $warnings,
            'errors' => $errors,
            'can_continue' => $this->canContinue()
        ];
    }
}