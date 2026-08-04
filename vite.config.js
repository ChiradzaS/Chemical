import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.tsx',
                'resources/js/chemicaldelivery.tsx',
                'resources/js/chemicaldeliverylist.tsx',
                'resources/js/chemicaljobcardlist.tsx',
                'resources/js/chemicalproductlist.tsx',
                'resources/js/createjobcard.tsx',
                'resources/js/jobcardReport.tsx',
                'resources/js/reactdeliveriescreate.tsx',
                'resources/js/reactdeliverylist.tsx',
                'resources/js/reactjoblist.tsx',
                'resources/js/reactproduct.tsx',
                'resources/js/reactorderlist.tsx',
                'resources/js/chemicaljobcard.tsx',
                'resources/js/stockadjustment.tsx',
                'resources/js/about.tsx',
            ],
            refresh: true,
        }),
        react(),
    ],
    build: {
        rollupOptions: {
            output: {
                entryFileNames: 'assets/[name].js',
                chunkFileNames: 'assets/[name].js',
                assetFileNames: 'assets/[name].[ext]',
            },
        },
    },
});