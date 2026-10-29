<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="/dashboard" class="app-brand-link">
            <span class="app-brand-text demo menu-text fw-bolder ms-2"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>
    <ul class="menu-inner py-1">
        <li class="menu-item <?= isActive(['dashboard']) ?>">
            <a href="/dashboard" class="menu-link"><i class="menu-icon tf-icons bx bx-home-circle"></i><div>Painel operacional</div></a>
        </li>
        <li class="menu-item <?= isActive(['tarefas']) ?>">
            <a href="/tarefas" class="menu-link"><i class="menu-icon tf-icons bx bx-task"></i><div>Minhas tarefas</div></a>
        </li>
        <li class="menu-item">
            <a href="#" id="btnLogout" class="menu-link"><i class="menu-icon tf-icons bx bx-log-out"></i><div>Sair</div></a>
        </li>
    </ul>
</aside>
