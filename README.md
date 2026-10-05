# MultiLojas

Marketplace/delivery multi-loja em PHP: cada loja atende em `<loja>.<domínio>`, com painel do lojista
(`/painel`), administração (`/administracao`) e área de afiliados (`/afiliado`).

## Subir com Docker Compose

```bash
cp .env.example .env      # preencha as chaves
docker compose up -d --build
```

- Site: http://localhost:8080 · Login: http://localhost:8080/login/
- Loja de demonstração: http://demo1.localhost:8080
- O banco é criado a partir de `database/schema.sql` na primeira subida.

## Estrutura

| Pasta | Conteúdo |
|---|---|
| `app/` | O sistema (raiz do site). Cada pasta é uma URL. |
| `app/_core/_includes/` | `config.php` (lê o `.env`) e `functions/` (regras de negócio). |
| `storage/uploads/` | Imagens enviadas. Fora do git; montada em `app/_core/_uploads`. |
| `database/schema.sql` | Estrutura e dados iniciais do banco. |
| `docker/` | Imagem PHP 7.4 + Apache e configurações. |
| `docs/INSTALL.md` | Deploy em VPS, cron e checklist de produção. |

## Observações técnicas

- **PHP 7.4.** Não há mais dependência do ionCube; a subida para PHP 8 ainda precisa ser validada.
- **MySQL sem modo estrito** (`sql_mode=""`, já configurado no Compose): várias tabelas não têm valor
  padrão nas colunas e os cadastros falham em modo estrito.
- As páginas escapam os dados com `mysqli_real_escape_string` antes de chamar as funções de
  `functions/data.php` e `functions/user.php`; as funções não escapam de novo.
