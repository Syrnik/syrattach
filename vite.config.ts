import { defineConfig, type Plugin } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import fs from 'fs';

function moveCssPlugin(): Plugin {
    return {
        name: 'syrattach-move-css',
        writeBundle(options, bundle) {
            const outDir = options.dir ?? 'js';
            for (const fileName of Object.keys(bundle)) {
                if (fileName.endsWith('.css')) {
                    const src = path.resolve(outDir, fileName);
                    const dest = path.resolve(outDir, '..', 'css', 'syrattach-prod.css');
                    fs.mkdirSync(path.dirname(dest), { recursive: true });
                    fs.copyFileSync(src, dest);
                    fs.unlinkSync(src);
                    break;
                }
            }
        },
    };
}

export default defineConfig(({ mode }) => ({
    plugins: [vue(), moveCssPlugin()],
    define: {
        'process.env.NODE_ENV': JSON.stringify(mode === 'production' ? 'production' : 'development'),
    },
    build: {
        lib: {
            entry: 'src/index.ts',
            formats: ['iife'],
            name: '__syrattach',
            fileName: () => mode === 'production' ? 'prod_syrattach.min.js' : 'prod_syrattach.js',
        },
        outDir: 'js',
        emptyOutDir: false,
        minify: mode === 'production' ? 'terser' : false,
        rollupOptions: {},
        cssCodeSplit: false,
    },
}));
