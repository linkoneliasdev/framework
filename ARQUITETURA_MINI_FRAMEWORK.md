# Guia de arquitetura do mini framework PHP

> Documento de referência para desenvolvedores e IAs que vão criar ou modificar funcionalidades neste repositório.
>
> **Regra principal:** antes de criar arquivos, examine a estrutura existente e siga este documento. Faça alterações pequenas, reutilize os includes/helpers existentes e não transforme o projeto em um framework diferente.

## 1. Visão geral

O projeto é um mini framework PHP tradicional, sem SPA e sem um framework MVC externo. O roteamento das páginas web passa pelo `index.php`; as páginas são arquivos PHP incluídos dentro do layout correspondente ao perfil autenticado. A API fica em `api/` e deve responder com JSON, sem renderizar o layout HTML.

Estrutura principal:

```text
/
├── index.php                       # Front controller/roteador das páginas protegidas
├── .htaccess                       # URLs amigáveis e bloqueios de acesso direto
├── .env                            # Configuração local; não versionar
├── composer.json
├── api/                            # Endpoints HTTP/JSON, independentes do layout
│   └── auth/
│       ├── auth_bridge.php
│       └── logout.php
├── app/
│   ├── includes/                   # Infraestrutura compartilhada
│   │   ├── config.php
│   │   ├── db.php                  # Conexão PDO
│   │   ├── session.php
│   │   ├── auth.php                # Usuário autenticado e autorização
│   │   ├── layout.php              # Papel/layout e helpers de navegação
│   │   └── helpers/
│   │       └── assets.php          # Helper asset()
│   └── layout/
│       ├── admin/
│       ├── collaborator/
│       └── user/                   # header, sidebar, navbar, footer, scripts
├── pages/                          # Conteúdo HTML/PHP das páginas web
│   ├── 403.php
│   ├── 404.php
│   ├── auth/                       # Telas públicas de autenticação
│   ├── admin/
│   ├── collaborator/
│   └── user/
└── public/
    └── assets/
        ├── css/
        ├── js/
        │   └── pages/              # JavaScript específico de telas
        ├── img/
        └── vendor/                 # Bibliotecas de terceiros já utilizadas
```

A árvore acima destaca as áreas relevantes; não representa todos os arquivos de assets do projeto.

## 2. Roteamento de páginas web

### Como uma URL chega à página

O arquivo `.htaccess` aplica estas regras gerais:

1. Bloqueia o acesso HTTP direto a diretórios internos como `app/` e `pages/`.
2. Mapeia as rotas públicas de autenticação `/login`, `/registrar` e `/forgot` para seus arquivos de tela.
3. Deixa as rotas `/api/` serem atendidas diretamente pelos endpoints, sem passar pelo roteador de páginas.
4. Serve arquivos públicos existentes, como CSS, JavaScript e imagens.
5. Encaminha as demais URLs para `index.php?pagina=...`.

Exemplos de URLs de páginas:

| URL | Arquivo de conteúdo |
|---|---|
| `/dashboard` | `pages/{perfil}/dashboard.php` |
| `/consultas` | `pages/{perfil}/consultas.php` |
| `/tarefas` | `pages/collaborator/tarefas.php` |
| `/admin/consultas` | `pages/admin/consultas.php`, somente quando o perfil atual é `admin` |
| `/login` | `pages/auth/login.php` (rota pública definida no `.htaccess`) |

O valor `{perfil}` é selecionado a partir do papel do usuário autenticado: `user`, `collaborator` ou `admin`.

### O que o `index.php` faz

O fluxo atual é, em essência:

1. Inicializa a sessão por `app/includes/session.php`.
2. Carrega configuração, PDO, helper de assets, autenticação e funções de layout.
3. Executa `requireLogin()` nas páginas protegidas.
4. Sincroniza o papel do usuário com o banco e define o layout apropriado.
5. Lê e sanitiza `$_GET['pagina']`; quando ausente/vazio, usa `dashboard`.
6. Se a URL contém um prefixo de perfil, verifica se corresponde ao perfil autenticado. Um perfil incompatível recebe resposta 403.
7. Resolve o arquivo somente dentro de `pages/{perfil}/`, usando caminhos reais para impedir traversal.
8. Inclui `header.php`, `sidebar.php` e `navbar.php` do layout do perfil.
9. Inclui o arquivo da página dentro da área de conteúdo ou exibe `pages/403.php`/`pages/404.php`.
10. Inclui `footer.php` e `scripts.php` do layout.

**Não crie um segundo roteador para páginas web.** Para uma nova tela protegida, crie o arquivo na pasta de perfil correta e acesse-o pela URL amigável correspondente.

### Regras para páginas

- Usuário comum: `pages/user/nome.php`.
- Administrador: `pages/admin/nome.php`.
- Colaborador: `pages/collaborator/nome.php`.
- Telas públicas de autenticação seguem o padrão já existente em `pages/auth/` e as regras explícitas do `.htaccess`. Não presuma que criar um arquivo nessa pasta o torna automaticamente público.
- As páginas de perfil são fragmentos de conteúdo incluídos pelo roteador: **não duplique nelas** `<!doctype html>`, `<html>`, `<head>`, `<body>` nem o layout completo.
- Para dados do usuário atual, reutilize as funções já disponíveis, como `currentUser()`; não leia ou confie em um ID de usuário arbitrário enviado pelo navegador.
- A criação da página não a adiciona automaticamente ao menu. Se necessário, atualize o menu em `app/layout/{perfil}/sidebar.php` e use o helper `isActive()` quando adequado.

## 3. Layout HTML e includes compartilhados

Cada perfil possui arquivos de layout próprios em `app/layout/{perfil}/`:

- `header.php`: início do documento HTML, metadados e CSS compartilhado.
- `sidebar.php`: navegação lateral.
- `navbar.php`: barra superior.
- `footer.php`: rodapé/fechamento de partes do layout.
- `scripts.php`: bibliotecas e scripts compartilhados, normalmente no final do documento.

O roteador seleciona o layout com base no perfil autenticado. Se uma funcionalidade for exclusiva de um perfil, mantenha a página e os itens de navegação na pasta desse perfil.

**Não inclua o layout completo dentro de uma página.** O HTML da tela deve conter apenas o conteúdo que será renderizado na área central.

## 4. CSS, JavaScript, imagens e bibliotecas

Os arquivos estáticos públicos ficam em `public/assets/`:

- CSS próprio: `public/assets/css/`.
- JavaScript próprio: `public/assets/js/`.
- JavaScript específico de tela: `public/assets/js/pages/` (há exemplos existentes, como as telas de autenticação).
- Imagens: `public/assets/img/`.
- Bibliotecas de terceiros já instaladas: `public/assets/vendor/`.

### Helper `asset()`

O helper é definido em `app/includes/helpers/assets.php` e é carregado pelo `index.php`. Ele gera um caminho público e acrescenta `?v=` com a data de modificação do arquivo quando o arquivo existe, ajudando a invalidar cache após alterações.

Exemplo em PHP/HTML:

```php
<link rel="stylesheet" href="<?= asset('assets/css/minha-tela.css') ?>">
<script src="<?= asset('assets/js/pages/minha-tela.js') ?>"></script>
<img src="<?= asset('assets/img/exemplo.png') ?>" alt="Exemplo">
```

### Regra importante sobre carregamento de JS

Os scripts globais são carregados pelo `app/layout/{perfil}/scripts.php`. **Os arquivos dentro de `public/assets/js/pages/` não são carregados automaticamente apenas por existirem.** Para uma nova tela, inclua o script específico usando `asset()` no ponto adequado do conteúdo da página ou conecte-o ao mecanismo explícito de scripts do layout, caso esse mecanismo seja implementado no projeto. Não coloque toda a lógica de uma tela em `main.js` se ela for específica daquela tela.

Use os scripts e bibliotecas já disponíveis no projeto antes de adicionar dependências. Não copie bibliotecas para outra pasta nem carregue versões duplicadas de jQuery/Bootstrap.

## 5. API: estrutura dos endpoints

### Separação entre páginas e API

- `pages/`: entrega interface HTML.
- `api/`: executa operações HTTP e retorna JSON.
- A API não deve incluir `header.php`, `sidebar.php`, `navbar.php`, `footer.php` ou `scripts.php`.
- Uma chamada AJAX/fetch/jQuery deve apontar para a URL do endpoint em `/api/...`, não para uma página web.
- O `.htaccess` deixa as rotas `/api/` passarem diretamente para os arquivos correspondentes. Portanto, não conte com o roteador `index.php?pagina=...` para executar endpoints.
- Os endpoints existentes em `api/auth/` são referências para entender o código já implementado. Antes de alterar autenticação, leia esses arquivos e seus includes.

### Padrão de organização para novos módulos de API

Para novos módulos, adote a organização abaixo, salvo quando o módulo já tiver uma convenção própria que precise ser preservada:

```text
api/
└── exemplo/
    ├── index.php                   # Entrada HTTP do módulo e roteador de actions
    └── actions/
        ├── listar.php              # Uma operação por arquivo
        ├── obter.php
        ├── criar.php
        ├── atualizar.php
        └── excluir.php
```

O endereço base será `/api/exemplo/` ou `/api/exemplo/index.php`, dependendo da URL usada na chamada. Como a regra do `.htaccess` não remove automaticamente `index.php` nem cria um roteador de actions, a chamada deve enviar a action explicitamente, por exemplo como parâmetro `action`.

### Responsabilidade de `api/exemplo/index.php`

O arquivo de entrada deve:

1. Definir o tipo de conteúdo como JSON e, quando aplicável, o status HTTP.
2. Carregar apenas os includes de infraestrutura necessários, usando caminhos absolutos baseados em `__DIR__` ou `dirname(__DIR__, ...)`.
3. Validar o método HTTP e autenticar/autorizAR a operação conforme o tipo de endpoint.
4. Ler e validar o parâmetro `action`.
5. Usar `switch ($action)` para encaminhar a operação ao arquivo específico em `actions/`.
6. Responder de forma previsível para action ausente/desconhecida e para erros, sem vazar stack traces, credenciais ou detalhes internos.
7. Garantir que a resposta seja JSON consistente e que nenhum HTML/aviso PHP seja impresso antes do JSON.

Exemplo estrutural (modelo para novas APIs; adapte os includes e as funções ao código real do projeto):

```php
<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__, 2) . '/app/includes/session.php';
startAppSession();

require_once dirname(__DIR__, 2) . '/app/includes/config.php';
require_once dirname(__DIR__, 2) . '/app/includes/db.php';
require_once dirname(__DIR__, 2) . '/app/includes/auth.php';

$action = (string) ($_GET['action'] ?? $_POST['action'] ?? '');

switch ($action) {
    case 'listar':
        require __DIR__ . '/actions/listar.php';
        break;

    case 'obter':
        require __DIR__ . '/actions/obter.php';
        break;

    case 'criar':
        require __DIR__ . '/actions/criar.php';
        break;

    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Action inválida ou não informada.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
}
```

Esse trecho é apenas um esqueleto. A IA deve conferir as assinaturas reais dos includes e as funções disponíveis antes de usá-lo. Não copie cegamente a inicialização de sessão/autenticação: endpoints públicos, endpoints autenticados por sessão e endpoints autenticados por token podem exigir fluxos diferentes.

### Responsabilidade de cada arquivo em `actions/`

O arquivo da action contém a lógica específica daquela operação. Por exemplo, `api/exemplo/actions/listar.php` deve validar os filtros de listagem, executar a consulta necessária usando PDO/prepared statements e retornar o resultado no formato JSON esperado.

- Não declare o mesmo `switch` em todos os arquivos de action.
- Não repita conexão PDO ou carregamento de configuração se isso já foi feito pelo arquivo de entrada.
- Não misture HTML de interface com resposta JSON.
- Não confie em IDs, papéis, permissões, valores monetários ou escopos enviados pelo cliente sem validação.
- Use parâmetros preparados para valores SQL. Nomes de colunas e ordenações dinâmicas devem ser escolhidos por allowlist, nunca concatenados livremente a partir da entrada.
- A autorização deve ser feita no servidor em cada endpoint. Ocultar um botão no frontend não protege a operação.
- Para recursos pertencentes ao usuário, determine o usuário pela sessão/token validado e restrinja a consulta no SQL com esse identificador; não permita que o cliente escolha livremente o proprietário dos dados.
- Defina códigos HTTP apropriados (por exemplo, 400 para entrada inválida, 401 para falta de autenticação, 403 para falta de permissão, 404 para recurso inexistente e 500 para erro interno).
- Não exiba mensagens internas do banco ou stack traces ao cliente. Registre detalhes técnicos no log apropriado.

### Exemplo de chamada de API a partir do JavaScript

Exemplo ilustrativo usando jQuery, que já existe no projeto:

```javascript
$.ajax({
    url: '/api/exemplo/index.php',
    method: 'GET',
    dataType: 'json',
    data: {
        action: 'listar'
    },
    success: function (resposta) {
        if (resposta.success) {
            console.log(resposta.data);
            return;
        }

        console.error(resposta.message || 'Não foi possível concluir a operação.');
    },
    error: function (xhr) {
        console.error('Falha na chamada da API:', xhr.status);
    }
});
```

Para operações de escrita, escolha o método HTTP correto e valide o corpo recebido no servidor. Não use GET para criar, alterar ou excluir dados. Se o endpoint usar sessão/cookies para autenticação, considere a proteção CSRF apropriada para as operações que alteram estado.

## 6. Banco de dados e configuração

- A conexão PDO central fica em `app/includes/db.php`; consulte esse arquivo antes de criar outra conexão.
- A configuração central fica em `app/includes/config.php`, com variáveis de ambiente definidas conforme a implementação existente.
- A autenticação e as funções de usuário ficam em `app/includes/auth.php`; a inicialização de sessão fica em `app/includes/session.php`.
- Reutilize a conexão existente (normalmente `$pdo`) e prepared statements.
- Não coloque senhas, tokens, chaves privadas ou credenciais no código versionado, nos arquivos de frontend ou neste documento.
- Não altere schema de banco por suposição. Quando a tarefa exigir mudança de banco, inspecione o schema atual e entregue SQL compatível, explicando a ordem de execução.

## 7. Fluxo recomendado ao implementar uma funcionalidade

Exemplo: criar uma tela de consultas com dados carregados pela API.

1. **Página:** criar `pages/user/consultas.php` para o HTML do conteúdo.
2. **JavaScript da tela:** criar `public/assets/js/pages/user/consultas.js` e incluí-lo explicitamente na página/layout com `asset()`.
3. **API:** criar `api/consultas/index.php` com o `switch` de actions e os arquivos de operação em `api/consultas/actions/`.
4. **Banco:** reutilizar `$pdo`, preparar consultas e limitar dados ao usuário autenticado.
5. **Navegação:** adicionar o link em `app/layout/user/sidebar.php`, se a tela precisar aparecer no menu.
6. **Segurança:** verificar autenticação, autorização, validação de entradas, CSRF quando aplicável e respostas de erro.
7. **Verificação:** conferir caminhos de includes/assets, sintaxe PHP, JSON retornado, status HTTP e comportamento para usuário sem permissão.
8. **Não duplicar:** procurar antes por helpers, endpoints, funções ou componentes que já resolvam parte do trabalho.

Os nomes acima são exemplos de convenção para novas funcionalidades; verifique se já existe um módulo equivalente antes de criá-lo.

## 8. Regras obrigatórias para a IA que modifica este repositório

Quando receber uma tarefa de implementação:

1. Leia este documento e inspecione os arquivos diretamente relacionados à tarefa antes de editar.
2. Preserve a arquitetura e as convenções existentes; não introduza Laravel, Symfony, outro framework, um router novo ou uma camada de abstração desnecessária.
3. Crie cada arquivo no local apropriado descrito aqui. Não coloque HTML de páginas em `api/`, nem lógica de endpoint em `pages/`.
4. Para páginas protegidas, use o roteador atual e a pasta do perfil correto.
5. Para APIs novas, siga `api/{modulo}/index.php` + `api/{modulo}/actions/{action}.php`, com `switch` na entrada, exceto quando for necessário preservar uma convenção existente.
6. Antes de escrever includes, confirme os caminhos e as funções reais lendo os arquivos referenciados.
7. Reutilize `$pdo`, autenticação, sessão, helpers de assets e componentes de layout já existentes.
8. Não presuma que um arquivo JavaScript específico será carregado automaticamente. Faça a inclusão explícita e verifique o caminho.
9. Não altere arquivos não relacionados à tarefa e não remova funcionalidades existentes.
10. Não crie credenciais de exemplo que pareçam reais nem exponha segredos em respostas, logs ou arquivos versionados.
11. Ao terminar, informe quais arquivos foram criados/alterados, o papel de cada um, as rotas envolvidas, eventuais SQLs necessários e os testes executados ou pendentes.

## 9. Arquivos de referência para consultar antes de alterar a arquitetura

- `index.php`: roteamento das páginas protegidas e inclusão do layout.
- `.htaccess`: URLs amigáveis, acesso direto a pastas e passagem direta de `/api/`.
- `app/includes/db.php`: conexão PDO.
- `app/includes/auth.php`: autenticação e papel do usuário.
- `app/includes/session.php`: inicialização da sessão.
- `app/includes/layout.php`: seleção de layout e helper `isActive()`.
- `app/includes/helpers/assets.php`: helper `asset()`.
- `app/layout/{perfil}/`: HTML compartilhado e scripts globais.
- `pages/{perfil}/`: conteúdo das páginas protegidas.
- `pages/auth/`: telas públicas de autenticação existentes.
- `api/auth/`: endpoints de autenticação já implementados.
- `public/assets/js/pages/`: exemplos de organização de JavaScript específico de tela.

Se este documento e o código atual divergirem, inspecione o código real e evite quebrar comportamentos existentes. Atualize este documento quando a arquitetura for alterada intencionalmente.
