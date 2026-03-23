# Seguranca

## Escopo suportado

Hoje a referencia de suporte e o estado mais recente do branch principal do repositorio. Nao existe politica formal de suporte para releases antigos.

## Como reportar vulnerabilidades

Nao abra issue publica para vulnerabilidades ainda sem correcao.

Use uma destas opcoes:

1. GitHub Security Advisory privado, se estiver habilitado no repositorio.
2. Canal privado definido pelos mantenedores atuais.

Inclua no relato:

- descricao do problema;
- impacto esperado;
- passos para reproduzir;
- arquivos ou rotas afetadas;
- sugestao de mitigacao, se houver.

## Tempo de resposta recomendado para a comunidade

- confirmacao de recebimento: ate 48 horas;
- triagem inicial: ate 7 dias;
- divulgacao publica: somente apos mitigacao ou acordo coordenado.

## Checklist minimo antes de colocar em producao

- deixar `DEBUG_MODE` desligado;
- usar HTTPS;
- definir `COOKIE_SECURE=true` em ambiente HTTPS;
- revisar permissoes de escrita so nas pastas necessarias;
- proteger ou remover o acesso a `/install/`;
- proteger `config.php`, que contem credenciais e constantes sensiveis;
- revisar estrategia de backup do banco e de uploads;
- manter dependencias do Composer atualizadas.

## Areas sensiveis do projeto

### Instalador

- `install/`
- `includes/install-state.php`
- `config.php`

Risco:

- reconfiguracao indevida do sistema;
- exposicao de ambiente;
- reinstalacao nao autorizada.

### Autenticacao e sessao

- `classes/Auth.php`
- `login.php`
- `register.php`
- `forgot-password.php`
- `reset-password.php`
- `includes/auth/`

Risco:

- tomada de conta;
- bypass de autenticacao;
- problemas de sessao e remember me.

### Uploads e conteudo

- `includes/upload-handler.php`
- `uploads/`
- modulos de licoes e noticias

Risco:

- upload de arquivos maliciosos;
- XSS armazenado;
- exposicao de arquivos privados.

### Configuracao e bootstrap

- `includes/config.php`
- `config/database.php`
- `includes/functions.php`

Risco:

- vazamento de segredos;
- comportamento inseguro em producao;
- regressao em sanitizacao e redirecionamento.

## Praticas obrigatorias em contribuicoes de seguranca

- manter queries parametrizadas;
- escapar saida HTML;
- validar upload por extensao, MIME e destino;
- nao expor stack trace em producao;
- evitar logs com secrets, tokens ou senhas.

## Limites atuais

O projeto ainda nao conta com:

- pipeline de CI de seguranca;
- suite automatizada cobrindo auth e instalacao;
- processo formal de release hardening.

Por isso, qualquer alteracao nas areas sensiveis acima deve vir acompanhada de validacao manual detalhada.
