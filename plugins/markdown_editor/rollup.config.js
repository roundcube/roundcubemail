import { nodeResolve } from '@rollup/plugin-node-resolve';
// Un-comment to generate bundle-stats.html
// import { bundleStats } from 'rollup-plugin-bundle-stats';
import terser from '@rollup/plugin-terser';

export default {
    input: 'javascript/index.js',
    output: [
        {
            file: 'markdown_editor.min.js',
            format: 'es',
            plugins: [terser()],
            // Un-comment to generate bundle-stats.html
            // assetFileNames: 'assets/[name].[hash][extname]',
            // chunkFileNames: 'assets/[name].[hash].js',
            // entryFileNames: 'assets/[name].[hash].js',
        },
    ],
    plugins: [nodeResolve()],
    // Un-comment to generate bundle-stats.html
    // plugins: [nodeResolve(), bundleStats()]
};
