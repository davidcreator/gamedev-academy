# ❓ Perguntas Frequentes (FAQ)

Encontre respostas para as dúvidas mais comuns sobre o GameDev Academy.

---

## 📚 Índice

- [Geral](#geral)
- [Instalação](#instalação)
- [Tutoriais](#tutoriais)
- [Engines](#engines)
- [Contribuição](#contribuição)
- [Técnico](#técnico)

---

## 🌐 Geral

### O que é o GameDev Academy?

O GameDev Academy é uma plataforma educacional open-source dedicada ao ensino de desenvolvimento de jogos. Oferecemos tutoriais, exemplos e recursos para desenvolvedores de todos os níveis.

### O conteúdo é gratuito?

Sim! Todo o conteúdo é 100% gratuito e open-source sob a licença MIT.

### Preciso saber programar para começar?

Não! Temos uma trilha para iniciantes absolutos que começa do zero.

### Em que idiomas o conteúdo está disponível?

Atualmente em Português (BR). Contribuições para traduções são bem-vindas!

### Posso usar os assets em meus jogos comerciais?

Sim! Todos os assets estão sob licença permissiva. Verifique a licença específica de cada asset.

---

## 🔧 Instalação

### Como faço para clonar o repositório?

```bash
git clone https://github.com/davidcreator/gamedev-academy.git
cd gamedev-academy
```

## Preciso de algum software específico?
Depende da trilha escolhida:

* Unity: Unity Hub + Unity 2021.3 LTS
* Godot: Godot 4.0+
* Pygame: Python 3.8+
* Phaser: Node.js 16+

## O repositório é muito grande para clonar?
Use clone superficial para download mais rápido:
```bash
git clone --depth 1 https://github.com/davidcreator/gamedev-academy.git
```

## Como atualizo minha cópia local?
```bash
git pull origin main
```

## 📖 Tutoriais
### Por onde devo começar?
1. Se nunca programou: tutorials/beginner/01-intro-gamedev/
1. Se já programa: tutorials/beginner/03-first-game/
1. Se já fez jogos: tutorials/intermediate/

#### Quanto tempo leva para completar um tutorial?
| Nível |	Tempo Médio |
| ----- | ------------ |
| Beginner | 	1-2 horas |
| Intermediate | 2-4 horas |
| Advanced |	4-8 horas |

#### Os tutoriais têm pré-requisitos?
Sim, cada tutorial lista seus pré-requisitos no README.md inicial.

#### Posso pular tutoriais?
Pode, mas recomendamos seguir a ordem, especialmente nos níveis iniciais.

#### Onde encontro ajuda se travar em um tutorial?
1. Verifique a seção "Problemas Comuns" do tutorial
1. Consulte o código final na pasta final/
1. Abra uma issue no GitHub
1. Pergunte na comunidade

## 🎮 Engines
#### Qual engine devo escolher?
| Engine |	Melhor Para |
| ------ | ------------ |
| Unity | Jogos 2D/3D profissionais, mobile, VR |
| Godot | Jogos 2D, open-source, aprendizado |
| Pygame | Protótipos, aprender programação |
| Phaser | Jogos web, HTML5 |

#### Posso usar uma engine diferente?
Sim! Os conceitos são transferíveis. Contribuições para outras engines são bem-vindas.

#### Unity ou Unreal?
Para iniciantes, recomendamos Unity. Unreal tem curva de aprendizado mais íngreme.

#### Godot 3 ou 4?
Recomendamos Godot 4 para novos projetos. Os tutoriais são atualizados para a versão mais recente.

## 🤝 Contribuição
### Como posso contribuir?
* Reportar bugs
* Sugerir features
* Melhorar documentação
* Adicionar tutoriais
* Contribuir com assets
*Leia o Guia de Contribuição completo.*

### Preciso ser expert para contribuir?
Não! Contribuições de todos os níveis são valorizadas, desde correção de typos até novos tutoriais.

### Minha contribuição será creditada?
Sim! Todos os contribuidores são reconhecidos no projeto.

### Quanto tempo leva para um PR ser revisado?
Geralmente 1-7 dias, dependendo da complexidade.

## 🔧 Técnico
### Quais são os requisitos mínimos de sistema?
**Para desenvolvimento geral:**

* 8GB RAM (16GB recomendado)
* 10GB espaço em disco
* Processador dual-core

**Para Unity:**
* GPU com suporte a DirectX 11/OpenGL 4.1

### Por que meu projeto Unity não abre?
1. Verifique a versão do Unity (2021.3 LTS)
1. Abra pelo Unity Hub
1. Deixe importar todos os assets

### Pygame não instala corretamente?
```bash
# Atualize o pip
pip install --upgrade pip

# Instale com flag específica
pip install pygame --pre
```

### Como reporto um bug?
1. Verifique se já existe issue similar
1. Use o template de bug report
1. Inclua: OS, versão, passos para reproduzir

### O código de exemplo não funciona?
1. Verifique se seguiu todos os passos
1. Compare com o código na pasta final/
1. Verifique a versão da engine/linguagem
1. Abra uma issue se o problema persistir

## 🎯 Dicas Extras
### Melhores práticas para estudar
⏰ Dedique tempo regular (30min-1h/dia)
💻 Digite o código, não copie/cole
🔧 Experimente modificar os exemplos
📝 Faça anotações
🎮 Complete os projetos práticos

## Recursos complementares
* Game Programming Patterns
* GDQuest (Godot)
* Brackeys (Unity)

## 🆘 Ainda com Dúvidas?
Se sua pergunta não foi respondida:
🔍 Pesquise nas Issues
📝 Abra uma nova issue com a label question
💬 Entre em contato com a comunidade
