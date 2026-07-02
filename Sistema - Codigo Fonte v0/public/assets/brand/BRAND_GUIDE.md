# Guia de Marca — Clube Investvida

**Corretora de Seguros** · Marca da Opção A (símbolo aberto) — evolução modernizada do logotipo original, preservando o DNA: figura humana de braços erguidos, esfera-cabeça e base azul.

> Descritor obrigatório (SUSEP): a assinatura sempre acompanha **"Corretora de Seguros"**. Nome empresarial: *Clube Investvida Corretora de Seguros Ltda*.

## 1. Arquivos
| Arquivo | Uso |
|---|---|
| `logo-horizontal.svg` | Aplicação principal em fundos claros |
| `logo-horizontal-clara.svg` | Aplicação principal em fundos escuros |
| `logo-simbolo.svg` | Símbolo isolado (colorido) |
| `logo-simbolo-claro.svg` | Símbolo isolado (branco / 1 cor) |
| `favicon.svg` · `favicon-32.png` | Favicon do site |
| `apple-touch-icon.png` | Ícone iOS / avatar de redes (180px) |

## 2. Cores (extraídas do vetor original)
| Cor | HEX | RGB | Uso |
|---|---|---|---|
| Âmbar | `#F8A600` | 248,166,0 | Primária quente, braço esquerdo, cabeça |
| Laranja | `#E94D11` | 233,77,17 | Ação / CTA, braço direito |
| Azul | `#005092` | 0,80,146 | Confiança, logotipo, texto de marca |
| Navy | `#062A4A` | 6,42,74 | Fundos escuros, menu lateral, selo |
| Ink | `#1B1918` | 27,25,24 | Texto principal |
| Surface | `#EEF1F5` | 238,241,245 | Fundo do aplicativo |

## 3. Tipografia
- **Manrope** — logotipo, títulos e números (peso 700/800).
- **Inter** — corpo de texto e interface.
- (Alternativa institucional fiel: **Carlito**, presente no material original.)

## 4. Área de proteção e tamanho mínimo
- **Área de proteção:** margem livre ao redor = altura da esfera-cabeça do símbolo. Nada invade essa área.
- **Tamanho mínimo:** símbolo isolado ≥ 24 px; lockup horizontal ≥ 120 px de largura. Abaixo disso, usar só o símbolo.

## 5. Aplicação em fundos
- **Claro:** logotipo colorido, texto azul `#005092`.
- **Escuro:** `logo-horizontal-clara` (texto branco); símbolo mantém as cores ou usa a versão branca em 1 cor.
- **Sobre foto:** usar sempre o selo/favicon (fundo navy) para garantir contraste.

## 6. Usos incorretos
- Não reintroduzir brilho/3D ou sombras no símbolo.
- Não distorcer, inclinar ou alterar proporções.
- Não trocar as cores da marca nem aplicar gradientes fora do âmbar→laranja.
- Não usar o logotipo sem o descritor "Corretora de Seguros".
- Não aplicar o logotipo colorido sobre fundos de baixo contraste.

## 7. Tokens de design (para o sistema)
```
--amber:#F8A600;  --orange:#E94D11;  --blue:#005092;
--navy:#062A4A;   --ink:#1B1918;     --surface:#EEF1F5;
--line:#E2E8F0;
--radius-sm:6px; --radius:8px; --radius-lg:14px; --radius-pill:999px;
--space:8px (base 4px);  --shadow:0 8px 24px rgba(6,42,74,.06);
--ok:#1E7A3D; --warn:#9A6700; --danger:#B3261E;
```
Estados de interface padronizados: **carregando** (skeleton), **vazio** (ícone + mensagem + ação), **erro** (borda/texto danger) e **sucesso** (texto ok). Foco sempre visível (contorno azul), navegação por teclado e contraste mínimo WCAG AA.
