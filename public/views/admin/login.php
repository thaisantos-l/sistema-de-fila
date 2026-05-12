<?php $page = 'admin-login'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="mb-3">Login Admin</h4>
                <p class="text-muted small mb-3">No primeiro acesso, se não existir usuário admin, este formulário cria o primeiro automaticamente.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars((string) $error) ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= $basePath ?>/admin/login" class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="username">Usuário</label>
                        <input class="form-control" id="username" name="username" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="password">Senha</label>
                        <input class="form-control" id="password" name="password" type="password" required>
                    </div>
                    <div class="col-12 d-grid">
                        <button class="btn btn-primary" type="submit">Entrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
