const mix = require('laravel-mix');
const path = require('path')

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */


// It seems that if we don't set this, then we get errors from the likes
// of font-awesome that can't load their assets at run time
// @see https://github.com/laravel-mix/laravel-mix/issues/1136
mix.setResourceRoot('../');

// Javascript
mix.js('resources/js/app.js', 'public/js')
    .vue()
    .sourceMaps();

mix.js('resources/js/voltage.js', 'public/js')
    .vue()
    .sourceMaps();

mix.js('resources/js/building.js', 'public/js')
    .vue()
    .sourceMaps();

mix.js('resources/js/report.js', 'public/js')
    .vue()
    .sourceMaps();

mix.copy('resources/js/meters.js','public/js/meters.js');
mix.copy('resources/js/epics2web.js','public/js/epics2web.js');
mix.copy('resources/js/odometer.min.js','public/js/odometer.min.js');
mix.copy('resources/js/canvasjs-1.9.10.min.js','public/js/canvasjs-1.9.10.min.js');
mix.copy('resources/js/jquery.dynameter.js','public/js/jquery.dynameter.js');
mix.copy('resources/js/jquery.maphilight.min.js','public/js/jquery.maphilight.min.js');

// SASS and CSS
mix.sass('resources/sass/app.scss', 'public/css');
mix.copy('resources/css/ionicons-2.0.1.min.css','public/css/ionicons-2.0.1.min.css');
mix.copy('resources/css/jquery.dynameter.css','public/css/jquery.dynameter.css');
mix.copy('resources/css/odometer-theme-plaza.css','public/css/odometer-theme-plaza.css');


// Directories
mix.copy('resources/font-awesome-4.7.0','public/font-awesome-4.7.0');
mix.copy('resources/img','public/img');
// mix.copy('resources/dt-1.10.15','public/dt-1.10.15');
//mix.copy('node_modules/flatpickr/dist', 'public/flatpickr-dist');
//mix.copy('node_modules/jquery-ui-dist', 'public/jquery-ui-dist');
// mix.copy('node_modules/select2/dist', 'public/select2-dist');
