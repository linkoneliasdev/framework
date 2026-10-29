# Perfil de colaborador

Esta alteração mantém a estrutura atual do projeto e adiciona o papel `collaborator`.

## Papéis e permissões

- `admin`: painel administrativo e páginas administrativas.
- `collaborator`: painel operacional e tarefas do colaborador; não acessa páginas de admin nem de usuário.
- `user`: painel e consultas do próprio usuário.

O roteador seleciona o layout e a pasta `pages/{papel}` com base no papel carregado do banco. O papel é sincronizado a cada requisição autenticada, para que uma mudança no banco tenha efeito na próxima requisição.

## Banco de dados

O campo `users.role` precisa aceitar o valor `collaborator`. Confira primeiro o tipo atual:

```sql
SHOW COLUMNS FROM users LIKE 'role';
```

Se a coluna atual for um ENUM que contém somente `user` e `admin`, ajuste o ENUM para incluir o novo valor preservando os valores e atributos atuais. Exemplo para uma coluna ENUM simples:

```sql
ALTER TABLE users
MODIFY role ENUM('user', 'admin', 'collaborator') NOT NULL DEFAULT 'user';
```

Não execute o exemplo sem conferir a definição atual da coluna; se ela for VARCHAR ou tiver outros valores, adapte a alteração para preservar o tipo e os valores existentes.

Para promover um usuário a colaborador, altere o papel manualmente usando o ID correto:

```sql
UPDATE users SET role = 'collaborator' WHERE id = 123;
```

Substitua `123` pelo ID pretendido. Não disponibilize essa operação diretamente ao usuário final.

## Telas adicionadas

- `pages/admin/dashboard.php`: resumo administrativo demonstrativo.
- `pages/user/dashboard.php`: resumo e consultas demonstrativas do usuário.
- `pages/collaborator/dashboard.php`: resumo operacional demonstrativo.
- `pages/collaborator/tarefas.php`: lista de tarefas demonstrativas.
- `app/layout/collaborator/`: header, sidebar, navbar, footer e scripts do colaborador.

Os números e registros nas telas são fictícios; conecte-os ao banco ao implementar os módulos reais.

## Helpers e sessão

- `app/includes/helpers/assets.php`: helper `asset()` separado.
- `app/includes/functions.php`: permanece como compatibilidade para código antigo e inclui o helper separado.
- `app/includes/session.php`: inicialização central da sessão com cookie HttpOnly, SameSite=Lax, strict mode e Secure quando a requisição é HTTPS.

## Observações de segurança

- O bloqueio de acesso direto a `pages/` depende do Apache estar com `mod_rewrite` e `AllowOverride` configurados para ler este `.htaccess`. Teste em seu ambiente antes de publicar.
- A autorização precisa existir também em cada endpoint/API que altera ou retorna dados; esconder links no menu não é controle de acesso.
- Para consultas pertencentes a usuários, filtre pelo ID obtido da sessão no SQL, e não por um ID fornecido livremente pelo navegador.
- Configure `APP_DEBUG=false` em produção e mantenha o arquivo JSON de credenciais Firebase fora da pasta pública.
