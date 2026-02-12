# 🤝 Guia de Contribuição

Obrigado pelo interesse em contribuir com o GameDev Academy! Este documento fornece diretrizes para contribuir com o projeto.

---

## 📋 Índice

- [Código de Conduta](#código-de-conduta)
- [Como Posso Contribuir?](#como-posso-contribuir)
- [Configuração do Ambiente](#configuração-do-ambiente)
- [Processo de Contribuição](#processo-de-contribuição)
- [Padrões de Código](#padrões-de-código)
- [Padrões de Commit](#padrões-de-commit)
- [Pull Requests](#pull-requests)

---

## 📜 Código de Conduta

Este projeto adota um [Código de Conduta](./CODE-OF-CONDUCT.md). Ao participar, espera-se que você mantenha este código.

---

## 🎯 Como Posso Contribuir?

### 🐛 Reportando Bugs

Encontrou um bug? Ajude-nos a corrigi-lo!

1. Verifique se já não existe uma [issue](https://github.com/davidcreator/gamedev-academy/issues) sobre o problema
2. Se não existir, crie uma nova issue usando o template de bug
3. Inclua o máximo de detalhes possível

**Template de Bug Report:**
```markdown
## Descrição do Bug
[Descrição clara e concisa]

## Passos para Reproduzir
1. Vá para '...'
2. Clique em '...'
3. Veja o erro

## Comportamento Esperado
[O que deveria acontecer]

## Screenshots
[Se aplicável]

## Ambiente
- OS: [ex: Windows 10]
- Engine: [ex: Unity 2021.3]
- Versão: [ex: 1.0.0]
```

## 💡 Sugerindo Features
Tem uma ideia? Adoraríamos ouvir!

1. Verifique se já não existe uma sugestão similar
1. Crie uma issue usando o template de feature request
1. Descreva o problema que a feature resolveria

### 📝 Melhorando a Documentação
Documentação é crucial! Você pode:

* Corrigir erros de digitação
* Melhorar explicações
* Adicionar exemplos
* Traduzir conteúdo

### 💻 Contribuindo com Código
* Corrija bugs existentes
* Implemente novas features
* Melhore performance
* Adicione testes

### 🎨 Contribuindo com Assets
* Sprites e texturas
* Efeitos sonoros
* Músicas (royalty-free)
* Fontes

## 🛠️ Configuração do Ambiente
1. Fork o Repositório
Clique no botão "Fork" no canto superior direito do repositório.

2. Clone seu Fork
```bash
git clone https://github.com/SEU-USERNAME/gamedev-academy.git
cd gamedev-academy
```

3. Configure o Upstream
```bash
git remote add upstream https://github.com/davidcreator/gamedev-academy.git
git fetch upstream
```

4. Crie uma Branch
```bash
git checkout -b feature/minha-feature
```

## 🔄 Processo de Contribuição
```text
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│    Fork     │────▶│   Develop   │────▶│     PR      │
└─────────────┘     └─────────────┘     └─────────────┘
                           │
                    ┌──────┴──────┐
                    ▼             ▼
              ┌─────────┐   ┌─────────┐
              │  Test   │   │ Document│
              └─────────┘   └─────────┘
```

## Passo a Passo
1. Sincronize com upstream
```bash
git fetch upstream
git checkout main
git merge upstream/main
```

2. Crie uma branch
```bash
git checkout -b tipo/descricao-curta
```

3. Faça suas alterações

* Escreva código limpo
* Adicione testes se necessário
* Atualize a documentação

4. Commit suas mudanças
```bash
git add .
git commit -m "tipo: descrição das mudanças"
```

5. Push para seu fork
```bash
git push origin tipo/descricao-curta
```

6. Abra um Pull Request

## 📏 Padrões de Código
### Geral
* Use indentação consistente (2 ou 4 espaços)
* Nomes descritivos para variáveis e funções
* Comente código complexo
* Mantenha funções pequenas e focadas

### Por Linguagem
#### C# (Unity)
```csharp
// PascalCase para classes e métodos públicos
public class PlayerController : MonoBehaviour
{
    // camelCase para variáveis privadas
    private float moveSpeed = 5f;
    
    // Prefixo _ para campos serializados
    [SerializeField] private float _jumpForce = 10f;
    
    public void Move(Vector3 direction)
    {
        // Implementação
    }
}
```

#### GDScript (Godot)
```gdscript
# snake_case para variáveis e funções
extends CharacterBody2D

var move_speed: float = 200.0
var jump_force: float = 400.0

func _physics_process(delta: float) -> void:
    handle_movement(delta)

func handle_movement(delta: float) -> void:
    # Implementação
    pass
```

#### Python (Pygame)
```python
# snake_case para funções e variáveis
class Player:
    def __init__(self):
        self.move_speed = 5
        self.jump_force = 10
    
    def update(self, delta_time: float) -> None:
        """Atualiza a posição do jogador."""
        self._handle_input()
        self._apply_physics(delta_time)
```

## 📝 Padrões de Commit
Usamos Conventional Commits:
```text
tipo(escopo): descrição curta

[corpo opcional]

[rodapé opcional]
```

## Tipos Permitidos
| Tipo |	Descrição |
| ---- | --------- |
| feat |	Nova feature |
| fix | 	Correção de bug |
| docs |	Documentação |
| style |	Formatação (não afeta código) |
| refactor |	Refatoração |
| test |	Adição/correção de testes |
| chore |	Manutenção geral |
| perf |	Melhoria de performance |

## Exemplos
```bash
feat(unity): adiciona sistema de inventário
fix(godot): corrige colisão do player
docs(readme): atualiza instruções de instalação
style(pygame): formata código seguindo PEP8
refactor(core): simplifica game loop
test(physics): adiciona testes de colisão
chore(deps): atualiza dependências
```

## 🔀 Pull Requests
### Checklist
Antes de abrir um PR, verifique:

* Código segue os padrões do projeto
* Testes passam (se aplicável)
* Documentação atualizada
* Commits seguem o padrão
* Branch atualizada com main

## Template de PR
```markdown
## Descrição
[Descreva as mudanças feitas]

## Tipo de Mudança
- [ ] Bug fix
- [ ] Nova feature
- [ ] Breaking change
- [ ] Documentação

## Como Testar
1. [Passos para testar]

## Checklist
- [ ] Código revisado
- [ ] Testes adicionados/atualizados
- [ ] Documentação atualizada

## Screenshots (se aplicável)
[Adicione screenshots]

## Issues Relacionadas
Closes #[número]
```

## Processo de Review
1. Mantenedor revisa o código
1. Feedback é fornecido (se necessário)
1. Alterações são feitas
1. PR é aprovado e merged

## 🏷️ Labels
| Label |	Descrição |
| bug	| Algo não está funcionando |
| enhancement |	Nova feature ou melhoria |
| documentation |	Melhorias na documentação |
| good first issue |	Bom para iniciantes |
| help wanted |	Precisamos de ajuda |
| question |	Dúvida ou discussão |
| wontfix | 	Não será corrigido |

## 🎉 Reconhecimento
Todos os contribuidores são reconhecidos:

* No README principal
* Na seção de Contributors
* Nos release notes

## ❓ Dúvidas?
* Abra uma issue
* Consulte a FAQ

*Obrigado por contribuir! 🙏*