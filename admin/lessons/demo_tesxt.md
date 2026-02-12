# 🧭 Título Nível 1 (H1)
```markdown
# 🧭 Título Nível 1 (H1)
```
Texto introdutório do documento. Aqui você explica o propósito geral.
Markdown é simples, elegante e poderoso.

## 📌 Título Nível 2 (H2)
```markdown
## 📌 Título Nível 2 (H2)
```

Texto explicativo com mais detalhes.

### 🔹 Título Nível 3 (H3)
```markdown
### 🔹 Título Nível 3 (H3)
```

Conteúdo mais específico.

#### Título Nível 4 (H4)
##### Título Nível 5 (H5)
###### Título Nível 6 (H6)

```markdown
#### Título Nível 4 (H4)
##### Título Nível 5 (H5)
###### Título Nível 6 (H6)
```
---

# ✍️ Formatação de Texto

**Negrito**
*Itálico*
***Negrito e Itálico***
~~Texto riscado~~
```Código inline```

    Isso é um bloco de citação.

    Pode ter múltiplas linhas.

        E até citações aninhadas.

```markdown
**Negrito**
*Itálico*
***Negrito e Itálico***
~~Texto riscado~~
```Código inline```

    Isso é um bloco de citação.

    Pode ter múltiplas linhas.

        E até citações aninhadas.
```

---

# 📋 Listas
## Lista não ordenada

* Item 1
* Item 2
  * Subitem 2.1
  * Subitem 2.2
* Item 3

## Lista ordenada

1. Primeiro item
1. Segundo item
1. Terceiro item

```markdown
* Item 1
* Item 2
  * Subitem 2.1
  * Subitem 2.2
* Item 3

## Lista ordenada

1. Primeiro item
1. Segundo item
1. Terceiro item
```

---

# 💻 Bloco de Código
## Código JavaScript
```javascript
function saudacao(nome) {
    return `Olá, ${nome}!`;
}

console.log(saudacao("David"));

```

## Código HTML
```html
<section>
  <h1>Título</h1>
  <p>Parágrafo de exemplo.</p>
</section>

```

## Código JSON
```json
{
  "nome": "Projeto Exemplo",
  "versao": "1.0.0",
  "ativo": true
}

```
---

## 📊 Tabelas
```markdonw
| Nome |	Função |	Status |
| ---- | --------- | ------ |
| David |	Desenvolvedor |	✅ Ativo |
| Maria |	Designer |	🟢 Online |
| Carlos |	QA	| ⏳ Testes | 
```
| Nome |	Função |	Status |
| ---- | --------- | ------ |
| David |	Desenvolvedor |	✅ Ativo |
| Maria |	Designer |	🟢 Online |
| Carlos |	QA	| ⏳ Testes | 

## Alinhamento de colunas
```markdown
| Esquerda |	Centro |	Direita |
| :-------- | :--------: | ---------: |
| Texto |	Texto |	Texto |
| Outro |	Exemplo |	123 |
```
| Esquerda |	Centro |	Direita |
| :-------- | :--------: | ---------: |
| Texto |	Texto |	Texto |
| Outro |	Exemplo |	123 |

---

# 🔗 Links e Imagens
## imagens
```markdown
![Texto alternativo](https://via.placeholder.com/150)
```

## links
```markdown
![Texto alternativo](https://linkdesejado.dominio)
```
---

# Renderizar Simbolos e Emojis
## Exemplo:

```markdown
:smile:
```
Resultado :smile: 😊

```markdown
:rocket: 
```

Resultado :rocket: 🚀

## Atalhos do Sistema:
* Windows 10/11: Pressione Tecla Windows + . (ponto).
* macOS: Pressione Command + Control + Space.
* Códigos HTML (Unicode): Use o formato &#x + código. Exemplo: &#x1F604; 
 😄. 

![Lista de Emojis GistFile](https://gist.github.com/rxaviers/7360908#file-gistfile1-md)

---

# Linha Horizontal
## Usando Hífens (Três ou mais)
```markdown
---
```
## Usando Asteriscos (Três ou mais)
```markdown
***
```

## Usando Sublinhado (Três ou mais)
```markdown
___
```

---

# 🧩 Tabelas de Código com Destaque (GitHub Flavored Markdown)
```diff
- linha removida
+ linha adicionada
# comentário
```

```bash
npm install
npm run dev
```

---

# 📚 Bloco de Detalhes (GitHub)
```markdown
<details> <summary>Clique para expandir</summary>

Conteúdo oculto aqui dentro.

Pode incluir:

Listas

Código

Texto formatado

</details>
```
<details> <summary>Clique para expandir</summary>

Conteúdo oculto aqui dentro.

Pode incluir:

Listas

Código

Texto formatado

</details>

---

# 🧠 Misturando Elementos
## Exemplo prático:

📌 Resumo do Projeto

Projeto focado em:
* Performance
* Escalabilidade
* Organização

```yaml
ambiente:
  produção: true
  debug: false
  versao: 2.3
```
---

# 🏁 Conclusão
Markdown é como LEGO para documentação 🧱
Simples, modular, eficiente e elegante.

Se quiser, posso montar uma versão estruturada especificamente para:

* README profissional
* Documentação técnica
* Template para artigos
* Template para propostas comerciais
* Estrutura para documentação de API
* Guia visual para GitHub estilizado

*Qual formato você quer explorar agora?*