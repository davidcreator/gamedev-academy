# Módulo de Lições (Lessons) - Gamedev Academy

## 📚 Sistema Completo de Gerenciamento de Lições

Sistema profissional de lições com EditorJS, vídeos, materiais complementares, drag & drop para reordenamento e muito mais!

## 📁 Arquivos do Módulo

```
gamedev-academy/admin/lessons/
├── list.php           → Listagem e gerenciamento
├── create.php         → Criar nova lição
├── edit.php           → Editar lição existente
├── delete.php         → Excluir lição (com confirmação)
└── reorder.php        → Reordenar lições (AJAX)
```

## 🗄️ Estrutura do Banco de Dados

```sql
CREATE TABLE `lessons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `module_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `summary` TEXT,
  `content` LONGTEXT,
  `content_type` ENUM('text', 'video', 'quiz', 'exercise', 'project', 'live', 'download') DEFAULT 'text',
  `video_url` VARCHAR(500),
  `video_provider` ENUM('youtube', 'vimeo', 'cloudflare', 'bunny', 'self') DEFAULT 'youtube',
  `duration_minutes` INT DEFAULT 0,
  `order_position` INT DEFAULT 0,
  `is_published` TINYINT(1) DEFAULT 0,
  `is_free_preview` TINYINT(1) DEFAULT 0,
  `attachment_url` VARCHAR(500),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_module (module_id),
  INDEX idx_order (order_position),
  INDEX idx_published (is_published),
  FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de progresso (opcional, mas recomendado)
CREATE TABLE `lesson_progress` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `lesson_id` INT NOT NULL,
  `is_completed` TINYINT(1) DEFAULT 0,
  `progress_percentage` DECIMAL(5,2) DEFAULT 0,
  `last_accessed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_at` TIMESTAMP NULL,
  UNIQUE KEY unique_user_lesson (user_id, lesson_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## ✨ Funcionalidades

### 📋 Listagem (lessons-list.php)

**Dashboard com Estatísticas:**
- Total de lições
- Publicadas
- Rascunhos
- Pré-visualizações gratuitas

**Filtros Avançados:**
- Busca por título/resumo
- Filtro por status (publicado/rascunho)
- Filtro por tipo de conteúdo
- Filtro por módulo/curso

**Funcionalidades Especiais:**
- **Drag & Drop** para reordenar lições
- Visualização hierárquica (curso > módulo > lição)
- Ações rápidas (ver, editar, excluir)
- Indicadores visuais (tipo, duração, status)
- Breadcrumb navegável

### ✍️ Criar Lição (lessons-create.php)

**Campos Principais:**
- Título (obrigatório)
- Resumo/Summary
- Tipo de conteúdo (7 opções)
- Conteúdo (EditorJS com toolbar)

**Vídeo:**
- URL do vídeo
- Provedor (YouTube, Vimeo, Cloudflare, Bunny, Self-hosted)
- Duração estimada

**Recursos:**
- Materiais complementares (GitHub, arquivos)
- Prévia gratuita
- Publicação
- Ordem automática

**Validações:**
- Título obrigatório
- URL de vídeo válida (se fornecida)
- Ordem automática (última posição)

### ✏️ Editar Lição (lessons-edit.php)

Todos os recursos do create.php, mais:

**Informações Adicionais:**
- ID e posição da lição
- Módulo e curso associados
- Datas de criação/atualização
- Link para ver lição (se publicada)

**Estatísticas:**
- Alunos matriculados
- Taxa de conclusão
- Progresso médio
- Último acesso

### 🗑️ Excluir Lição (lessons-delete.php)

**Página de Confirmação:**
- Preview completo da lição
- Informações do curso/módulo
- Lista do que será excluído
- Impacto nos alunos
- Sugestões de alternativas

**Recursos:**
- Reordenação automática das lições restantes
- Exclusão do progresso dos alunos
- Alertas para lições publicadas

### 🔄 Reordenar (lessons-reorder.php)

**Endpoint AJAX:**
- Recebe array de IDs
- Atualiza ordem no banco
- Retorna sucesso/erro
- Sem recarregar página

## 🎯 Fluxo de Trabalho

### Criar Curso Completo

1. **Criar Curso** → `admin/courses/create.php`
2. **Criar Módulos** → `admin/modules/create.php`
3. **Criar Lições** → `admin/lessons/create.php`
4. **Organizar Ordem** → Arrastar e soltar na listagem
5. **Publicar** → Editar e marcar como publicado

### Adicionar Lição com Vídeo

1. Acesse `admin/lessons/list.php?module_id=X`
2. Clique em "Nova Lição"
3. Preencha título e resumo
4. Selecione tipo "Vídeo"
5. Cole URL do YouTube/Vimeo
6. Defina duração estimada
7. Adicione conteúdo escrito (opcional)
8. Marque como publicada
9. Salve

### Oferecer Prévia Gratuita

1. Edite a lição
2. Marque "Prévia Gratuita"
3. Mantenha como "Publicada"
4. Salve

> Lições marcadas como prévia gratuita ficam acessíveis sem login ou assinatura!

### Reordenar Lições

1. Acesse a listagem de lições do módulo
2. Arraste o ícone ⋮⋮ da lição
3. Solte na nova posição
4. A ordem é salva automaticamente

## 🎨 Tipos de Conteúdo

### 1. Texto
- Ideal para: Artigos, documentação, conceitos
- Usa: EditorJS completo
- Exemplo: "Introdução ao C#"

### 2. Vídeo
- Ideal para: Tutoriais práticos, demonstrações
- Suporta: YouTube, Vimeo, etc
- Exemplo: "Como criar um personagem no Unity"

### 3. Quiz
- Ideal para: Avaliações, testes
- Requer: Sistema de quiz (implementar separadamente)
- Exemplo: "Teste seus conhecimentos em C#"

### 4. Exercício
- Ideal para: Prática guiada, desafios
- Usa: Texto + código
- Exemplo: "Crie um sistema de pontuação"

### 5. Projeto
- Ideal para: Trabalhos práticos, portfólio
- Usa: Descrição + requisitos
- Exemplo: "Desenvolva um jogo completo"

### 6. Live
- Ideal para: Aulas ao vivo, webinars
- Usa: URL de streaming
- Exemplo: "Live: Perguntas e Respostas"

### 7. Download
- Ideal para: Assets, templates, arquivos
- Usa: Link para download
- Exemplo: "Pack de sprites gratuito"

## 🔍 Hierarquia do Sistema

```
Curso (ex: Unity Completo)
  ├── Módulo 1 (ex: Fundamentos)
  │   ├── Lição 1 (ex: Instalação)
  │   ├── Lição 2 (ex: Interface)
  │   └── Lição 3 (ex: Primeiro projeto)
  ├── Módulo 2 (ex: Programação C#)
  │   ├── Lição 1 (ex: Variáveis)
  │   └── Lição 2 (ex: Funções)
  └── Módulo 3 (ex: Game Objects)
      └── ...
```

## 🎥 Provedores de Vídeo

### YouTube
```
URL: https://www.youtube.com/watch?v=VIDEO_ID
ou: https://youtu.be/VIDEO_ID
```

### Vimeo
```
URL: https://vimeo.com/VIDEO_ID
```

### Cloudflare Stream
```
URL: https://customer-XXX.cloudflarestream.com/VIDEO_ID/manifest/video.m3u8
```

### Bunny.net
```
URL: https://video.bunnycdn.com/play/library/VIDEO_ID
```

### Self-hosted
```
URL: https://seusite.com/videos/video.mp4
```

## 📊 Sistema de Progresso

### Rastrear Progresso do Aluno

```php
// Quando aluno acessa lição
$db->insert('lesson_progress', [
    'user_id' => $userId,
    'lesson_id' => $lessonId,
    'progress_percentage' => 0,
    'last_accessed_at' => date('Y-m-d H:i:s')
]);

// Quando aluno completa lição
$db->update('lesson_progress', [
    'is_completed' => 1,
    'progress_percentage' => 100,
    'completed_at' => date('Y-m-d H:i:s')
], 'user_id = :user AND lesson_id = :lesson', [
    'user' => $userId,
    'lesson' => $lessonId
]);
```

### Verificar se Lição está Completa

```php
$progress = $db->fetch("
    SELECT is_completed 
    FROM lesson_progress 
    WHERE user_id = ? AND lesson_id = ?
", [$userId, $lessonId]);

$isCompleted = $progress && $progress['is_completed'];
```

### Calcular Progresso do Módulo

```php
$moduleProgress = $db->fetch("
    SELECT 
        COUNT(*) as total_lessons,
        SUM(CASE WHEN lp.is_completed = 1 THEN 1 ELSE 0 END) as completed_lessons
    FROM lessons l
    LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = ?
    WHERE l.module_id = ?
", [$userId, $moduleId]);

$percentage = ($moduleProgress['completed_lessons'] / $moduleProgress['total_lessons']) * 100;
```

## 🎬 Embedar Vídeos no Frontend

### YouTube

```php
<?php if ($lesson['video_provider'] === 'youtube'): ?>
    <?php
    // Extrair ID do vídeo
    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&]+)/', $lesson['video_url'], $matches);
    $videoId = $matches[1] ?? '';
    ?>
    <iframe 
        width="100%" 
        height="500" 
        src="https://www.youtube.com/embed/<?= $videoId ?>" 
        frameborder="0" 
        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" 
        allowfullscreen>
    </iframe>
<?php endif; ?>
```

### Vimeo

```php
<?php if ($lesson['video_provider'] === 'vimeo'): ?>
    <?php
    preg_match('/vimeo\.com\/(\d+)/', $lesson['video_url'], $matches);
    $videoId = $matches[1] ?? '';
    ?>
    <iframe 
        src="https://player.vimeo.com/video/<?= $videoId ?>" 
        width="100%" 
        height="500" 
        frameborder="0" 
        allow="autoplay; fullscreen" 
        allowfullscreen>
    </iframe>
<?php endif; ?>
```

## 🔒 Controle de Acesso

### Verificar se Lição é Gratuita

```php
function canAccessLesson($userId, $lessonId) {
    $db = Database::getInstance();
    $lesson = $db->fetch("SELECT * FROM lessons WHERE id = ?", [$lessonId]);
    
    // Prévia gratuita - todos podem acessar
    if ($lesson['is_free_preview']) {
        return true;
    }
    
    // Verificar se usuário está matriculado no curso
    $courseId = $db->fetch("
        SELECT c.id 
        FROM courses c
        JOIN modules m ON c.id = m.course_id
        JOIN lessons l ON m.id = l.module_id
        WHERE l.id = ?
    ", [$lessonId])['id'];
    
    $enrollment = $db->fetch("
        SELECT * FROM enrollments 
        WHERE user_id = ? AND course_id = ? AND status = 'active'
    ", [$userId, $courseId]);
    
    return $enrollment !== null;
}
```

## 📱 Exemplo de Página de Lição (Frontend)

```php
<?php
require_once 'includes/init.php';

$lessonId = intval($_GET['id'] ?? 0);
$db = Database::getInstance();

// Buscar lição
$lesson = $db->fetch("
    SELECT l.*, 
           m.title as module_title,
           c.title as course_title,
           c.id as course_id
    FROM lessons l
    JOIN modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE l.id = ? AND l.is_published = 1
", [$lessonId]);

if (!$lesson) {
    header("HTTP/1.0 404 Not Found");
    include '404.php';
    exit;
}

// Verificar acesso
if (!canAccessLesson($_SESSION['user_id'] ?? 0, $lessonId)) {
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

// Renderizar conteúdo
$content = EditorJSLoader::editorJSToHtml($lesson['content']);
?>

<div class="lesson-page">
    <!-- Breadcrumb -->
    <nav>
        <a href="cursos.php">Cursos</a> /
        <a href="curso.php?id=<?= $lesson['course_id'] ?>"><?= escape($lesson['course_title']) ?></a> /
        <span><?= escape($lesson['title']) ?></span>
    </nav>

    <!-- Vídeo (se houver) -->
    <?php if (!empty($lesson['video_url'])): ?>
        <div class="video-container">
            <!-- Embedar vídeo aqui -->
        </div>
    <?php endif; ?>

    <!-- Conteúdo -->
    <div class="lesson-content">
        <h1><?= escape($lesson['title']) ?></h1>
        
        <?php if ($lesson['summary']): ?>
            <p class="lead"><?= escape($lesson['summary']) ?></p>
        <?php endif; ?>

        <div class="content">
            <?= $content ?>
        </div>

        <!-- Materiais -->
        <?php if ($lesson['attachment_url']): ?>
            <div class="attachments">
                <h3>Materiais Complementares</h3>
                <a href="<?= escape($lesson['attachment_url']) ?>" target="_blank">
                    <i class="fas fa-download"></i> Download
                </a>
            </div>
        <?php endif; ?>

        <!-- Navegação -->
        <div class="lesson-navigation">
            <a href="#" class="btn-previous">← Lição Anterior</a>
            <button onclick="markAsComplete()" class="btn-complete">
                Marcar como Concluída
            </button>
            <a href="#" class="btn-next">Próxima Lição →</a>
        </div>
    </div>
</div>

<script>
function markAsComplete() {
    fetch('/api/lessons/<?= $lessonId ?>/complete', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Lição marcada como concluída!');
            // Redirecionar para próxima lição
        }
    });
}
</script>
```

## 🚀 Recursos Avançados

### Bloquear Lições por Ordem

```php
function getNextAvailableLesson($userId, $moduleId) {
    $db = Database::getInstance();
    
    // Buscar todas as lições do módulo
    $lessons = $db->fetchAll("
        SELECT * FROM lessons 
        WHERE module_id = ? 
        ORDER BY order_position ASC
    ", [$moduleId]);
    
    foreach ($lessons as $lesson) {
        $progress = $db->fetch("
            SELECT is_completed 
            FROM lesson_progress 
            WHERE user_id = ? AND lesson_id = ?
        ", [$userId, $lesson['id']]);
        
        // Primeira lição não concluída
        if (!$progress || !$progress['is_completed']) {
            return $lesson;
        }
    }
    
    return null; // Todas concluídas
}
```

### Certificado de Conclusão

```php
function canIssueCertificate($userId, $courseId) {
    $db = Database::getInstance();
    
    $stats = $db->fetch("
        SELECT 
            COUNT(*) as total_lessons,
            SUM(CASE WHEN lp.is_completed = 1 THEN 1 ELSE 0 END) as completed
        FROM lessons l
        JOIN modules m ON l.module_id = m.id
        LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = ?
        WHERE m.course_id = ?
    ", [$userId, $courseId]);
    
    return $stats['total_lessons'] == $stats['completed'];
}
```

## 🎯 Boas Práticas

### Estrutura de Conteúdo

1. **Introdução** (1-2 parágrafos)
2. **Objetivos** (lista)
3. **Conteúdo Principal** (seções organizadas)
4. **Exemplos Práticos** (código, imagens)
5. **Resumo** (pontos principais)
6. **Próximos Passos** (o que vem a seguir)

### Vídeos

- Máximo 15-20 minutos por lição
- Qualidade mínima: 720p
- Áudio claro e sem ruídos
- Legendas quando possível

### Materiais Complementares

- Código fonte no GitHub
- Slides em PDF
- Assets e recursos
- Links relevantes

## 📊 Métricas Importantes

- Taxa de conclusão por lição
- Tempo médio de conclusão
- Lições mais acessadas
- Pontos de desistência
- Feedback dos alunos

---

**Desenvolvido para Gamedev Academy**  
Sistema completo de gerenciamento de lições com EditorJS

**Versão:** 1.0.0  
**Data:** 2024  
**Licença:** Proprietária