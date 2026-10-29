<?php
declare(strict_types=1);

$user = currentUser();
$userId = (int) ($user['id'] ?? 0);

if ($userId <= 0) {
    echo '<div class="alert alert-danger">Sua sessão expirou. Entre novamente.</div>';
    return;
}

if (empty($_SESSION['csrf_projects'])) {
    $_SESSION['csrf_projects'] = bin2hex(random_bytes(32));
}
$csrfProjects = (string) $_SESSION['csrf_projects'];

$stmt = $pdo->prepare(
    'SELECT id, name, domain, public_key, status, created_at, updated_at
     FROM projects
     WHERE user_id = :user_id
     ORDER BY created_at DESC, id DESC'
);
$stmt->execute([':user_id' => $userId]);
$projects = $stmt->fetchAll();

$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Projetos</h4>
        <p class="text-muted mb-0">Cadastre os sites que você deseja acompanhar.</p>
    </div>
    <button type="button" class="btn btn-primary" id="btnNovoProjeto">
        <i class="bx bx-plus me-1"></i> Novo projeto
    </button>
</div>

<div class="alert alert-info d-flex align-items-start gap-2" role="alert">
    <i class="bx bx-info-circle fs-4"></i>
    <div>
        <strong>Como funciona?</strong>
        <div>Por enquanto, esta área cadastra o site e gera uma chave pública identificadora. A instalação do rastreador, criação de links e coleta de visitas serão etapas posteriores.</div>
    </div>
</div>

<div id="mensagemProjetos" class="alert d-none" role="alert"></div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">Meus sites</h5>
        <span class="badge bg-label-primary"><?= count($projects) ?> projeto(s)</span>
    </div>
    <?php if (!$projects): ?>
        <div class="card-body text-center py-5">
            <div class="mb-3"><i class="bx bx-globe text-primary" style="font-size: 3rem;"></i></div>
            <h5>Nenhum projeto cadastrado</h5>
            <p class="text-muted mb-3">Adicione seu primeiro site para começar a configurar o rastreamento.</p>
            <button type="button" class="btn btn-primary" id="btnPrimeiroProjeto">
                <i class="bx bx-plus me-1"></i> Criar primeiro projeto
            </button>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Projeto / domínio</th>
                        <th>Chave pública</th>
                        <th>Status</th>
                        <th>Criado em</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= $escape($project['name']) ?></div>
                            <a href="https://<?= $escape($project['domain']) ?>" target="_blank" rel="noopener noreferrer" class="small text-muted">
                                <?= $escape($project['domain']) ?> <i class="bx bx-link-external"></i>
                            </a>
                        </td>
                        <td>
                            <code class="small"><?= $escape($project['public_key']) ?></code>
                            <button type="button" class="btn btn-sm btn-icon btn-text-secondary ms-1 btnCopiarChave"
                                    data-key="<?= $escape($project['public_key']) ?>" title="Copiar chave">
                                <i class="bx bx-copy"></i>
                            </button>
                        </td>
                        <td>
                            <?php if ($project['status'] === 'active'): ?>
                                <span class="badge bg-label-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge bg-label-secondary">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $escape(date('d/m/Y', strtotime((string) $project['created_at']))) ?></td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <button type="button" class="btn btn-sm btn-icon btn-text-primary btnEditarProjeto"
                                        title="Editar projeto"
                                        data-id="<?= (int) $project['id'] ?>"
                                        data-name="<?= $escape($project['name']) ?>"
                                        data-domain="<?= $escape($project['domain']) ?>">
                                    <i class="bx bx-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary btnAlternarStatus"
                                        data-id="<?= (int) $project['id'] ?>"
                                        data-status="<?= $escape($project['status'] === 'active' ? 'inactive' : 'active') ?>">
                                    <?= $project['status'] === 'active' ? 'Desativar' : 'Ativar' ?>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">
            <small class="text-muted"><i class="bx bx-shield-quarter me-1"></i> Seus projetos são exibidos e alterados apenas dentro da sua conta.</small>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalProjeto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formProjeto">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalProjeto">Novo projeto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="projetoId" value="">
                    <div class="mb-3">
                        <label for="projetoNome" class="form-label">Nome do projeto</label>
                        <input type="text" class="form-control" id="projetoNome" maxlength="120" required placeholder="Ex.: Loja principal">
                        <div class="form-text">Um nome fácil de reconhecer no painel.</div>
                    </div>
                    <div class="mb-3">
                        <label for="projetoDominio" class="form-label">Domínio do site</label>
                        <input type="text" class="form-control" id="projetoDominio" maxlength="253" required placeholder="exemplo.com.br">
                        <div class="form-text">Informe somente o domínio, sem caminho, porta ou parâmetros.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSalvarProjeto">
                        <span class="spinner-border spinner-border-sm me-1 d-none" id="spinnerProjeto" aria-hidden="true"></span>
                        <span id="textoBtnSalvarProjeto">Salvar projeto</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var csrfToken = <?= json_encode($csrfProjects) ?>;
    var modalElement = document.getElementById('modalProjeto');
    var modalProjeto = new bootstrap.Modal(modalElement);
    var form = document.getElementById('formProjeto');
    var mensagem = document.getElementById('mensagemProjetos');
    var btnSalvar = document.getElementById('btnSalvarProjeto');
    var textoBtnSalvar = document.getElementById('textoBtnSalvarProjeto');
    var spinner = document.getElementById('spinnerProjeto');

    function mostrarMensagem(texto, tipo) {
        mensagem.textContent = texto;
        mensagem.className = 'alert alert-' + tipo;
        mensagem.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function abrirNovoProjeto() {
        form.reset();
        document.getElementById('projetoId').value = '';
        document.getElementById('tituloModalProjeto').textContent = 'Novo projeto';
        textoBtnSalvar.textContent = 'Salvar projeto';
        modalProjeto.show();
    }

    document.getElementById('btnNovoProjeto').addEventListener('click', abrirNovoProjeto);
    var btnPrimeiro = document.getElementById('btnPrimeiroProjeto');
    if (btnPrimeiro) {
        btnPrimeiro.addEventListener('click', abrirNovoProjeto);
    }

    async function enviar(payload) {
        var response = await fetch('/api/projects.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        var result = await response.json().catch(function () {
            return { success: false, message: 'Resposta inválida do servidor.' };
        });

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Não foi possível concluir a operação.');
        }

        return result;
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        var id = document.getElementById('projetoId').value;
        var payload = {
            action: id ? 'update' : 'create',
            name: document.getElementById('projetoNome').value.trim(),
            domain: document.getElementById('projetoDominio').value.trim()
        };

        if (id) {
            payload.id = Number(id);
        }

        btnSalvar.disabled = true;
        spinner.classList.remove('d-none');

        try {
            var result = await enviar(payload);
            modalProjeto.hide();
            mostrarMensagem(result.message, 'success');
            window.setTimeout(function () { window.location.reload(); }, 600);
        } catch (error) {
            mostrarMensagem(error.message, 'danger');
        } finally {
            btnSalvar.disabled = false;
            spinner.classList.add('d-none');
        }
    });

    document.querySelectorAll('.btnEditarProjeto').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('projetoId').value = button.dataset.id;
            document.getElementById('projetoNome').value = button.dataset.name;
            document.getElementById('projetoDominio').value = button.dataset.domain;
            document.getElementById('tituloModalProjeto').textContent = 'Editar projeto';
            textoBtnSalvar.textContent = 'Salvar alterações';
            modalProjeto.show();
        });
    });

    document.querySelectorAll('.btnAlternarStatus').forEach(function (button) {
        button.addEventListener('click', async function () {
            var status = button.dataset.status;
            var confirmacao = status === 'inactive'
                ? 'Deseja desativar este projeto?'
                : 'Deseja reativar este projeto?';

            if (!window.confirm(confirmacao)) {
                return;
            }

            button.disabled = true;
            try {
                var result = await enviar({
                    action: 'toggle',
                    id: Number(button.dataset.id),
                    status: status
                });
                mostrarMensagem(result.message, 'success');
                window.setTimeout(function () { window.location.reload(); }, 400);
            } catch (error) {
                mostrarMensagem(error.message, 'danger');
                button.disabled = false;
            }
        });
    });

    document.querySelectorAll('.btnCopiarChave').forEach(function (button) {
        button.addEventListener('click', async function () {
            try {
                await navigator.clipboard.writeText(button.dataset.key);
                mostrarMensagem('Chave pública copiada.', 'success');
            } catch (error) {
                mostrarMensagem('Não foi possível copiar automaticamente. Selecione e copie a chave.', 'warning');
            }
        });
    });
});
</script>
