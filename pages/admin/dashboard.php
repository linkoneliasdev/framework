<?php
$adminName = htmlspecialchars((string) (currentUser()['name'] ?? 'Administrador'), ENT_QUOTES, 'UTF-8');
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Painel administrativo</h4>
        <p class="text-muted mb-0">Olá, <?= $adminName ?>. Aqui está uma visão geral do sistema.</p>
    </div>
    <span class="badge bg-label-danger">Administrador</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3"><div class="card"><div class="card-body"><span class="text-muted">Usuários</span><h3 class="mb-0 mt-2">128</h3><small class="text-success">+8 este mês</small></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card"><div class="card-body"><span class="text-muted">Colaboradores</span><h3 class="mb-0 mt-2">12</h3><small class="text-muted">Acesso operacional</small></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card"><div class="card-body"><span class="text-muted">Consultas hoje</span><h3 class="mb-0 mt-2">342</h3><small class="text-success">Operação normal</small></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card"><div class="card-body"><span class="text-muted">Pendências</span><h3 class="mb-0 mt-2">7</h3><small class="text-warning">Precisam de atenção</small></div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">Atividade recente</h5><span class="badge bg-label-primary">Exemplo visual</span></div>
    <div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Evento</th><th>Responsável</th><th>Data</th><th>Status</th></tr></thead><tbody>
        <tr><td>Novo cadastro</td><td>Maria Oliveira</td><td>Hoje, 09:42</td><td><span class="badge bg-label-success">Concluído</span></td></tr>
        <tr><td>Consulta processada</td><td>João Santos</td><td>Hoje, 09:18</td><td><span class="badge bg-label-info">Processado</span></td></tr>
        <tr><td>Revisão de acesso</td><td>Administrador</td><td>Ontem, 17:30</td><td><span class="badge bg-label-warning">Pendente</span></td></tr>
    </tbody></table></div>
    <div class="card-body"><small class="text-muted">Os números e registros desta tela são dados demonstrativos. Substitua-os pelas consultas reais do seu projeto.</small></div>
</div>
