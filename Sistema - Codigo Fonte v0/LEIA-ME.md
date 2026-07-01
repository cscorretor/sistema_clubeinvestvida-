# Sistema Clube Investvida — Código-fonte (v0)

Protótipo navegável do novo sistema que substitui o Segflex. Foco: **Seguros de Pessoas**.
Frontend (`public/*.html`) e banco (`database/*.sql`) mantidos pelo Claude; backend Laravel e versionamento pelo Codex.

## Telas (`public/`)

| Arquivo | Tela | Destaques |
|---|---|---|
| `login.html` | Login / 2FA | Autenticação com verificação em duas etapas |
| `dashboard.html` | Dashboard | KPIs, produção por mês, carteira por ramo, renovações |
| `clientes.html` | Lista de Clientes | Busca, filtros; linhas abrem a ficha |
| `cliente-detalhe.html` | Ficha 360º do Cliente | Abas: apólices, documentos, chamados, linha do tempo, dados |
| `cadastro-cliente.html` | Cadastro de Cliente | CEP automático, validação de CPF, PF/PJ, cônjuge, contatos |
| `apolice-pessoas.html` | Apólice de Pessoas | Vidas, beneficiários (soma 100%), coberturas por ramo, fatura |
| `leads.html` | Funil de Leads / CRM | Kanban, score ICP, importar Google Contatos |
| `chamados.html` | Chamados / Sinistros | Tipos, prazos, prioridade, status, filtros |
| `financeiro.html` | Financeiro / Comissões | Parcelas, baixa, rateio, pagar×receber |
| `cofre.html` | Cofre Digital | Upload arrastar-e-soltar, grade/lista, preview |
| `configuracoes.html` | Configurações | Usuários & permissões, auditoria, corretora, segurança |

Todas as telas internas compartilham o mesmo menu lateral. Comece por `login.html` ou `dashboard.html`.

## Banco de dados (`database/`)
- `01_schema_core.sql` — clientes, contatos, cônjuge, CNH, contas, auditoria.
- `02_schema_seguros.sql` — seguradoras, ramos, apólices, vidas, beneficiários, coberturas, parcelas, rateio.
- `03_schema_leads_chamados.sql` — usuários/permissões, leads/CRM, interações, campanhas, chamados/movimentos.

## Estágio
Interface completa e funcional (validações, cálculos, gráficos e navegação no navegador); **sem gravar no banco ainda** — isso é o backend Laravel (Codex).

## Próximos passos
1. Backend Laravel: conectar Login e Cadastro ao MariaDB (autenticar + gravar).
2. OCR de proposta (IA) para autopreencher o cadastro pelo PDF.
3. Google Contatos + score ICP reais no funil de leads.
