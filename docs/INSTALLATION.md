# 🔧 Guia de Instalação

Este guia irá ajudá-lo a configurar o GameDev Academy em sua máquina local.

---

## 📋 Pré-requisitos

Antes de começar, certifique-se de ter instalado:

### Obrigatórios
- **Git** (versão 2.30 ou superior)
  - [Download Git](https://git-scm.com/downloads)
- **Editor de Código** (recomendamos VS Code)
  - [Download VS Code](https://code.visualstudio.com/)

### Opcionais (dependendo da trilha escolhida)

| Ferramenta | Versão Mínima | Download |
|------------|---------------|----------|
| Unity | 2021.3 LTS | [unity.com](https://unity.com/download) |
| Godot | 4.0+ | [godotengine.org](https://godotengine.org/download) |
| Python | 3.8+ | [python.org](https://www.python.org/downloads/) |
| Node.js | 16+ | [nodejs.org](https://nodejs.org/) |

---

## 🚀 Instalação Passo a Passo

### 1️⃣ Clone o Repositório

```bash
# Via HTTPS
git clone https://github.com/davidcreator/gamedev-academy.git

# Via SSH (recomendado)
git clone git@github.com:davidcreator/gamedev-academy.git
```

### 2️⃣ Navegue até o Diretório
```bash
cd gamedev-academy
```
### 3️⃣ Verifique a Estrutura
```bash
# Linux/Mac
ls -la

# Windows
dir
```
### 4️⃣ Escolha sua Trilha
```bash
# Para tutoriais de iniciante
cd tutorials/beginner

# Para exemplos prontos
cd examples
```

## 🎮 Configuração por Engine
### Unity
```bash
# 1. Abra o Unity Hub
# 2. Clique em "Add"
# 3. Navegue até gamedev-academy/examples/unity
# 4. Selecione a pasta do projeto desejado
```

### Godot
```bash
# 1. Abra o Godot
# 2. Clique em "Import"
# 3. Navegue até gamedev-academy/examples/godot
# 4. Selecione o arquivo project.godot
```

### Pygame
```bash
# Crie um ambiente virtual
python -m venv venv

# Ative o ambiente
# Windows
venv\Scripts\activate
# Linux/Mac
source venv/bin/activate

# Instale as dependências
pip install -r requirements.txt
```

## ✅ Verificação da Instalação
Execute o script de verificação:
```bash
# Linux/Mac
./scripts/verify-installation.sh

# Windows
scripts\verify-installation.bat
```

Saída esperada:
```text
✅ Git: Instalado (v2.40.0)
✅ Estrutura: OK
✅ Exemplos: Disponíveis
✅ Pronto para começar!
```

## 🔄 Atualizações
Mantenha seu repositório local atualizado:
```bash
# Buscar atualizações
git fetch origin

# Atualizar branch principal
git pull origin main
```

## ❓ Problemas Comuns
**Erro de Permissão (Linux/Mac)**
```bash
chmod +x scripts/*.sh
```

**Git não reconhecido (Windows)**
Adicione o Git ao PATH do sistema ou reinstale marcando a opção "Add to PATH".

**Projeto Unity não abre**
Verifique se a versão do Unity instalada é compatível (2021.3 LTS ou superior).

## 📞 Precisa de Ajuda?
📖 Consulte a FAQ
🐛 Abra uma Issue
💬 Entre na nossa comunidade