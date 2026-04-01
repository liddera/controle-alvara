const STATUS_MAP = {
    open: { label: 'Conectado', className: 'bg-green-100 text-green-700' },
    connecting: { label: 'Aguardando conexao', className: 'bg-amber-100 text-amber-700' },
    misconfigured: { label: 'Indisponivel', className: 'bg-gray-100 text-gray-700' },
    close: { label: 'Desconectado', className: 'bg-red-100 text-red-700' },
    unknown: { label: 'Desconectado', className: 'bg-red-100 text-red-700' },
};

const POLL_DELAYS = [5000, 5000, 10000, 15000];

const getCsrfToken = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
};

const updateStatusPill = (pill, status) => {
    if (!pill) return;
    const normalized = (status || 'unknown').toLowerCase();
    const config = STATUS_MAP[normalized] || STATUS_MAP.unknown;
    pill.textContent = config.label;
    pill.dataset.status = normalized;
    pill.className = `inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${config.className}`;
};

const initWhatsAppConnection = () => {
    const root = document.querySelector('[data-whatsapp-connection]');
    if (!root) return;

    const refreshUrl = root.dataset.refreshUrl || '';
    const initialStatus = root.dataset.status || 'unknown';
    const qrContainer = root.querySelector('[data-role="qr-container"]');
    const qrOverlay = root.querySelector('[data-role="qr-overlay"]');
    const statusPill = root.querySelector('[data-role="whatsapp-status-pill"]');
    const qrImage = root.querySelector('[data-role="qr-image"]');
    const qrPayload = root.querySelector('[data-role="qr-payload"]');

    let currentStatus = initialStatus;
    let attempts = 0;

    updateStatusPill(statusPill, currentStatus);

    const setOverlay = (visible) => {
        if (!qrOverlay) return;
        qrOverlay.classList.toggle('hidden', !visible);
    };

    const setQrVisible = (visible) => {
        if (!qrContainer) return;
        qrContainer.classList.toggle('hidden', !visible);
    };

    const applyStatus = (data) => {
        const nextStatus = (data.status || currentStatus).toLowerCase();
        const statusChanged = nextStatus !== currentStatus;

        if (data.qr_base64 && qrImage) {
            qrImage.src = `data:image/png;base64,${data.qr_base64}`;
            setQrVisible(true);
        }

        if (data.qr_payload && qrPayload) {
            qrPayload.textContent = data.qr_payload;
            setQrVisible(true);
        }

        if (statusChanged) {
            if (nextStatus === 'connecting') {
                setOverlay(true);
            }

            if (nextStatus === 'open') {
                setOverlay(true);
                setTimeout(() => {
                    setOverlay(false);
                    setQrVisible(false);
                }, 1500);
            }
        }

        currentStatus = nextStatus;
        updateStatusPill(statusPill, currentStatus);
    };

    const poll = async () => {
        if (!refreshUrl || attempts >= POLL_DELAYS.length) {
            return;
        }

        const delay = POLL_DELAYS[attempts] || 15000;
        attempts += 1;

        try {
            const response = await fetch(refreshUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
            });

            if (!response.ok) {
                throw new Error('refresh');
            }

            const data = await response.json();
            applyStatus(data);

            if (currentStatus !== 'open') {
                setTimeout(poll, delay);
            }
        } catch (e) {
            if (currentStatus !== 'open') {
                setTimeout(poll, delay);
            }
        }
    };

    if (currentStatus !== 'open') {
        setTimeout(poll, POLL_DELAYS[0]);
    }
};

document.addEventListener('DOMContentLoaded', initWhatsAppConnection);
