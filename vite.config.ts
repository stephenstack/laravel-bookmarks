import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const devHost = env.VITE_DEV_HOST || '127.0.0.1';
    const devPort = Number(env.VITE_DEV_PORT || 5173);
    const devOrigin = env.VITE_DEV_SERVER_URL || `http://${devHost}:${devPort}`;

    return {
        server: {
            host: env.VITE_DEV_BIND || devHost,
            port: devPort,
            strictPort: true,
            origin: devOrigin,
            https: {
                key:
                    env.VITE_DEV_KEY_PATH ||
                    '/etc/pki/tls/private/wildcard.key',
                cert:
                    env.VITE_DEV_CERT_PATH ||
                    '/etc/pki/tls/certs/fullchain.crt',
            },
        },
        plugins: [
            laravel({
                input: ['resources/js/app.ts'],
                refresh: true,
            }),
            tailwindcss(),
            wayfinder({
                formVariants: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
    };
});
