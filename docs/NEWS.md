# Módulo de Notícias (News) - Gamedev Academy

## 📰 Sistema Completo de Gerenciamento de Notícias

Sistema profissional de notícias com EditorJS, SEO, agendamento e muito mais!

## 📁 Arquivos do Módulo

```
gamedev-academy/admin/news/
├── list.php           → Listagem e gerenciamento
├── create.php         → Criar nova notícia
├── edit.php           → Editar notícia existente
└── delete.php         → Excluir notícia (com confirmação)
```

## 🗄️ Estrutura do Banco de Dados

```sql
CREATE TABLE `news` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) UNIQUE NOT NULL,
  `excerpt` TEXT,
  `content` LONGTEXT,
  `featured_image` VARCHAR(500),
  `category` VARCHAR(50),
  `author_id` INT,
  `status` ENUM('draft', 'published', 'scheduled', 'archived') DEFAULT 'draft',
  `is_featured` TINYINT(1) DEFAULT 0,
  `meta_title` VARCHAR(60),
  `meta_description` VARCHAR(160),
  `published_at` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_slug (slug),
  INDEX idx_status (status),
  INDEX idx_category (category),
  INDEX idx_published_at (published_at),
  FOREIGN KEY (author_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## ✨ Funcionalidades

### 📋 Listagem (news-list.php)

**Dashboard com Estatísticas:**
- Total de notícias
- Publicadas
- Rascunhos
- Agendadas

**Filtros Avançados:**
- Busca por título/conteúdo
- Filtro por status
- Filtro por categoria
- Paginação completa

**Visualização:**
- Thumbnail da imagem
- Título e resumo
- Categoria e status
- Autor e data
- Ações rápidas (ver, editar, excluir)

### ✍️ Criar Notícia (news-create.php)

**Campos Principais:**
- Título (obrigatório)
- Slug (gerado automaticamente)
- Resumo/Excerpt
- Conteúdo (EditorJS)

**Opções de Publicação:**
- Status: Rascunho, Publicado, Agendado, Arquivado
- Publicar imediatamente
- Agendar para data/hora específica
- Marcar como destaque

**Organização:**
- 6 categorias predefinidas
- Imagem destacada (URL ou upload)

**SEO:**
- Meta título (60 caracteres)
- Meta descrição (160 caracteres)
- Contador de caracteres em tempo real

**Recursos Extras:**
- Geração automática de slug
- Preview de imagem
- Auto-save do conteúdo
- Prevenção de perda de dados

### ✏️ Editar Notícia (news-edit.php)

Todos os recursos do create.php, mais:

**Informações Adicionais:**
- Data de criação
- Data de última atualização
- Data de publicação
- Informações do autor

**Funcionalidades:**
- Alterar status
- Reagendar publicação
- Ver notícia publicada (link direto)
- Estatísticas (se implementado)

### 🗑️ Excluir Notícia (news-delete.php)

**Página de Confirmação:**
- Visualização completa da notícia
- Lista do que será excluído
- Alertas para notícias publicadas
- Sugestões de alternativas (arquivar, despublicar)

## 🎯 Fluxo de Trabalho

### Criar e Publicar Notícia

1. Acesse `admin/news/list.php`
2. Clique em "Nova Notícia"
3. Preencha título e conteúdo
4. Adicione imagem destacada
5. Escolha categoria
6. Configure SEO (opcional)
7. Selecione "Publicado" e marque "Publicar agora"
8. Clique em "Criar Notícia"

### Agendar Publicação

1. Crie a notícia normalmente
2. Selecione status "Agendado"
3. Escolha data e hora futura
4. Salve

> **Nota:** Você precisará implementar um cron job para publicar automaticamente as notícias agendadas.

### Destacar Notícia

1. Edite a notícia
2. Marque "Destacar na página inicial"
3. Salve

## 🔧 Configurações

### Categorias

Categorias predefinidas (edite no código se necessário):

```php
$categories = [
    'lancamentos' => 'Lançamentos',
    'tutoriais' => 'Tutoriais',
    'industria' => 'Indústria',
    'eventos' => 'Eventos',
    'entrevistas' => 'Entrevistas',
    'comunidade' => 'Comunidade'
];
```

### Status

- **draft**: Rascunho (não visível)
- **published**: Publicado (visível no site)
- **scheduled**: Agendado (será publicado na data definida)
- **archived**: Arquivado (não visível, mas preservado)

## 🌐 URLs Amigáveis

O sistema gera slugs automaticamente:

```
"Unity 2024: Novos Recursos"
↓
"unity-2024-novos-recursos"
```

URL final: `https://seusite.com/noticias/unity-2024-novos-recursos`

## 🔍 SEO

### Meta Título
- Máximo: 60 caracteres
- Se vazio: usa o título da notícia
- Aparece na aba do navegador e nos resultados de busca

### Meta Descrição
- Máximo: 160 caracteres  
- Se vazio: usa o excerpt
- Aparece nos resultados de busca do Google

### Imagem em Destaque
- Recomendado: 1200x630px (proporção 1.91:1)
- Usada em compartilhamentos no Facebook, Twitter, etc.

## 📱 Responsividade

Todas as páginas são 100% responsivas:

- Desktop: Layout completo com sidebar
- Tablet: Layout adaptado
- Mobile: Menu colapsável, tabelas scrolláveis

## 🚀 Integrações Possíveis

### Cron Job para Agendamento

Crie um arquivo `cron/publish-scheduled-news.php`:

```php
<?php
require_once '../includes/init.php';

$db = Database::getInstance();

// Publicar notícias agendadas
$db->query("
    UPDATE news 
    SET status = 'published' 
    WHERE status = 'scheduled' 
    AND published_at <= NOW()
");

echo "Notícias agendadas publicadas!";
```

Configure no cron:
```bash
*/5 * * * * php /path/to/cron/publish-scheduled-news.php
```

### RSS Feed

Crie um arquivo `rss.php` na raiz:

```php
<?php
require_once 'includes/init.php';

header('Content-Type: application/rss+xml; charset=utf-8');

$db = Database::getInstance();
$news = $db->fetchAll("
    SELECT * FROM news 
    WHERE status = 'published' 
    ORDER BY published_at DESC 
    LIMIT 20
");

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0">
  <channel>
    <title>Gamedev Academy - Notícias</title>
    <link><?= url('') ?></link>
    <description>Últimas notícias sobre desenvolvimento de jogos</description>
    
    <?php foreach ($news as $item): ?>
    <item>
      <title><?= escape($item['title']) ?></title>
      <link><?= url('noticias/' . $item['slug']) ?></link>
      <description><?= escape($item['excerpt']) ?></description>
      <pubDate><?= date('r', strtotime($item['published_at'])) ?></pubDate>
    </item>
    <?php endforeach; ?>
  </channel>
</rss>
```

### Sitemap

Adicione no `sitemap.xml`:

```php
<?php foreach ($news as $item): ?>
<url>
  <loc><?= url('noticias/' . $item['slug']) ?></loc>
  <lastmod><?= date('Y-m-d', strtotime($item['updated_at'])) ?></lastmod>
  <changefreq>weekly</changefreq>
  <priority>0.8</priority>
</url>
<?php endforeach; ?>
```

## 📊 Estatísticas (Opcional)

Para adicionar estatísticas de visualizações, adicione campos na tabela:

```sql
ALTER TABLE news 
ADD COLUMN views INT DEFAULT 0,
ADD COLUMN likes INT DEFAULT 0,
ADD COLUMN shares INT DEFAULT 0;
```

Incremente no arquivo de visualização:

```php
$db->query("UPDATE news SET views = views + 1 WHERE id = ?", [$id]);
```

## 🎨 Personalização

### Adicionar Nova Categoria

1. Edite `news-create.php` e `news-edit.php`
2. Adicione no array `$categories`

```php
'nome-categoria' => 'Nome Visível'
```

### Adicionar Novo Status

1. Altere o ENUM no banco:

```sql
ALTER TABLE news 
MODIFY COLUMN status ENUM('draft', 'published', 'scheduled', 'archived', 'seu-novo-status');
```

2. Adicione nos arrays `$statusColors` e `$statusLabels`

## 🔒 Segurança

### Validações Implementadas

✅ Título obrigatório
✅ Slug único
✅ Proteção contra XSS (escape)
✅ Validação de caracteres SEO
✅ Confirmação de exclusão

### Recomendações Adicionais

- Validar permissões por role (admin, editor, autor)
- Sanitizar HTML do EditorJS antes de salvar
- Implementar versionamento de conteúdo
- Adicionar log de alterações

## 📖 Exemplo de Uso Frontend

Página de listagem `noticias.php`:

```php
<?php
$db = Database::getInstance();
$news = $db->fetchAll("
    SELECT * FROM news 
    WHERE status = 'published' 
    ORDER BY published_at DESC 
    LIMIT 12
");

foreach ($news as $item):
?>
<article>
    <img src="<?= escape($item['featured_image']) ?>" alt="<?= escape($item['title']) ?>">
    <h2><?= escape($item['title']) ?></h2>
    <p><?= escape($item['excerpt']) ?></p>
    <a href="<?= url('noticias/' . $item['slug']) ?>">Ler mais</a>
</article>
<?php endforeach; ?>
```

Página individual `noticia.php`:

```php
<?php
$slug = $_GET['slug'] ?? '';
$news = $db->fetch("SELECT * FROM news WHERE slug = ? AND status = 'published'", [$slug]);

if (!$news) {
    header("HTTP/1.0 404 Not Found");
    include '404.php';
    exit;
}

// Renderizar conteúdo
$html = EditorJSLoader::editorJSToHtml($news['content']);
?>
<article>
    <h1><?= escape($news['title']) ?></h1>
    <img src="<?= escape($news['featured_image']) ?>" alt="">
    <div class="content"><?= $html ?></div>
</article>
```

## 🆘 Solução de Problemas

### Slug duplicado
- O sistema adiciona timestamp automaticamente
- Ou edite manualmente para tornar único

### EditorJS não aparece
- Verifique se `editorjs-loader.php` está incluído
- Confirme que os CSS e JS foram carregados

### Imagem não aparece
- Verifique URL da imagem
- Confirme que o caminho está correto
- Teste a URL diretamente no navegador

### Data agendada não funciona
- Implemente o cron job de publicação automática
- Verifique timezone do servidor

## 📚 Próximos Passos

### Recursos Avançados

- [ ] Sistema de comentários
- [ ] Curtidas e reações
- [ ] Compartilhamento social
- [ ] Newsletter/email
- [ ] Notícias relacionadas
- [ ] Busca com Elasticsearch
- [ ] Cache com Redis
- [ ] CDN para imagens

### Melhorias

- [ ] Editor de imagens integrado
- [ ] Galeria de imagens
- [ ] Tags/taxonomia
- [ ] Múltiplos autores
- [ ] Workflows de aprovação
- [ ] Tradução multilíngue

---

**Desenvolvido para Gamedev Academy**  
Sistema profissional de gerenciamento de notícias com EditorJS

**Versão:** 1.0.0  
**Data:** 2024  
**Licença:** Proprietária