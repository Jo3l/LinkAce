const mix = require('laravel-mix');

mix.options({
  processCssUrls: false
});

mix.disableNotifications();

mix.setPublicPath('public');

mix.js('resources/assets/js/app.js', 'assets/dist/js');
mix.js('resources/assets/js/fontawesome.js', 'assets/dist/js')
  .version();

mix.combine([
  'node_modules/jquery/dist/jquery.min.js',
  'node_modules/bootstrap/dist/js/bootstrap.min.js',
  'node_modules/select2/dist/js/select2.min.js'
], 'public/assets/dist/js/dependencies.js')
  .version();

mix.sass('resources/assets/sass/app.scss', 'assets/dist/css')
  .sass('resources/assets/sass/app-dark.scss', 'assets/dist/css')
  .sass('resources/assets/sass/loader.scss', 'assets/dist/css')
  .version();
