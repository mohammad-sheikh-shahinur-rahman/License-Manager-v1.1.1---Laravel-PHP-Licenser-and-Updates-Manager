const mix = require('laravel-mix')
const path = require('path')

const directory = path.basename(path.resolve(__dirname))
const source = `platform/plugins/${directory}`
const dist = `public/vendor/core/plugins/${directory}`

mix
    .sass(`${source}/resources/sass/dashboard/style.scss`, `${dist}/css/dashboard`)
    .sass(`${source}/resources/sass/dashboard/style-rtl.scss`, `${dist}/css/dashboard`)
    .js(`${source}/resources/js/dashboard/script.js`, `${dist}/js/dashboard`)
    .js(`${source}/resources/js/regenerate-encryption-key.js`, `${dist}/js`)
    .js(`${source}/resources/js/bulk-license-defaults.js`, `${dist}/js`)

if (mix.inProduction()) {
    mix
        .copy(`${dist}/css/dashboard/style.css`, `${source}/public/css/dashboard`)
        .copy(`${dist}/css/dashboard/style-rtl.css`, `${source}/public/css/dashboard`)
        .copy(`${dist}/js/dashboard/script.js`, `${source}/public/js/dashboard`)
        .copy(`${dist}/js/regenerate-encryption-key.js`, `${source}/public/js`)
        .copy(`${dist}/js/bulk-license-defaults.js`, `${source}/public/js`)
}
