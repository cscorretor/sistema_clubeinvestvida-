# Sistema Clube Investvida — Código-fonte (v0)

Protótipo navegável do novo sistema que substitui o Segflex. Foco: **Seguros de Pessoas**.

## Telas (abra qualquer uma no navegador e circule pela barra lateral)

| Arquivo (em `public/`) | Tela | O que demonstra |
|---|---|---|
| `dashboard.html` | Dashboard | KPIs, produção por mês, carteira por ramo, renovações |
| `clientes.html` | Lista de Clientes | Busca, filtros, carteira, status |
| `cadastro-cliente.html` | Cadastro de Cliente | CEP automático, validação de CPF, PF/PJ, cônjuge, múltiplos contatos |
| `apolice-pessoas.html` | Apólice de Pessoas | Vidas, beneficiários (soma 100%), coberturas por ramo, fatura |
| `leads.html` | Funil de Leads / CRM | Kanban, score ICP, importar Google Contatos |
| `financeiro.html` | Financeiro / Comissões | Parcelas, baixa, rateio de produtores, pagar×receber |
| `cofre.html` | Cofre Digital | Upload arrastar-e-soltar, grade/lista, preview, categorias |
| `configuracoes.html` | Configurações | Usuários & permissões, **auditoria**, dados da corretora, 2FA |

> Sugestão: comece por `dashboard.html` e navegue pelo menu lateral — todas as 7 telas estão interligadas.

## Banco de dados (`database/`)
- `01_schema_core.sql` — clientes, contatos, cônjuge, CNH, contas, **auditoria**.
- `02_schema_seguros.sql` — seguradoras, ramos, apólices, vidas, beneficiários, coberturas, parcelas, rateio.

Importe pelo phpMyAdmin (hPanel) ou `mysql ... < arquivo.sql` quando o banco estiver pronto.

## Estágio atual
Protótipo de interface **completo e funcional** (validações, cálculos e navegação rodam no navegador), mas **ainda sem gravar no banco**. O próximo salto é o **backend Laravel**, que conecta estas telas ao MariaDB.

## Subir pelo GitHub Desktop
Copie a pasta para o repositório local → **Commit** → **Push origin**.

## Próximos passos sugeridos
1. **Backend Laravel**: login + 2FA, e gravar Cadastro/Apólice no MariaDB.
2. **OCR de proposta** (IA gratuita) para autopreencher o cadastro pelo PDF da seguradora.
3. Conectar o **funil de leads** ao Google Contatos + score ICP real.
