# Toolbar Fixa - Guia de Uso

## 🎨 Nova Interface Estilo Word/Google Docs

A toolbar agora está sempre visível no topo do editor, tornando muito mais fácil adicionar e formatar conteúdo!

## 📋 Recursos da Toolbar

### Seletor de Estilo
- **Parágrafo** - Texto normal
- **Título 1 a 4** - Diferentes níveis de títulos

### Grupo de Listas
- **Lista** 📝 - Lista com marcadores
- **Numerada** 🔢 - Lista numerada
- **Tarefas** ✅ - Lista de checklist

### Grupo de Blocos
- **Citação** 💬 - Bloco de citação
- **Código** 💻 - Bloco de código
- **Divisor** ➖ - Linha separadora

### Grupo de Mídia
- **Imagem** 🖼️ - Inserir imagem
- **Tabela** 📊 - Criar tabela
- **Vídeo** 🎥 - Incorporar vídeo (YouTube, Vimeo, etc)

### Grupo Outros
- **Aviso** ⚠️ - Caixa de aviso/alerta
- **HTML** 📄 - Inserir HTML customizado

## 🖱️ Como Usar

### Método 1: Toolbar (Recomendado)
1. Clique no botão desejado na toolbar
2. O bloco será inserido automaticamente
3. Comece a digitar

### Método 2: Inline (Tradicional)
1. Digite "/" no editor
2. Escolha o bloco desejado
3. Ou use Tab para ver todas as opções

### Método 3: Atalhos de Teclado
- `Ctrl/Cmd + B` - Negrito
- `Ctrl/Cmd + I` - Itálico
- `Ctrl/Cmd + K` - Código inline
- `Ctrl/Cmd + Shift + H` - Header
- `Ctrl/Cmd + Shift + O` - Citação
- `Ctrl/Cmd + Shift + C` - Código

## 💡 Dicas

### Formatação Inline
Selecione qualquer texto para ver as opções de formatação:
- **Negrito**
- *Itálico*
- `Código inline`
- Marcador (highlight)
- Sublinhado
- Link

### Navegação Rápida
- Use as setas ↑↓ para navegar entre blocos
- Arraste os blocos para reordenar
- Clique nos três pontos (⋮) para mais opções

### Mobile
Em dispositivos móveis, a toolbar mostra apenas os ícones (sem texto) e permite scroll horizontal.

## 🎯 Exemplo de Fluxo de Trabalho

1. **Título**: Clique em "Título 1" no seletor
2. **Introdução**: Digite normalmente (parágrafo)
3. **Lista**: Clique no botão "Lista"
4. **Imagem**: Clique no botão "Imagem"
5. **Código**: Clique no botão "Código"
6. **Conclusão**: Digite normalmente

## 🔄 Funcionalidades Mantidas

A toolbar fixa NÃO substitui as funcionalidades originais:
- Você ainda pode usar "/" para adicionar blocos
- O menu de contexto (três pontos) continua disponível
- Arrastar e soltar blocos funciona normalmente
- Todas as configurações de cada bloco estão preservadas

## 🎨 Personalização

Se quiser modificar a toolbar, edite:
- `editorjs-custom.css` - Estilos visuais
- `editorjs-init.js` - Botões e funcionalidades

## 📱 Responsividade

A toolbar se adapta automaticamente:
- **Desktop**: Mostra ícones + texto
- **Tablet**: Mostra ícones + texto (scroll se necessário)
- **Mobile**: Mostra apenas ícones com scroll horizontal

## 🆘 Solução de Problemas

### Toolbar não aparece
1. Limpe o cache do navegador
2. Verifique se `editorjs-custom.css` está carregado
3. Verifique o console por erros

### Botões não funcionam
1. Espere o editor carregar completamente
2. Verifique se `window.editorInstance` existe no console
3. Recarregue a página

### Layout quebrado no mobile
1. Verifique se tem `viewport` meta tag no head
2. Teste em modo responsivo do navegador
3. Limpe cache do CSS

---

**Desenvolvido para Gamedev Academy**  
Toolbar personalizada para melhor experiência de edição