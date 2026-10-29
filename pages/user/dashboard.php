<?php
$userName = htmlspecialchars((string) (currentUser()['name'] ?? 'Usuário'), ENT_QUOTES, 'UTF-8');
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><h4 class="fw-bold mb-1">Meu painel</h4><p class="text-muted mb-0">Bem-vindo, <?= $userName ?>. Acompanhe sua utilização.</p></div>
    <span class="badge bg-label-primary">Usuário</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4"><div class="card"><div class="card-body"><span class="text-muted">Consultas realizadas</span><h3 class="mt-2 mb-0">24</h3><small class="text-muted">Neste mês · exemplo</small></div></div></div>
    <div class="col-sm-6 col-xl-4"><div class="card"><div class="card-body"><span class="text-muted">Saldo disponível</span><h3 class="mt-2 mb-0">76</h3><small class="text-muted">Unidades demonstrativas</small></div></div></div>
    <div class="col-sm-6 col-xl-4"><div class="card"><div class="card-body"><span class="text-muted">Plano atual</span><h3 class="mt-2 mb-0">Inicial</h3><small class="text-success">Ativo · exemplo</small></div></div></div>
</div>

<div class="card mb-4"><div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div><h5 class="mb-1">Precisa consultar novos dados?</h5><p class="text-muted mb-0">Acesse o histórico e acompanhe suas solicitações.</p></div>
    <a href="/consultas" class="btn btn-primary"><i class="bx bx-search me-1"></i> Ver consultas</a>
</div></div>

<div class="card"><div class="card-header"><h5 class="mb-0">Últimas consultas</h5></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Referência</th><th>Solicitada em</th><th>Status</th></tr></thead><tbody>
<tr><td>#CONS-1024</td><td>Hoje, 10:05</td><td><span class="badge bg-label-success">Concluída</span></td></tr>
<tr><td>#CONS-1023</td><td>Ontem, 16:21</td><td><span class="badge bg-label-info">Processada</span></td></tr>
<tr><td>#CONS-1022</td><td>Ontem, 11:08</td><td><span class="badge bg-label-warning">Em análise</span></td></tr>
</tbody></table></div><div class="card-body"><small class="text-muted">Conteúdo de demonstração; conecte ao histórico real quando implementar o módulo.</small></div></div>
