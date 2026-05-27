import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

function stripEnv(value, fallback = '') {
    if (value === undefined || value === null || value === '') {
        return fallback;
    }

    return String(value).replace(/^["']|["']$/g, '');
}

/**
 * Host WebSocket — jangan pakai 0.0.0.0 di browser.
 */
function resolveWsHost() {
    const blade = window.AutodealsReverb ?? {};
    const configured = stripEnv(import.meta.env.VITE_REVERB_HOST, stripEnv(blade.host, '127.0.0.1'));
    const pageHost = window.location.hostname;

    if (pageHost === '0.0.0.0' || pageHost === '') {
        return '127.0.0.1';
    }

    if (configured === 'localhost' || configured === '127.0.0.1') {
        return pageHost === 'localhost' ? '127.0.0.1' : pageHost;
    }

    return configured;
}

const bladeConfig = window.AutodealsReverb ?? {};
const key = stripEnv(bladeConfig.key) || stripEnv(import.meta.env.VITE_REVERB_APP_KEY);
const scheme = stripEnv(bladeConfig.scheme, stripEnv(import.meta.env.VITE_REVERB_SCHEME, 'http'));
const port = Number(stripEnv(bladeConfig.port, stripEnv(import.meta.env.VITE_REVERB_PORT, '8080')));
const wsHost = resolveWsHost();
const forceTLS = scheme === 'https';

window.AutodealsEchoConfig = { key, wsHost, port, scheme };

if (!key) {
    console.error(
        '[AutoDeals Echo] REVERB_APP_KEY kosong. Cek .env → jalankan: php artisan config:clear && restart npm run dev',
    );
} else {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key,
        wsHost,
        wsPort: port,
        wssPort: port,
        forceTLS,
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
        // Echo v2: jangan prepend namespace App\Events\ pada listener tanpa titik
        namespace: '',
    });

    const pusher = window.Echo?.connector?.pusher;

    if (pusher) {
        Pusher.logToConsole = import.meta.env.DEV;

        pusher.connection.bind('connecting', () => {
            window.dispatchEvent(new CustomEvent('autodeals:reverb-connecting'));
        });

        pusher.connection.bind('connected', () => {
            console.info('[AutoDeals Echo] WebSocket connected', window.AutodealsEchoConfig);
            window.dispatchEvent(new CustomEvent('autodeals:reverb-connected'));
        });

        pusher.connection.bind('failed', () => {
            console.error('[AutoDeals Echo] WebSocket failed — jalankan: php artisan reverb:start');
            window.dispatchEvent(new CustomEvent('autodeals:reverb-error'));
        });

        pusher.connection.bind('unavailable', () => {
            console.error('[AutoDeals Echo] Reverb unavailable di', `${scheme}://${wsHost}:${port}`);
            window.dispatchEvent(new CustomEvent('autodeals:reverb-error'));
        });

        pusher.connection.bind('disconnected', () => {
            window.dispatchEvent(new CustomEvent('autodeals:reverb-disconnected'));
        });
    }

    if (typeof window.Echo.onConnectionChange === 'function') {
        window.Echo.onConnectionChange((status) => {
            console.info('[AutoDeals Echo] status:', status);
            if (status === 'connected') {
                window.dispatchEvent(new CustomEvent('autodeals:reverb-connected'));
            }
        });
    }
}
