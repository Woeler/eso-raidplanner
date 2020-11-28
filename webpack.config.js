var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/js/app.js')
    .addEntry('calendar', './assets/js/calendar.js')
    .addEntry('markdown', './assets/js/markdown.js')
    .addEntry('poll', './assets/js/poll.js')
    .addStyleEntry('darkmode', './assets/css/darkmode.scss')
    .autoProvidejQuery()
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning()
    .configureBabel(() => {}, {
        useBuiltIns: 'usage',
        corejs: 3
    })
    .enableSassLoader(function(sassOptions) {}, {
        //resolveUrlLoader: false
    })
    .enablePostCssLoader((options) => {
        options.config = {
            path: 'config/postcss.config.js'
        };
    })
    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[ext]'
    })
;

module.exports = Encore.getWebpackConfig();
