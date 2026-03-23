# Business Plan: Certificados, Monetizacao e Gestao Financeira

## Objetivo
Estruturar a GameDev Academy como uma plataforma com quatro linhas de receita e uma politica clara de certificados:

1. Cursos gratuitos para aquisicao e topo de funil.
2. Cursos pagos avulsos para venda direta.
3. Cursos de longa duracao para maior ticket medio.
4. Assinaturas para receita recorrente previsivel.

## Politica de certificados
### Cursos gratuitos
- Certificado emitido ao concluir 100% do curso.
- Objetivo: gerar prova social, aumentar ativacao e incentivar migracao para planos pagos.

### Cursos pagos avulsos
- Certificado emitido somente quando existirem:
  - conclusao do curso;
  - pagamento confirmado.
- Objetivo: proteger o valor percebido do certificado e evitar emissao em compras pendentes, canceladas ou reembolsadas.

### Cursos acessados por assinatura
- Certificado emitido quando:
  - o curso estiver concluido;
  - houver assinatura paga ativa;
  - o plano permitir certificados.
- Observacao: distribuicao detalhada do pool de instrutores por assinatura depende de metricas de consumo por curso/instrutor.

## Catalogo de produtos
### 1. Cursos gratuitos
- Papel: entrada no ecossistema.
- Faixa de carga horaria sugerida: 1h a 8h.
- Meta: gerar cadastro, primeira conclusao e pedido de upgrade.
- KPI principal: taxa de cadastro para primeiro curso concluido.

### 2. Cursos pagos avulsos
- Papel: monetizacao direta de skills especificas.
- Faixa sugerida: 6h a 24h.
- Preco sugerido: ticket acessivel e promocional.
- KPI principal: conversao por pagina de curso e receita liquida por curso.

### 3. Cursos de longa duracao
- Papel: ticket premium e trilha profissional.
- Regra operacional adotada no sistema: curso longo quando a carga horaria for igual ou superior ao limite configurado.
- Valor agregado: projeto final, suporte mais forte, certificacao premium.
- KPI principal: ticket medio, taxa de conclusao e margem por coorte/programa.

### 4. Assinaturas
- Papel: previsibilidade de caixa.
- Modelo sugerido:
  - mensal;
  - anual com desconto;
  - certificados habilitados apenas em planos pagos.
- KPI principal: MRR, ARR, churn, LTV e taxa de upgrade.

## Politica comercial recomendada
### Cursos gratuitos
- Sem repasse direto por matricula.
- Medir CAC indireto, conversao para paid e impacto em retenção.

### Cursos pagos avulsos
- Regra padrao sugerida:
  - 60% instrutor;
  - 40% plataforma.

### Cursos de longa duracao
- Regra padrao sugerida:
  - 70% instrutor;
  - 30% plataforma.
- Justificativa: maior demanda de producao, suporte e curadoria do instrutor.

### Assinaturas
- Regra padrao sugerida:
  - 40% da receita recorrente compoe pool de instrutores;
  - 60% fica com a plataforma.
- Distribuicao recomendada do pool:
  - minutos assistidos ponderados;
  - conclusoes;
  - nota do curso;
  - participacao em atividades avaliativas.

## Politica de repasse para instrutores
- Fechamento: mensal.
- Retencao: 14 dias apos o fechamento ou apos a confirmacao do pagamento, para absorver cancelamentos e chargebacks.
- Base de calculo:
  - receita liquida do curso;
  - menos descontos aplicados;
  - menos reembolsos no periodo.
- Relatorio minimo por instrutor:
  - vendas;
  - receita bruta;
  - descontos;
  - receita liquida;
  - comissao do instrutor;
  - parcela da plataforma;
  - repasses pagos;
  - repasses pendentes.

## DRE operacional recomendada
### Receitas
- Cursos pagos avulsos.
- Cursos de longa duracao.
- Receita recorrente de assinaturas.
- Ajustes manuais e afiliacoes futuras.

### Custos e despesas
- Repasses a instrutores.
- Infraestrutura.
- Ferramentas e licencas.
- Marketing.
- Suporte.
- Juridico e contabilidade.
- Tributos.

### Indicadores obrigatorios
- Receita bruta.
- Receita liquida.
- Reembolsos.
- MRR.
- ARR.
- Despesas pagas.
- Repasses pendentes.
- Saldo operacional.

## O que foi implementado nesta etapa
### Regra de certificados
- O fluxo de estudo agora usa uma camada dedicada para emissao de certificados.
- Cursos gratuitos podem emitir certificado ao concluir.
- Cursos pagos exigem conclusao e pagamento confirmado.
- Assinaturas ativas com certificado habilitado tambem podem liberar emissao.

### Gestao financeira
- Foi criada uma base de relatorio financeiro no admin.
- O admin agora possui um painel de:
  - receita de cursos;
  - MRR e ARR de assinaturas;
  - preview de repasse para instrutores;
  - despesas por categoria;
  - saldo operacional.

### Upgrade incremental
- Foi incluido um script para bancos ja existentes:
  - `php scripts/install-business-finance-upgrade.php`
- Esse upgrade cria a tabela `financial_expenses` e registra as chaves de negocio/financeiro no `settings`.

## Arquivos principais desta entrega
- `classes/CertificateService.php`
- `classes/FinanceService.php`
- `classes/Setting.php`
- `learn.php`
- `admin/finance/index.php`
- `admin/includes/sidebar.php`
- `admin/settings/settings.php`
- `scripts/install-business-finance-upgrade.php`

## Proximos passos recomendados
1. Criar tela administrativa para gerenciamento de planos de assinatura e catalogo por plano.
2. Registrar pagamentos de assinatura em tabela de transacoes recorrentes.
3. Criar distribuicao real do pool de assinatura por consumo de conteudo.
4. Criar emissao visual do certificado com pagina publica de validacao.
5. Criar relatorio DRE mensal exportavel em CSV/PDF.
