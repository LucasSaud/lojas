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

- `administracao/estabelecimentos/editar/index.php` chama `edit_estabelecimento()` com uma lista de
  argumentos diferente da assinatura da função (sem `$accesstoken` e com campos de PagSeguro). A edição
  de loja pelo administrador grava valores nas colunas erradas; a edição pelo painel da loja
  (`painel/configuracoes`) está correta.
- Os comprovantes são gravados no banco sem escape em `mercadopago_process.php` e `getnet_process.php`:
  um apóstrofo no nome do produto ou da loja faz essa gravação falhar.
- Integração com o Mercado Pago validada só contra respostas simuladas; falta teste em sandbox.
- Pontos de fidelidade: `new_pedido` sempre registra zero pontos (assim era no sistema original).
- Dentro do contêiner, a sincronização disparada no login do lojista chama `APP_DOMAIN` por HTTP;
  em desenvolvimento (`localhost:8080`) essa chamada falha sem consequência. Em produção funciona.
