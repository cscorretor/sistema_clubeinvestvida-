# Implantação na Hostinger Business

Este projeto foi preparado para a hospedagem compartilhada da Hostinger: sem acesso root, sem Docker, sem Redis, sem WebSocket e sem worker de fila permanente.

## Estrutura segura

O hPanel não permite trocar livremente o document root do plano compartilhado. Use a estrutura equivalente abaixo:

```text
diretório do domínio/
├── laravel_app/     aplicação completa, .env e vendor
└── public_html/     somente o conteúdo de laravel_app/public
```

Não coloque o projeto Laravel inteiro em `public_html`. O arquivo `.env`, os fontes e os dados da aplicação precisam ficar fora do alcance da web.

O arquivo `deploy/hostinger/public-index.php` já aponta de `public_html` para a pasta privada irmã `laravel_app`.

## 1. Preparar o hPanel

1. Ative o SSL do domínio.
2. Em **Sites → Gerenciar → Configuração PHP**, selecione PHP 8.2.
3. Crie um banco e um usuário em **Bancos de dados MySQL**.
4. Anote host, porta, banco e usuário. A conexão Laravel continua sendo `mysql`, compatível com MariaDB.
5. Ative o acesso SSH. Ele é restrito à conta, o que é suficiente para este procedimento.

Para conferir a versão real do servidor de banco, abra o phpMyAdmin e execute:

```sql
SELECT VERSION();
```

## 2. Enviar a aplicação

Pelo SSH, entre no diretório do domínio e clone o repositório privado:

```bash
git clone https://github.com/cscorretor/sistema_clubeinvestvida-.git laravel_app
cd laravel_app
```

Se o GitHub pedir autenticação, use um token de acesso ou configure uma chave de implantação. Nunca coloque o token dentro do repositório.

## 3. Criar o ambiente de produção

Execute o configurador interativo. Ele solicita as senhas sem mostrá-las, cria o `.env` com permissão restrita e gera a chave da aplicação:

```bash
bash deploy/hostinger/configure-env.sh
```

Não envie o conteúdo do `.env` ao GitHub ou por mensagens.

Na Hostinger, o comando `php` do terminal pode apontar para uma versão diferente da selecionada para o site. Os scripts de configuração e implantação usam e exigem explicitamente `/opt/alt/php82/usr/bin/php`.

Garanta escrita apenas nas pastas exigidas pelo Laravel:

```bash
chmod -R ug+rwX storage bootstrap/cache
```

## 4. Primeira publicação

O script executa migrations, seeders idempotentes, caches de produção e copia somente os arquivos públicos:

```bash
bash deploy/hostinger/deploy.sh
```

O script detecta automaticamente `/opt/alt/php82/usr/bin/php` na Hostinger. Se o PHP 8.2 estiver em outro caminho, execute:

```bash
PHP_BIN=/caminho/retornado/php bash deploy/hostinger/deploy.sh
```

Ao terminar, abra:

- `https://seu-dominio.com.br/up` — deve mostrar a aplicação saudável;
- `https://seu-dominio.com.br/login` — deve mostrar o login;
- `https://seu-dominio.com.br/.env` — deve retornar 404 ou acesso negado.

Depois de confirmar o acesso administrativo, remova `SEED_ADMIN_PASSWORD` do `.env` e troque a senha provisória. O seeder não redefine a senha de um administrador que já existe.

## 5. Cron do scheduler

Em **Sites → Gerenciar → Avançado → Cron Jobs**, crie uma tarefa a cada minuto. Use o caminho retornado por `which php` e o caminho absoluto da aplicação:

```bash
cd /home/USUARIO/domains/DOMINIO/laravel_app && /caminho/do/php artisan schedule:run
```

O cron da Hostinger opera em UTC, enquanto a aplicação usa `America/Sao_Paulo`. Os horários definidos pelo Laravel serão interpretados no fuso da aplicação.

Não crie `queue:work`, Supervisor ou qualquer daemon. O projeto usa `QUEUE_CONNECTION=sync`, adequado ao plano Business compartilhado.

## 6. Atualizações

Faça backup do banco pelo hPanel antes de migrations importantes. Depois:

```bash
cd /home/USUARIO/domains/DOMINIO/laravel_app
git pull --ff-only origin main
bash deploy/hostinger/deploy.sh
```

O script ativa brevemente o modo de manutenção, atualiza o banco, recria os caches e reabre a aplicação mesmo se ocorrer erro após a manutenção.

## Checklist

- `.env` existe apenas em `laravel_app`;
- `APP_ENV=production` e `APP_DEBUG=false`;
- `SESSION_ENCRYPT=true` e `SESSION_SECURE_COOKIE=true`;
- `QUEUE_CONNECTION=sync` e `CACHE_STORE=file`;
- `storage` e `bootstrap/cache` têm permissão de escrita;
- `public_html/index.php` é o modelo de `deploy/hostinger/public-index.php`;
- cron executa `schedule:run` a cada minuto;
- PDFs, `.bak` e backups do Segflex não foram enviados ao GitHub.
