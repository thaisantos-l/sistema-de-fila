<?php $page = 'admin-dashboard'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="mb-1">Painel Administrativo</h3>
        <small class="text-muted">Usuário logado: <?= htmlspecialchars((string) ($admin['username'] ?? '')) ?></small>
    </div>
    <div class="d-flex gap-2">
        <button id="callNextBtn" class="btn btn-success">Chamar próximo</button>
        <form method="POST" action="<?= $basePath ?>/admin/logout">
            <button type="submit" class="btn btn-outline-danger">Sair</button>
        </form>
    </div>
</div>

<div class="row g-3 mb-4" id="metricsCards">
    <div class="col-6 col-md-4 col-lg-2"><div class="metric-card"><span>Total</span><strong id="mTotal">0</strong></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="metric-card"><span>Aguardando</span><strong id="mAguardando">0</strong></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="metric-card"><span>Atendimento</span><strong id="mAtendimento">0</strong></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="metric-card"><span>Finalizado</span><strong id="mFinalizado">0</strong></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="metric-card"><span>Cancelado</span><strong id="mCancelado">0</strong></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="metric-card"><span>Espera média (min)</span><strong id="mEspera">0</strong></div></div>
</div>

<div id="adminAlert" class="alert d-none" role="alert"></div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle" id="queueTable">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Telefone</th>
                    <th>Status</th>
                    <th>Posição</th>
                    <th>Criado em</th>
                    <th>Ações</th>
                </tr>
                </thead>
                <tbody>
                <tr><td colspan="7" class="text-center text-muted">Carregando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
