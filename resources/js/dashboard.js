import './echo';

function escapeHtml(value) {
    if (value === null || value === undefined) {
        return '';
    }

    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function updatePendingCounts(count) {
    const badge = document.getElementById('pending-offers-badge');
    const dropdownCount = document.getElementById('pending-offers-dropdown-count');
    const widgetCount = document.getElementById('pending-offers-widget-count');
    const widgetPulse = document.getElementById('pending-offers-widget-pulse');

    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    if (dropdownCount) {
        dropdownCount.textContent = `${count} New`;
    }

    if (widgetCount) {
        widgetCount.textContent = `${count} Offers`;
    }

    if (widgetPulse) {
        widgetPulse.classList.toggle('hidden', count <= 0);
    }
}

function buildNotificationItem(payload) {
    const offer = payload.offer ?? {};
    const car = payload.car ?? {};
    const buyerName = escapeHtml(payload.buyer_name ?? offer.buyer_name ?? 'Buyer');
    const carLabel = escapeHtml(
        `${car.brand ?? 'Car'} ${car.model ?? ''}`.trim()
    );
    const priceFormatted = escapeHtml(
        payload.price_offered_formatted ??
            `Rp ${offer.price_offered_formatted ?? ''}`
    );
    const offerId = offer.id;

    const item = document.createElement('div');
    item.className =
        'p-4 hover:bg-gray-50/80 transition flex gap-3.5 items-start cursor-pointer offer-notification-item animate-in';
    item.dataset.offerId = offerId;
    item.innerHTML = `
        <div class="w-9 h-9 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center shrink-0 mt-0.5">
            <i data-lucide="file-text" class="w-4 h-4"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs text-gray-500 font-medium">New Offer Submitted</p>
            <p class="text-sm font-bold text-gray-900 truncate mt-0.5">${buyerName}</p>
            <p class="text-xs text-gray-600 mt-1 bg-gray-100 px-2 py-1 rounded-lg inline-block font-medium">
                Target: ${carLabel}
            </p>
            <div class="flex items-center justify-between mt-2.5">
                <span class="text-xs font-black text-blue-600">${priceFormatted}</span>
                <span class="text-[10px] bg-amber-50 text-amber-700 px-2 py-0.5 rounded-md font-bold uppercase tracking-wider">Review</span>
            </div>
        </div>
    `;

    item.addEventListener('click', () => {
        if (typeof window.openReviewModal === 'function') {
            window.openReviewModal(
                offerId,
                payload.buyer_name ?? offer.buyer_name,
                `${car.brand ?? 'Car'} ${car.model ?? ''}`.trim(),
                offer.price_offered_formatted ??
                    String(offer.price_offered ?? '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')
            );
        }
    });

    return item;
}

function buildTableRow(payload) {
    const offer = payload.offer ?? {};
    const car = payload.car ?? {};
    const config = window.AutodealsConfig ?? {};
    const csrf = config.csrfToken ?? '';
    const offerId = offer.id;
    const buyerName = escapeHtml(payload.buyer_name ?? offer.buyer_name);
    const carName = escapeHtml(`${car.brand ?? ''} ${car.model ?? ''}`.trim());
    const stockCode = escapeHtml(car.stock_code ?? 'N/A');
    const priceFormatted = escapeHtml(
        payload.price_offered_formatted ??
            `Rp ${offer.price_offered_formatted ?? ''}`
    );
    const acceptUrl = config.offerAcceptUrl
        ? config.offerAcceptUrl(offerId)
        : `/offers/${offerId}/accept`;
    const rejectUrl = config.offerRejectUrl
        ? config.offerRejectUrl(offerId)
        : `/offers/${offerId}/reject`;

    const row = document.createElement('tr');
    row.className = 'hover:bg-gray-50/60 transition offer-table-row animate-in';
    row.dataset.offerId = offerId;
    row.innerHTML = `
        <td class="p-4 font-bold text-gray-950">${buyerName}</td>
        <td class="p-4">
            <span class="font-medium text-gray-900 block">${carName}</span>
            <span class="text-xs text-gray-400 font-mono">${stockCode}</span>
        </td>
        <td class="p-4 font-bold text-blue-600">${priceFormatted}</td>
        <td class="p-4">
            <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-600 text-xs font-semibold capitalize">pending review</span>
        </td>
        <td class="p-4 text-center">
            <button
                type="button"
                class="review-offer-btn px-3 py-1.5 text-xs bg-gray-100 hover:bg-blue-600 hover:text-white text-gray-700 font-bold rounded-lg transition cursor-pointer"
            >
                Review Offer
            </button>
            <form id="accept-form-${offerId}" action="${acceptUrl}" method="POST" class="hidden">
                <input type="hidden" name="_token" value="${csrf}">
            </form>
            <form id="reject-form-${offerId}" action="${rejectUrl}" method="POST" class="hidden">
                <input type="hidden" name="_token" value="${csrf}">
                <input type="hidden" name="reject_reason" value="">
            </form>
        </td>
    `;

    row.querySelector('.review-offer-btn')?.addEventListener('click', () => {
        if (typeof window.openReviewModal === 'function') {
            window.openReviewModal(
                offerId,
                payload.buyer_name ?? offer.buyer_name,
                `${car.brand ?? 'Car'} ${car.model ?? ''}`.trim(),
                offer.price_offered_formatted ??
                    String(offer.price_offered ?? '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')
            );
        }
    });

    return row;
}

function prependOfferToUi(payload) {
    const notificationsList = document.getElementById('notifications-list');
    const tableBody = document.getElementById('pending-offers-table-body');
    const emptyNotifications = document.getElementById('notifications-empty');
    const emptyTableRow = document.getElementById('pending-offers-empty-row');

    if (emptyNotifications) {
        emptyNotifications.remove();
    }

    if (emptyTableRow) {
        emptyTableRow.remove();
    }

    if (notificationsList) {
        const existing = notificationsList.querySelector(
            `[data-offer-id="${payload.offer?.id}"]`
        );
        if (!existing) {
            notificationsList.prepend(buildNotificationItem(payload));
        }
    }

    if (tableBody) {
        const existing = tableBody.querySelector(
            `[data-offer-id="${payload.offer?.id}"]`
        );
        if (!existing) {
            tableBody.prepend(buildTableRow(payload));
        }
    }

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

function showOfferToast(buyerName, options = {}) {
    if (typeof Swal === 'undefined') {
        return;
    }

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: options.icon ?? 'info',
        title: options.title ?? `New offer received from ${buyerName}!`,
        showConfirmButton: false,
        timer: options.timer ?? 4500,
        timerProgressBar: true,
    });
}

function showCancellationToast(payload) {
    if (typeof Swal === 'undefined') {
        return;
    }

    const message =
        payload.cancel_message ??
        `Pemberitahuan: Penawaran dari ${payload.buyer_name ?? 'pengguna'} untuk mobil ${payload.vehicle_label ?? ''} telah DIBATALKAN oleh pengguna.`;

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'warning',
        title: message,
        showConfirmButton: false,
        timer: 6000,
        timerProgressBar: true,
        width: '28rem',
    });
}

function removeOfferFromUi(offerId) {
    if (!offerId) {
        return;
    }

    document
        .querySelectorAll(`[data-offer-id="${offerId}"]`)
        .forEach((element) => element.remove());

    const tableBody = document.getElementById('pending-offers-table-body');
    const notificationsList = document.getElementById('notifications-list');

    if (tableBody && tableBody.querySelectorAll('tr[data-offer-id]').length === 0) {
        const emptyRow = document.createElement('tr');
        emptyRow.id = 'pending-offers-empty-row';
        emptyRow.innerHTML = `
            <td colspan="5" class="p-8 text-center text-gray-400 font-medium">
                No pending offers available at the moment.
            </td>
        `;
        tableBody.appendChild(emptyRow);
    }

    if (
        notificationsList &&
        notificationsList.querySelectorAll('[data-offer-id]').length === 0 &&
        !document.getElementById('notifications-empty')
    ) {
        const empty = document.createElement('div');
        empty.id = 'notifications-empty';
        empty.className =
            'p-8 text-center flex flex-col items-center justify-center gap-2';
        empty.innerHTML =
            '<p class="text-xs text-gray-400 font-medium">All caught up! No new offers.</p>';
        notificationsList.appendChild(empty);
    }
}

function handleOfferSubmitted(payload, options = {}) {
    const count = payload.pending_review_count ?? 0;

    updatePendingCounts(count);
    prependOfferToUi(payload);

    if (!options.silent) {
        showOfferToast(payload.buyer_name ?? payload.offer?.buyer_name ?? 'a buyer');
    }
}

function handleOfferCancelled(payload) {
    const offerId = payload.offer?.id;
    const count = payload.pending_review_count ?? 0;

    updatePendingCounts(count);
    removeOfferFromUi(offerId);
    showCancellationToast(payload);

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

function handleOfferDecision(payload, status) {
    const offerId = payload.offer_id ?? payload.offer?.id;
    const count = payload.pending_review_count;

    if (typeof count === 'number') {
        updatePendingCounts(count);
    } else {
        updatePendingCounts(Math.max(0, getPendingCountFromDom() - 1));
    }

    removeOfferFromUi(offerId);

    if (typeof Swal !== 'undefined') {
        const label = payload.vehicle_label ?? payload.offer?.buyer_name ?? 'penawaran';
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: status === 'accepted' ? 'success' : 'info',
            title:
                status === 'accepted'
                    ? `Penawaran diterima — ${label}`
                    : `Penawaran ditolak — ${label}`,
            showConfirmButton: false,
            timer: 4000,
        });
    }

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

function getPendingCountFromDom() {
    const badge = document.getElementById('pending-offers-badge');
    return badge ? parseInt(badge.textContent, 10) || 0 : 0;
}

function updateReverbStatus(state) {
    const el = document.getElementById('reverb-connection-status');
    const dot = document.getElementById('reverb-connection-dot');
    if (!el) {
        return;
    }

    const labels = {
        live: { text: 'Live', textClass: 'text-emerald-600', dotClass: 'bg-emerald-500' },
        connecting: { text: 'Connecting', textClass: 'text-amber-600', dotClass: 'bg-amber-400 animate-pulse' },
        offline: { text: 'Offline', textClass: 'text-rose-600', dotClass: 'bg-rose-500 animate-pulse' },
    };

    const cfg = labels[state] ?? labels.offline;
    el.textContent = cfg.text;
    el.className = `text-[10px] font-bold uppercase tracking-wider ${cfg.textClass}`;

    if (dot) {
        dot.className = `w-2 h-2 rounded-full ${cfg.dotClass}`;
    }
}

let echoListenersBound = false;
let pollIntervalId = null;

function bindEchoListeners() {
    if (echoListenersBound || typeof window.Echo === 'undefined') {
        return;
    }

    echoListenersBound = true;

    const channel = window.Echo.channel('admin-dashboard');

    channel.listen('.OfferSubmitted', (payload) => {
        console.info('[AutoDeals] WebSocket OfferSubmitted', payload);
        handleOfferSubmitted(payload);
    });

    channel.listen('.OfferCancelled', (payload) => handleOfferCancelled(payload));
    channel.listen('.OfferAccepted', (payload) => handleOfferDecision(payload, 'accepted'));
    channel.listen('.OfferRejected', (payload) => handleOfferDecision(payload, 'rejected'));

    channel.subscribed(() => {
        console.info('[AutoDeals] Subscribed: admin-dashboard');
        updateReverbStatus('live');
    });

    channel.error((error) => {
        console.error('[AutoDeals] Channel error', error);
        updateReverbStatus('offline');
    });
}

async function syncPendingOffersFromServer() {
    const url =
        (window.AutodealsConfig && window.AutodealsConfig.pendingOffersSyncUrl) ||
        '/dashboard/pending-offers-sync';

    try {
        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        const count = data.pending_review_count ?? 0;
        updatePendingCounts(count);

        (data.offers ?? []).forEach((payload) => {
            const offerId = payload.offer?.id;
            if (!offerId) {
                return;
            }

            const exists = document.querySelector(`[data-offer-id="${offerId}"]`);
            if (!exists) {
                handleOfferSubmitted(payload, { silent: false });
            }
        });
    } catch (error) {
        console.warn('[AutoDeals] Polling sync gagal', error);
    }
}

function startPollingFallback() {
    if (pollIntervalId) {
        return;
    }

    syncPendingOffersFromServer();
    pollIntervalId = window.setInterval(syncPendingOffersFromServer, 4000);
}

document.addEventListener('DOMContentLoaded', () => {
    updateReverbStatus('connecting');
    bindEchoListeners();
    startPollingFallback();
});

window.addEventListener('autodeals:reverb-connecting', () => {
    updateReverbStatus('connecting');
});

window.addEventListener('autodeals:reverb-connected', () => {
    updateReverbStatus('live');
    bindEchoListeners();
});

window.addEventListener('autodeals:reverb-error', () => {
    updateReverbStatus('offline');
});

window.addEventListener('autodeals:reverb-disconnected', () => {
    updateReverbStatus('offline');
});
