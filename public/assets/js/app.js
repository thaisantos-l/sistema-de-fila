(function () {
    const baseUrl = window.APP_BASE_URL && window.APP_BASE_URL !== '.' ? window.APP_BASE_URL : '';

    function api(path, options = {}) {
        return fetch(`${baseUrl}${path}`, {
            headers: {
                'Content-Type': 'application/json',
                ...(options.headers || {}),
            },
            credentials: 'same-origin',
            ...options,
        }).then(async (response) => {
            const contentType = (response.headers.get('content-type') || '').toLowerCase();
            if (!contentType.includes('application/json')) {
                throw new Error('Resposta inválida da API.');
            }

            const data = await response.json().catch(() => ({ success: false, message: 'Resposta inválida da API.' }));
            if (!response.ok) {
                const error = new Error(data.message || 'Erro ao processar requisição.');
                error.details = data;
                throw error;
            }

            if (!data.success) {
                const error = new Error(data.message || 'Erro ao processar requisição.');
                error.details = data;
                throw error;
            }

            return data;
        });
    }

    function showAlert(el, message, type = 'info') {
        el.className = `alert alert-${type}`;
        el.textContent = message;
        el.classList.remove('d-none');
    }

    function hideAlert(el) {
        el.classList.add('d-none');
        el.textContent = '';
    }

    function statusLabel(status) {
        const map = {
            aguardando: 'Aguardando',
            em_atendimento: 'Em atendimento',
            finalizado: 'Finalizado',
            cancelado: 'Cancelado',
        };
        return map[status] || status;
    }

    function onlyDigits(value) {
        return (value || '').replace(/\D/g, '').slice(0, 11);
    }

    function maskPhone(value) {
        const digits = onlyDigits(value);
        if (!digits) {
            return '';
        }

        if (digits.length <= 10) {
            return digits
                .replace(/^(\d{0,2})(\d{0,4})(\d{0,4}).*/, (_, ddd, p1, p2) => {
                    let out = '';
                    if (ddd) {
                        out += `(${ddd}`;
                        if (ddd.length === 2) {
                            out += ') ';
                        }
                    }
                    if (p1) {
                        out += p1;
                    }
                    if (p2) {
                        out += `-${p2}`;
                    }
                    return out;
                });
        }

        return digits.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, (_, ddd, p1, p2) => `(${ddd}) ${p1}${p2 ? `-${p2}` : ''}`);
    }

    function statusBadge(status) {
        const style = {
            aguardando: 'warning text-dark',
            em_atendimento: 'primary',
            finalizado: 'success',
            cancelado: 'secondary',
        };

        return `<span class="badge rounded-pill text-bg-${style[status] || 'secondary'} badge-status">${statusLabel(status)}</span>`;
    }

    const createForm = document.getElementById('createTicketForm');
    if (createForm) {
        const createAlert = document.getElementById('createTicketAlert');
        const trackAlert = document.getElementById('trackAlert');
        const trackResult = document.getElementById('trackResult');
        const ticketBox = document.getElementById('ticketBox');
        const ticketNumber = document.getElementById('ticketNumber');
        const ticketPosition = document.getElementById('ticketPosition');
        const phoneInput = document.getElementById('telefone');

        function showTicket(data) {
            ticketNumber.textContent = data?.id ?? '-';
            ticketPosition.textContent = `Posição atual: ${data?.position ?? '-'}`;
            ticketBox.classList.remove('d-none');
        }

        function hideTicket() {
            ticketBox.classList.add('d-none');
            ticketNumber.textContent = '-';
            ticketPosition.textContent = 'Posição atual: -';
        }

        if (phoneInput) {
            phoneInput.addEventListener('input', () => {
                phoneInput.value = maskPhone(phoneInput.value);
            });
        }

        createForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            hideAlert(createAlert);
            hideTicket();

            const nome = document.getElementById('nome').value.trim();
            const telefone = document.getElementById('telefone').value.trim();

            try {
                const res = await api('/api/queue', {
                    method: 'POST',
                    body: JSON.stringify({ nome, telefone }),
                });

                const data = res.data || {};
                showTicket(data);
                showAlert(
                    createAlert,
                    `${res.message} Sua senha é ${data.id}. Posição atual: ${data.position ?? '-'}.`,
                    'success'
                );
                const trackIdInput = document.getElementById('ticketId');
                if (trackIdInput && data.id) {
                    trackIdInput.value = data.id;
                }
                createForm.reset();
                hideAlert(trackAlert);
                trackResult.classList.add('d-none');
            } catch (error) {
                const data = error.details?.data || null;
                if (data?.id) {
                    showTicket(data);
                    const trackIdInput = document.getElementById('ticketId');
                    if (trackIdInput) {
                        trackIdInput.value = data.id;
                    }
                }

                const detail = error.details?.error ? ` (${error.details.error})` : '';
                showAlert(createAlert, `${error.message}${detail}`, 'danger');
            }
        });

        const trackForm = document.getElementById('trackTicketForm');
        trackForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            hideAlert(trackAlert);

            const id = document.getElementById('ticketId').value;
            try {
                const res = await api(`/api/queue/${id}`);
                const data = res.data;

                document.getElementById('resultId').textContent = data.id;
                document.getElementById('resultStatus').textContent = statusLabel(data.status);
                document.getElementById('resultPosition').textContent = data.position ?? '-';
                trackResult.classList.remove('d-none');
                showTicket(data);
            } catch (error) {
                trackResult.classList.add('d-none');
                const detail = error.details?.error ? ` (${error.details.error})` : '';
                showAlert(trackAlert, `${error.message}${detail}`, 'danger');
            }
        });
    }

    const queueTable = document.getElementById('queueTable');
    if (queueTable) {
        const tbody = queueTable.querySelector('tbody');
        const alertBox = document.getElementById('adminAlert');
        const callNextBtn = document.getElementById('callNextBtn');

        async function loadMetrics() {
            const res = await api('/api/metrics');
            const metrics = res.data || {};

            document.getElementById('mTotal').textContent = metrics.total ?? 0;
            document.getElementById('mAguardando').textContent = metrics.aguardando ?? 0;
            document.getElementById('mAtendimento').textContent = metrics.em_atendimento ?? 0;
            document.getElementById('mFinalizado').textContent = metrics.finalizado ?? 0;
            document.getElementById('mCancelado').textContent = metrics.cancelado ?? 0;
            document.getElementById('mEspera').textContent = metrics.tempo_medio_espera_min ?? 0;
        }

        async function loadQueue() {
            const res = await api('/api/queue');
            const rows = res.data || [];

            if (!rows.length) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Nenhum registro na fila.</td></tr>';
                return;
            }

            tbody.innerHTML = rows.map((row) => {
                const finishBtn = row.status === 'em_atendimento'
                    ? `<button class="btn btn-sm btn-success" data-action="finish" data-id="${row.id}">Finalizar</button>`
                    : '';

                const cancelBtn = ['aguardando', 'em_atendimento'].includes(row.status)
                    ? `<button class="btn btn-sm btn-outline-warning" data-action="cancel" data-id="${row.id}">Cancelar</button>`
                    : '';

                const deleteBtn = ['finalizado', 'cancelado'].includes(row.status)
                    ? `<button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${row.id}">Remover</button>`
                    : '';

                return `
                    <tr>
                        <td>${row.id}</td>
                        <td>${row.nome}</td>
                        <td>${row.telefone || '-'}</td>
                        <td>${statusBadge(row.status)}</td>
                        <td>${row.position ?? '-'}</td>
                        <td>${row.created_at || '-'}</td>
                        <td class="d-flex gap-1 flex-wrap">${finishBtn}${cancelBtn}${deleteBtn}</td>
                    </tr>
                `;
            }).join('');
        }

        async function refresh() {
            try {
                hideAlert(alertBox);
                await Promise.all([loadMetrics(), loadQueue()]);
            } catch (error) {
                showAlert(alertBox, error.message, 'danger');
            }
        }

        callNextBtn.addEventListener('click', async () => {
            try {
                const res = await api('/api/queue/next/call', { method: 'PATCH' });
                showAlert(alertBox, res.message, 'success');
                await refresh();
            } catch (error) {
                showAlert(alertBox, error.message, 'warning');
            }
        });

        tbody.addEventListener('click', async (event) => {
            const button = event.target.closest('button[data-action]');
            if (!button) {
                return;
            }

            const id = button.dataset.id;
            const action = button.dataset.action;

            const actions = {
                finish: { method: 'PATCH', path: `/api/queue/${id}/finish` },
                cancel: { method: 'PATCH', path: `/api/queue/${id}/cancel` },
                delete: { method: 'DELETE', path: `/api/queue/${id}` },
            };

            try {
                const target = actions[action];
                const res = await api(target.path, { method: target.method });
                showAlert(alertBox, res.message, 'success');
                await refresh();
            } catch (error) {
                showAlert(alertBox, error.message, 'danger');
            }
        });

        refresh();
    }
})();
