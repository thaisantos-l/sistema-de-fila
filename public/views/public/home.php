<?php $page = 'public-home'; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">Entrar na fila</h5>
                <form id="createTicketForm" class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="nome">Nome</label>
                        <input class="form-control" id="nome" name="nome" required minlength="3" placeholder="Digite seu nome completo">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="telefone">Telefone</label>
                        <input class="form-control" id="telefone" name="telefone" maxlength="15" placeholder="(11) 99999-9999" required>
                    </div>
                    <div class="col-12 d-grid">
                        <button class="btn btn-primary" type="submit">Entrar na fila</button>
                    </div>
                </form>
                <div id="createTicketAlert" class="alert mt-3 d-none" role="alert"></div>

                <div id="ticketBox" class="card border-success bg-success-subtle mt-3 d-none">
                    <div class="card-body">
                        <small class="text-success-emphasis d-block mb-1">Sua senha</small>
                        <div class="display-6 fw-bold text-success-emphasis" id="ticketNumber">-</div>
                        <small class="text-secondary" id="ticketPosition">Posição atual: -</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">Consultar posição</h5>
                <form id="trackTicketForm" class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="ticketId">Número da senha</label>
                        <input class="form-control" id="ticketId" name="ticketId" type="number" min="1" required placeholder="Ex: 1">
                    </div>
                    <div class="col-12 d-grid">
                        <button class="btn btn-outline-primary" type="submit">Consultar</button>
                    </div>
                </form>

                <div id="trackResult" class="mt-3 d-none">
                    <h6 class="mb-3">Resultado</h6>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Senha</span><strong id="resultId">-</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Status</span><strong id="resultStatus">-</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Posição</span><strong id="resultPosition">-</strong>
                        </li>
                    </ul>
                </div>
                <div id="trackAlert" class="alert mt-3 d-none" role="alert"></div>
            </div>
        </div>
    </div>
</div>
