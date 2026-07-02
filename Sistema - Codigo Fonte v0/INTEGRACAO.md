# Guia de Integração Frontend → Backend (para o Codex)

Frontend estático (HTML + `assets/css/app.css` + `assets/js/app.js`), sem CDN e sem build.
As telas hoje usam **dados ilustrativos** e, onde há API, um **fallback local** só no protótipo.
Abaixo, os contratos esperados para o backend Laravel ligar cada tela.

## Convenções
- Respostas JSON. Datas ISO `YYYY-MM-DD`. Valores monetários em número (centavos ou decimal — definir no backend).
- Autocomplete usa: `GET /api/<recurso>?q=TERMO` → array de objetos; o campo exibido é configurável (ver abaixo).

## Autocomplete
| Tela | Campo | Endpoint | Campo exibido |
|---|---|---|---|
| cadastro-cliente | Profissão | `GET /api/profissoes?q=` | `titulo` |
| novo-chamado | Cliente | `GET /api/clientes?q=` | `nome` |

Regras: inicia após **3 caracteres**, debounce 250 ms, mostra até 10, permite texto manual, estado "Nenhum resultado".

## Endpoints por tela (sugestão)
| Tela | Método/rota | Uso |
|---|---|---|
| login | `POST /login` + 2FA (`POST /2fa`) | autenticação |
| clientes | `GET /api/clientes` (busca, filtros, paginação) | listagem |
| cliente-detalhe | `GET /api/clientes/{id}` (+ apólices, docs, chamados) | ficha 360º |
| cadastro-cliente | `POST /api/clientes` / `PUT /api/clientes/{id}` | grava cliente + contatos (tabelas cliente_*) |
| apolice-pessoas | `POST /api/apolices` | grava apólice + vidas + beneficiários + coberturas + parcelas |
| leads / novo-lead | `GET/POST /api/leads`, `PATCH /api/leads/{id}` (etapa) | funil e cadastro |
| chamados / novo-chamado | `GET/POST /api/chamados`, `POST /api/chamados/{id}/movimentos` | chamados/sinistros |
| financeiro | `GET /api/comissoes`, `POST /api/comissoes/baixar` | baixa e rateio |
| cofre | `GET/POST /api/documentos` (upload) | arquivo digital |
| configuracoes | `GET/POST /api/usuarios`, `GET /api/auditoria` | usuários, permissões, log |
| campanhas | `GET/POST /api/campanhas` | marketing (e-mail/WhatsApp) |

## Mapeamento telas ↔ tabelas (SQL em `database/`)
- clientes/cadastro → `clientes`, `cliente_enderecos`, `cliente_telefones`, `cliente_emails`, `cliente_conjuge`, `cliente_cnh`, `audit_log`
- apólice → `apolices`, `apolice_vidas`, `apolice_beneficiarios`, `apolice_coberturas`, `apolice_parcelas`, `apolice_rateio`, `ramos`, `seguradoras`
- leads/campanhas/chamados → `leads`, `lead_interacoes`, `campanhas`, `chamados`, `chamado_movimentos`, `usuarios`, `permissoes`

## Observações
- CEP: `assets/js/app.js` chama ViaCEP direto do navegador (não precisa backend).
- CPF: validação de dígito é local (`CI.cpfValido`). Autofill de nome/nascimento por CPF **não** está implementado (depende de base paga Serpro — decisão futura).
- Toda gravação/alteração/exclusão deve registrar em `audit_log` (rastreabilidade / LGPD).
