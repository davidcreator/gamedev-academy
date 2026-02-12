# 🏗️ Arquitetura do Projeto

Este documento descreve a arquitetura e organização do GameDev Academy.

---

## 📊 Visão Geral
    ┌─────────────────────────────────────────────────────────────┐
    │ GAMEDEV ACADEMY │
    ├─────────────────────────────────────────────────────────────┤
    │ ┌─────────────┐ ┌─────────────┐ ┌─────────────────────┐ │
    │ │ TUTORIALS │ │ ASSETS │ │ EXAMPLES │ │
    │ │ │ │ │ │ │ │
    │ │ • Beginner │ │ • Sprites │ │ • Unity Projects │ │
    │ │ • Intermed. │ │ • Sounds │ │ • Godot Projects │ │
    │ │ • Advanced │ │ • Fonts │ │ • Pygame Projects │ │
    │ └─────────────┘ └─────────────┘ └─────────────────────┘ │
    ├─────────────────────────────────────────────────────────────┤
    │ ┌─────────────┐ ┌─────────────┐ ┌─────────────────────┐ │
    │ │ TEMPLATES │ │ TOOLS │ │ DOCS │ │
    │ │ │ │ │ │ │ │
    │ │ • Starters │ │ • Scripts │ │ • Guides │ │
    │ │ • Boilerpl. │ │ • Utilities │ │ • API Reference │ │
    │ └─────────────┘ └─────────────┘ └─────────────────────┘ │
    └─────────────────────────────────────────────────────────────┘

## 📁 Estrutura de Diretórios
    gamedev-academy/
    │
    ├── 📁 assets/ # Recursos reutilizáveis
    │ ├── 📁 sprites/ # Imagens e spritesheets
    │ │ ├── 📁 characters/
    │ │ ├── 📁 environments/
    │ │ ├── 📁 ui/
    │ │ └── 📁 effects/
    │ ├── 📁 sounds/ # Efeitos sonoros
    │ │ ├── 📁 sfx/
    │ │ └── 📁 music/
    │ ├── 📁 fonts/ # Fontes para jogos
    │ └── 📁 tilesets/ # Tilesets para level design
    │
    ├── 📁 docs/ # Documentação
    │ ├── 📄 INSTALLATION.md
    │ ├── 📄 GETTING-STARTED.md
    │ ├── 📄 CONTRIBUTING.md
    │ ├── 📄 CODE-OF-CONDUCT.md
    │ ├── 📄 ARCHITECTURE.md
    │ ├── 📄 API.md
    │ ├── 📄 FAQ.md
    │ └── 📄 CHANGELOG.md
    │
    ├── 📁 examples/ # Projetos de exemplo
    │ ├── 📁 unity/
    │ │ ├── 📁 pong/
    │ │ ├── 📁 platformer/
    │ │ └── 📁 rpg-starter/
    │ ├── 📁 godot/
    │ │ ├── 📁 space-shooter/
    │ │ ├── 📁 puzzle-game/
    │ │ └── 📁 top-down-rpg/
    │ ├── 📁 pygame/
    │ │ ├── 📁 snake/
    │ │ ├── 📁 breakout/
    │ │ └── 📁 asteroids/
    │ └── 📁 phaser/
    │ ├── 📁 endless-runner/
    │ └── 📁 match-three/
    │
    ├── 📁 tutorials/ # Tutoriais organizados
    │ ├── 📁 beginner/
    │ │ ├── 📁 01-intro-gamedev/
    │ │ ├── 📁 02-programming-basics/
    │ │ ├── 📁 03-first-game/
    │ │ └── 📁 04-game-loop/
    │ ├── 📁 intermediate/
    │ │ ├── 📁 01-physics/
    │ │ ├── 📁 02-ai-basics/
    │ │ ├── 📁 03-state-machines/
    │ │ └── 📁 04-save-system/
    │ ├── 📁 advanced/
    │ │ ├── 📁 01-networking/
    │ │ ├── 📁 02-shaders/
    │ │ ├── 📁 03-procedural/
    │ │ └── 📁 04-optimization/
    │ └── 📁 engine-specific/
    │ ├── 📁 unity/
    │ ├── 📁 godot/
    │ └── 📁 pygame/
    │
    ├── 📁 templates/ # Templates iniciais
    │ ├── 📁 unity/
    │ │ ├── 📁 2d-platformer/
    │ │ └── 📁 3d-fps/
    │ ├── 📁 godot/
    │ │ ├── 📁 2d-adventure/
    │ │ └── 📁 mobile-game/
    │ └── 📁 pygame/
    │ └── 📁 arcade-game/
    │
    ├── 📁 tools/ # Ferramentas e scripts
    │ ├── 📁 scripts/
    │ │ ├── 📄 setup.sh
    │ │ ├── 📄 build.sh
    │ │ └── 📄 verify.sh
    │ ├── 📁 generators/
    │ └── 📁 converters/
    │
    ├── 📁 .github/ # Configurações GitHub
    │ ├── 📁 ISSUE_TEMPLATE/
    │ ├── 📁 workflows/
    │ └── 📄 PULL_REQUEST_TEMPLATE.md
    │
    ├── 📄 README.md # Documentação principal
    ├── 📄 LICENSE # Licença do projeto
    ├── 📄 CONTRIBUTING.md # Guia de contribuição (link)
    └── 📄 .gitignore # Arquivos ignorados

## 🎯 Princípios de Design

### 1. Modularidade
Cada componente é independente e reutilizável.

    [Tutorial] ──uses──▶ [Assets]
    │
    └──references──▶ [Examples]

### 2. Progressão
Conteúdo organizado por nível de dificuldade.

    Beginner ──▶ Intermediate ──▶ Advanced
    │ │ │
    ▼ ▼ ▼
    Basics Systems Complex
    Concepts Patterns Solutions

### 3. Consistência
Estrutura uniforme em todos os tutoriais.

    tutorial/
    ├── README.md # Introdução e objetivos
    ├── assets/ # Recursos específicos
    ├── src/ # Código fonte
    ├── steps/ # Passo a passo
    │ ├── step-01/
    │ ├── step-02/
    │ └── step-N/
    └── final/ # Versão completa

## 🔄 Fluxo de Dados
    ┌──────────────────────────────────────────────────────────┐
    │ USUÁRIO/ESTUDANTE │
    └────────────────────────┬─────────────────────────────────┘
    │
    ▼
    ┌──────────────────────────────────────────────────────────┐
    │ README.md │
    │ (Ponto de entrada principal) │
    └────────────────────────┬─────────────────────────────────┘
    │
    ┌─────────────┼─────────────┐
    ▼ ▼ ▼
    ┌───────────┐ ┌───────────┐ ┌───────────┐
    │ Tutorials │ │ Examples │ │ Docs │
    └─────┬─────┘ └─────┬─────┘ └─────┬─────┘
    │ │ │
    └─────────────┴─────────────┘
    │
    ▼
    ┌─────────────────┐
    │ Assets │
    │ (Recursos) │
    └─────────────────┘

## 📦 Dependências por Engine

### Unity
    Unity 2021.3 LTS
    ├── TextMeshPro (incluso)
    ├── 2D Tilemap Editor
    └── Input System (novo)

### Godot
    Godot 4.0+
    ├── GDScript (principal)
    └── C# (alternativo)

### Pygame
    Python 3.8+
    ├── pygame 2.0+
    ├── numpy (opcional)
    └── pillow (opcional)

## 🔐 Convenções de Nomenclatura

### Arquivos

| Tipo | Convenção | Exemplo |
|------|-----------|---------|
| Tutorial | kebab-case | `01-getting-started.md` |
| Asset | kebab-case | `player-sprite.png` |
| Script (Python) | snake_case | `player_controller.py` |
| Script (C#) | PascalCase | `PlayerController.cs` |
| Script (GDScript) | snake_case | `player_controller.gd` |

### Diretórios
tipo-descricao/ # Geral
01-nome-tutorial/ # Tutoriais numerados
asset-category/ # Assets por categoria

## 🚀 Performance e Otimização

### Assets
- Sprites: PNG com compressão
- Áudio: OGG para efeitos, MP3 para música
- Tamanho máximo por asset: 5MB

### Repositório
- Uso de Git LFS para assets grandes
- Branches leves para features
- Histórico limpo e organizado

---

## 📈 Escalabilidade

O projeto é desenhado para crescer:

    Fase 1 (Atual)
    ├── Tutoriais básicos
    ├── 3 engines principais
    └── Documentação inicial

    Fase 2 (Futuro)
    ├── Tutoriais avançados
    ├── Mais engines
    ├── Ferramentas próprias
    └── Comunidade

    Fase 3 (Longo prazo)
    ├── Cursos completos
    ├── Certificações
    ├── Mentoria
    └── Marketplace de assets
