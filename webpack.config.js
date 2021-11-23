const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. ) if your JavaScript imports CSS.
     */
    // .addEntry('app', './assets/app.js')
    //
    // // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    // .enableStimulusBridge('./assets/controllers.json')

    .addEntry('site-common', '/js/app/site/common.js')
    .addEntry('frontend-common', '/js/app/frontend/common.js')
    .addEntry('profile', '/js/app/profile/index.js')
    .addEntry('common', '/js/app/common.js')
    .addEntry('register', './js/app/register/index.js')
    .addEntry('notifications', './js/app/notifications/index.js')
    .addEntry('widgets', './js/app/widgets/index.js')
    .addEntry('widgets-admin', './js/app/widgets/admin.js')
    .addEntry('admin-businesses', './js/app/admin/businesses.js')
    .addEntry('business', './js/app/business/index.js')
    .addEntry('business-form', './js/app/business/form.js')
    .addEntry('business-fulfillment-methods', './js/app/business/fulfillment-methods.js')
    .addEntry('listing-pricing-rules', './js/app/listing/pricing-rules.js')
    .addEntry('businesses-map', './js/app/businesses-map/index.js')
    .addEntry('business-service-editor', './js/app/business/service-editor.js')
    .addEntry('listing-form', './js/app/listing/form.js')
    .addEntry('listing', './js/app/site/listing.js')
    .addEntry('mapboard', './js/app/mapboard/index.js')
    .addEntry('product-form', './js/app/product/form.js')
    .addEntry('product-list', './js/app/product/list.js')
    .addEntry('product-option-form', './js/app/forms/product-option.js')
    .addEntry('admin-listings', './js/app/admin/listings.js')
    .addEntry('user-form', './js/app/user/form.js')
    .addEntry('search-address', './js/app/search/address.js')
    .addEntry('frontend-search-address', './js/app/search/frontend-address.js')
    .addEntry('frontend-map-category', './js/app/frontend/map/map-category.js')
    .addEntry('frontend-listing-detail', './js/app/frontend/listing-detail.js')
    .addEntry('frontend-map-layers', './js/app/frontend/map/map-layers.js')
    .addEntry('frontend-datepicker-detail', './js/app/frontend/components/datepicker-detail.js')
    .addEntry('user-invite', './js/app/user/invite.js')
    .addEntry('business-list', './js/app/business/list.js')
    .addEntry('dashboard', './js/app/dashboard/index.js')
    .addEntry('calendar', './js/app/calendar/index.js')
    .addEntry('scheduler', './js/app/calendar-scheduler/index.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // .configureBabel((config) => {
    //     config.plugins.push('@babel/plugin-proposal-class-properties');
    // })

    // enables @babel/preset-env polyfills
    // .configureBabelPresetEnv((config) => {
    //     config.useBuiltIns = 'usage';
    //     config.corejs = 3;
    // })

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use React
    //.enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()
;

// https://github.com/webpack/webpack-dev-server/blob/master/CHANGELOG.md#400-beta0-2020-11-27
Encore.configureDevServerOptions(options => {
    options.firewall = false
    options.static = [
        {
            directory: 'public/',
            watch: {
                usePolling: true,
            }
        }
    ]
    options.headers = { 'Access-Control-Allow-Origin': '*' }
    options.compress = true
})

let webpackConfig = Encore.getWebpackConfig();

webpackConfig.stats = 'minimal'

module.exports = webpackConfig
