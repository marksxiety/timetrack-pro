import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        vue(),
    ],
    server: {
        watch: {
            ignored: [
                '**/node_modules/**',
                '**/storage/**',
                '**/vendor/**',
                '**/public/**'
            ],
        },
        fs: {
            strict: true
        },
        hmr: {
            host: 'localhost'
        }
    },
    optimizeDeps: {
        include: [
            'vue',
            '@inertiajs/inertia',
            '@inertiajs/inertia-vue3'
        ]
    },
});
