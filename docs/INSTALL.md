# Instalação em VPS

## 1. Pré-requisitos

- Docker e Docker Compose.
- DNS: `seudominio.com.br`, `*.seudominio.com.br` (lojas e cidades) e `conheca.seudominio.com.br`
  apontando para a VPS.
- Proxy reverso com TLS (Caddy, Traefik ou Nginx) encaminhando para a porta 8080 e enviando
  `X-Forwarded-Proto: https`. Certificado curinga para `*.seudominio.com.br`.

## 2. Configuração

```bash
cp .env.example .env
```

| Variável | O que é |
|---|---|
| `APP_DOMAIN` | Domínio principal, sem protocolo. |
| `APP_HTTPS` | `1` em produção. |
| `DB_*` | Banco. Troque as senhas. |
| `SMTP_*` | Conta de envio. O servidor usado é `mail.<domínio de SMTP_USER>`, porta 587. |
| `RECAPTCHA_*` | Chaves do Google reCAPTCHA. |
| `EXTERNAL_TOKEN` | Protege `cron.php` e os callbacks de pagamento. Gere um valor novo e longo. |
| `MP_*` | Credenciais do Mercado Pago que recebem a cobrança dos planos. |
| `VAPID_*` | Par de chaves das notificações push. A pública também fica em `app/main.js` (linha 1). |

## 3. Subir

```bash
docker compose up -d --build
```

Envie as imagens para `storage/uploads/` (por exemplo com `rsync`). Essa pasta e o volume `db_data`
são os dois lugares com dados: inclua ambos no backup.

## 4. Tarefas agendadas (crontab da VPS)

```cron
*/15 * * * * curl -fsS "https://seudominio.com.br/cron.php?acao=sync&token=EXTERNAL_TOKEN" >/dev/null
* * * * *    curl -fsS "https://seudominio.com.br/cron.php?acao=agendamentos&token=EXTERNAL_TOKEN" >/dev/null
```

## 5. Antes de abrir ao público

- [ ] Trocar a senha do usuário administrador inicial (`admin@admin.com`) em `/administracao/configuracoes`.
- [ ] `EXTERNAL_TOKEN`, senhas do banco e par VAPID novos (os que vieram com o sistema são públicos).
- [ ] Credenciais próprias de Mercado Pago, SMTP e reCAPTCHA.
- [ ] Testar um pagamento de plano em sandbox (`MP_SANDBOX=1`).
- [ ] Remover as lojas e os pedidos de demonstração do banco, se for começar vazio.

## Pendências conhecidas

- `app/conheca2/` (landing page alternativa) e `app/app/estabelecimento/pagseguro/config.php` ainda
  têm o domínio do fornecedor original fixo no código.
- `_core/_ajax/delete_image.php` autoriza qualquer usuário logado a apagar mídia de qualquer loja.
- Várias páginas montam SQL com `$_GET` sem escape (ex.: `index.php`, `cron.php`).
- A sincronização de pagamentos chama o Mercado Pago sem verificar o certificado TLS
  (`consulta_pagamento` em `functions/user.php` e `mp.php`).
- `html_mail` usa `SMTPDebug = 3`, que escreve o diálogo SMTP na saída da página.
