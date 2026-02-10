<?php
/**
 * GameDev Academy - Verificação de Requisitos (Wrapper)
 * 
 * Este arquivo serve como ponto de entrada para a verificação de requisitos.
 * Redireciona para o arquivo principal em includes/
 * 
 * @package GameDev Academy
 * @subpackage Installer
 */

// Definir constante de acesso
define('INSTALLER_ACCESS', true);

// Incluir o arquivo principal de verificação
require_once __DIR__ . '/includes/check-requirements.php';