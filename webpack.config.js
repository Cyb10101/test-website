var Encore = require('@symfony/webpack-encore');
var webpack = require('webpack');

Encore
    .setOutputPath('public/build/')

    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')
    .setManifestKeyPrefix('build')

    // will create public/build/app.js and public/build/app.css
    .addEntry('app', './assets/js/app.js')
    // .addStyleEntry('app', './assets/css/app.scss')

    .enableSassLoader()
    .autoProvidejQuery()

    // .autoProvideVariables({ Popper: ['popper.js', 'default'] })

    .addPlugin(new webpack.DefinePlugin({
        'process.isProduction': Encore.isProduction()
    }))

    .addPlugin(new webpack.ProvidePlugin({
        // '$': 'jquery',
        // 'jQuery': 'jquery',
        Popper: ['popper.js', 'default']
        // CKEDITOR: ['ckeditor', 'default']
    }))

    .enableSourceMaps(!Encore.isProduction())
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
;

// export the final configuration
module.exports = Encore.getWebpackConfig();
