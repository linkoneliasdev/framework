<?php
$collaboratorName = htmlspecialchars((string) (currentUser()['name'] ?? 'Colaborador'), ENT_QUOTES, 'UTF-8');
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><h4 class="fw-bold mb-1">Painel operacional</h4><p class="text-muted mb-0">Olá, <?= $collaboratorName ?>. Veja o resumo das suas atividades.</p></div>
    <span class="badge bg-label-info">Colaborador</span>
</div>

<div class="alert alert-info" role="alert">
    <i class="bx bx-info-circle me-1"></i>
    Seu perfil tem acesso às telas operacionais. Configurações administrativas e gerenciamento de usuários ficam restritos ao administrador.
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4"><div class="card"><div class="card-body"><span class="text-muted">Tarefas atribuídas</span><h3 class="mt-2 mb-0">8</h3><small class="text-muted">Exemplo demonstrativo</small></div></div></div>
    <div class="col-sm-6 col-xl-4"><div class="card"><div class="card-body"><span class="text-muted">Concluídas</span><h3 class="mt-2 mb-0">5</h3><small class="text-success">Nesta semana · exemplo</small></div></div></div>
    <div class="col-sm-6 col-xl-4"><div class="card"><div class="card-body"><span class="text-muted">Aguardando revisão</span><h3 class="mt-2 mb-0">3</h3><small class="text-warning">Exemplo demonstrativo</small></div></div></div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">Atividades recentes</h5></div>
    <div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Atividade</th><th>Prazo</th><th>Status</th></tr></thead><tbody>
        <tr><td>Revisar cadastro #1042</td><td>Hoje</td><td><span class="badge bg-label-warning">Pendente</span></td></tr>
        <tr><td>Validar consulta #1038</td><td>Hoje</td><td><span class="badge bg-label-info">Em andamento</span></td></tr>
        <tr><td>Conferir relatório #1029</td><td>Ontem</td><td><span class="badge bg-label-success">Concluída</span></td></tr>
    </tbody></table></div>
    <div class="card-body"><small class="text-muted">Dados fictícios para demonstrar a tela. As permissões também devem ser aplicadas nos endpoints que executam ações.</small></div>
</div>
