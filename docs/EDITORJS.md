# Implementação EditorJS - Gamedev Academy

## ✨ Recursos Principais

- 🎨 **Toolbar Fixa Estilo Word/Google Docs** - Interface intuitiva sempre visível
- 📝 **14 Tipos de Blocos** - Header, List, Checklist, Quote, Code, Table, Image e mais
- 🖼️ **Upload de Imagens** - Por arquivo e por URL com otimização automática
- 📱 **100% Responsivo** - Funciona perfeitamente em desktop, tablet e mobile
- 🇧🇷 **Interface em Português** - Tradução completa
- 💾 **Auto-save** - Salva automaticamente no textarea do formulário
- ⚡ **Atalhos de Teclado** - Produtividade máxima
- 🔒 **Prevenção de Perda** - Avisa antes de sair sem salvar

## 📁 Estrutura de Arquivos

```
gamedev-academy/
├── includes/
│   └── editorjs-loader.php          # Classe principal de carregamento
├── assets/
│   ├── css/
│   │   └── editorjs-custom.css      # Estilos personalizados
│   └── js/
│       └── editorjs-init.js         # Script de inicialização
└── admin/
    ├── lessons/
    │   ├── lessons-create.php        # Criar lição
    │   └── lessons-edit.php          # Editar lição
    └── news/
        ├── news-create.php           # Criar notícia
        └── news-edit.php             # Editar notícia
```

## 🚀 Instalação

### 1. Copiar Arquivos

Copie os arquivos para as respectivas pastas do projeto:

```bash
# Copiar loader PHP
cp editorjs-loader.php gamedev-academy/includes/

# Copiar CSS
cp editorjs-custom.css gamedev-academy/assets/css/

# Copiar JavaScript
cp editorjs-init.js gamedev-academy/assets/js/
```

### 2. Configurar Database

Certifique-se que suas tabelas tenham uma coluna para armazenar o conteúdo JSON:

```sql
-- Para lessons
ALTER TABLE lessons ADD COLUMN content LONGTEXT;

-- Para news
ALTER TABLE news ADD COLUMN content LONGTEXT;
```

## 📝 Como Usar

### Implementação Básica

Em qualquer arquivo PHP onde você quer usar o EditorJS:

```php
<?php
// 1. Incluir o loader no início do arquivo
require_once '../../includes/editorjs-loader.php';

// 2. No <head> ou início do body, carregar os estilos
EditorJSLoader::renderStyles();
?>

<!-- 3. No HTML, criar os elementos necessários -->
<div id="editorjs"></div>
<textarea class="d-none" id="content" name="content"></textarea>

<?php
// 4. Antes do </body>, carregar os scripts
EditorJSLoader::renderScripts();

// 5. Inicializar o editor
EditorJSLoader::init($existingContent, 'editorjs', 'content');
?>
```

## 🎨 Toolbar Fixa (Estilo Word/Google Docs)

A implementação inclui uma **toolbar fixa sempre visível** no topo do editor, facilitando muito o uso!

### Como a Toolbar Funciona

A toolbar é criada automaticamente ao inicializar o editor e inclui:

#### Seletor de Estilo
- Parágrafo normal
- Títulos 1, 2, 3 e 4

#### Botões de Formatação
- **Listas**: Lista com marcadores, Lista numerada, Lista de tarefas
- **Blocos**: Citação, Código, Divisor
- **Mídia**: Imagem, Tabela, Vídeo
- **Outros**: Aviso, HTML

### Usar a Toolbar

Simplesmente clique no botão desejado e o bloco será inserido automaticamente! É muito mais intuitivo que o método tradicional.

### Três Formas de Adicionar Blocos

1. **Toolbar (Novo!)** - Clique nos botões na barra superior
2. **Tradicional** - Digite "/" no editor
3. **Atalhos** - Use Ctrl/Cmd + atalhos

Para mais detalhes, consulte o arquivo `TOOLBAR-GUIDE.md`.

## 📝 Exemplos de Uso

```php
<?php
$pageTitle = 'Criar Artigo';
include '../includes/header.php';
require_once '../../includes/editorjs-loader.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => $_POST['title'],
        'content' => $_POST['content'], // JSON do EditorJS
    ];
    
    $db->insert('articles', $data);
    redirect('artigos.php');
}

EditorJSLoader::renderStyles();
?>

<form method="POST">
    <input type="text" name="title" required>
    
    <div id="editorjs"></div>
    <textarea class="d-none" id="content" name="content"></textarea>
    
    <button type="submit">Salvar</button>
</form>

<?php
EditorJSLoader::renderScripts();
EditorJSLoader::init('', 'editorjs', 'content');
include '../includes/footer.php';
?>
```

### Exemplo Completo - Editar Conteúdo

```php
<?php
$pageTitle = 'Editar Artigo';
include '../includes/header.php';
require_once '../../includes/editorjs-loader.php';

$id = $_GET['id'];
$article = $db->fetch("SELECT * FROM articles WHERE id = ?", [$id]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => $_POST['title'],
        'content' => $_POST['content'],
    ];
    
    $db->update('articles', $data, 'id = :id', ['id' => $id]);
    redirect('artigos.php');
}

EditorJSLoader::renderStyles();
?>

<form method="POST">
    <input type="text" name="title" value="<?= escape($article['title']) ?>">
    
    <div id="editorjs"></div>
    <textarea class="d-none" id="content" name="content"><?= escape($article['content']) ?></textarea>
    
    <button type="submit">Atualizar</button>
</form>

<?php
EditorJSLoader::renderScripts();
// Passar o conteúdo existente
EditorJSLoader::init($article['content'], 'editorjs', 'content');
include '../includes/footer.php';
?>
```

## 🎨 Customização

### Habilitar/Desabilitar Plugins

```php
// Desabilitar um plugin
EditorJSLoader::disablePlugin('table');

// Habilitar novamente
EditorJSLoader::enablePlugin('table');

// Ver plugins habilitados
$plugins = EditorJSLoader::getEnabledPlugins();
```

### Adicionar Novos Plugins

Edite `includes/editorjs-loader.php`:

```php
private static $versions = [
    'editorjs' => '2.28.2',
    // Adicionar novo plugin aqui
    'seu-plugin' => '1.0.0',
];

private static $enabledPlugins = [
    'header',
    'list',
    // Adicionar aqui
    'seu-plugin',
];
```

E em `assets/js/editorjs-init.js`, adicione a configuração:

```javascript
function getEditorTools() {
    const tools = {};
    
    // Adicionar seu plugin
    if (typeof SeuPlugin !== 'undefined') {
        tools.seuPlugin = {
            class: SeuPlugin,
            config: {
                // configurações
            }
        };
    }
    
    return tools;
}
```

## 🖼️ Upload de Imagens

Para o upload de imagens funcionar, você precisa criar endpoints:

### admin/upload-image.php

```php
<?php
require_once '../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

if (!isset($_FILES['image'])) {
    echo json_encode(['success' => 0, 'message' => 'Nenhuma imagem enviada']);
    exit;
}

$file = $_FILES['image'];
$uploadDir = '../uploads/images/';
$fileName = time() . '_' . basename($file['name']);
$uploadPath = $uploadDir . $fileName;

if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    echo json_encode([
        'success' => 1,
        'file' => [
            'url' => url('uploads/images/' . $fileName)
        ]
    ]);
} else {
    echo json_encode(['success' => 0, 'message' => 'Erro no upload']);
}
```

## 🔄 Conversão de Conteúdo

### EditorJS para HTML

Para exibir o conteúdo no frontend:

```php
<?php
// Método 1: Usar a função da classe
$html = EditorJSLoader::editorJSToHtml($article['content']);
echo $html;

// Método 2: Criar seu próprio renderizador customizado
function renderContent($jsonContent) {
    $data = json_decode($jsonContent, true);
    
    foreach ($data['blocks'] as $block) {
        switch($block['type']) {
            case 'header':
                echo "<h{$block['data']['level']}>{$block['data']['text']}</h{$block['data']['level']}>";
                break;
            case 'paragraph':
                echo "<p>{$block['data']['text']}</p>";
                break;
            // ... outros tipos
        }
    }
}
```

### HTML para EditorJS

Para migrar conteúdo antigo:

```php
<?php
$htmlContent = '<p>Meu conteúdo antigo</p>';
$editorjsContent = EditorJSLoader::htmlToEditorJS($htmlContent);

// Salvar no banco
$db->update('articles', ['content' => $editorjsContent], 'id = :id', ['id' => $id]);
```

## 🎯 Recursos Disponíveis

### Plugins Incluídos

- **Header** - Títulos (H1-H6)
- **List** - Listas ordenadas e não ordenadas
- **Checklist** - Lista de tarefas
- **Quote** - Citações
- **Code** - Blocos de código
- **Delimiter** - Separadores
- **Table** - Tabelas
- **Warning** - Avisos/Alertas
- **Image** - Imagens (requer endpoint de upload)
- **Embed** - Incorporar vídeos (YouTube, Vimeo, etc)
- **Raw HTML** - HTML puro
- **InlineCode** - Código inline
- **Marker** - Destacar texto
- **Underline** - Sublinhado

### Atalhos de Teclado

- `CMD/CTRL + B` - Negrito
- `CMD/CTRL + I` - Itálico
- `CMD/CTRL + K` - Inline Code
- `CMD/CTRL + SHIFT + H` - Header
- `CMD/CTRL + SHIFT + O` - Quote
- `CMD/CTRL + SHIFT + C` - Code Block
- `CMD/CTRL + SHIFT + M` - Marker
- `TAB` - Ver blocos disponíveis

## 🔍 Debug

### Verificar Conteúdo

```javascript
// No console do navegador
console.log(window.editorInstance);

// Obter dados atuais
window.editorInstance.save().then((data) => {
    console.log(data);
});
```

### Logs

O sistema já inclui logs automáticos no console. Para produção, remova ou desabilite:

```javascript
// Em editorjs-init.js, comentar:
// console.log('EditorJS está pronto!');
```

## 🚨 Problemas Comuns

### Editor não aparece

1. Verifique se todos os scripts foram carregados
2. Verifique erros no console do navegador
3. Confirme que os elementos `#editorjs` e `#content` existem

### Conteúdo não salva

1. Verifique se o formulário tem method="POST"
2. Confirme que o textarea `#content` tem name="content"
3. Verifique se o onChange está funcionando no console

### Imagens não fazem upload

1. Crie o endpoint `/admin/upload-image.php`
2. Verifique permissões da pasta de uploads
3. Confirme que o formato de retorno está correto

## 📱 Responsividade

O EditorJS é totalmente responsivo. Os estilos em `editorjs-custom.css` já incluem:

- Breakpoints para mobile
- Touch-friendly na tela sensível ao toque
- Suporte a Dark Mode

## 🔐 Segurança

### Validação de Conteúdo

Sempre valide e sanitize o conteúdo antes de salvar:

```php
<?php
function sanitizeEditorJSContent($content) {
    $data = json_decode($content, true);
    
    if (!isset($data['blocks'])) {
        return '{}';
    }
    
    // Validar estrutura
    foreach ($data['blocks'] as &$block) {
        // Remover scripts maliciosos, etc
    }
    
    return json_encode($data);
}

$cleanContent = sanitizeEditorJSContent($_POST['content']);
$db->insert('articles', ['content' => $cleanContent]);
```

## 📚 Referências

- [EditorJS Official Docs](https://editorjs.io/)
- [GitHub EditorJS](https://github.com/codex-team/editor.js)
- [Lista de Plugins](https://github.com/editor-js)

## 🆘 Suporte

Para problemas ou dúvidas:

1. Verifique o console do navegador
2. Verifique os logs do PHP
3. Consulte a documentação oficial do EditorJS
4. Teste em modo de desenvolvimento primeiro

---

**Versão**: 1.0.0  
**Última atualização**: 2024  
**Compatibilidade**: EditorJS 2.28+, PHP 7.4+