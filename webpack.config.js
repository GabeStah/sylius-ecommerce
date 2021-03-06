const path = require('path');
const Encore = require('@symfony/webpack-encore');

const syliusBundles = path.resolve(
  __dirname,
  'vendor/sylius/sylius/src/Sylius/Bundle/'
);
const uiBundleScripts = path.resolve(
  syliusBundles,
  'UiBundle/Resources/private/js/'
);
const uiBundleResources = path.resolve(
  syliusBundles,
  'UiBundle/Resources/private/'
);

// Shop config
Encore.setOutputPath('public/build/shop/')
  .setPublicPath('/build/shop')
  .addEntry('shop-entry', './assets/shop/entry.js')
  .disableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .enableSassLoader();

const shopConfig = Encore.getWebpackConfig();

shopConfig.resolve.alias['sylius/ui'] = uiBundleScripts;
shopConfig.resolve.alias['sylius/ui-resources'] = uiBundleResources;
shopConfig.resolve.alias['sylius/bundle'] = syliusBundles;
shopConfig.name = 'shop';

Encore.reset();

// Admin config
Encore.setOutputPath('public/build/admin/')
  .setPublicPath('/build/admin')
  .addEntry('admin-entry', './assets/admin/entry.js')
  // Copy assets to proper path referenced by /admin/pages view
  .copyFiles({
    from: './vendor/bitbag/cms-plugin/src/Resources/public',
    to:
      '../../bundles/_themes/sylius/bootstrap-theme/bitbagsyliuscmsplugin/[path][name].[ext]'
  })
  .disableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .enableSassLoader();

const adminConfig = Encore.getWebpackConfig();

adminConfig.resolve.alias['sylius/ui'] = uiBundleScripts;
adminConfig.resolve.alias['sylius/ui-resources'] = uiBundleResources;
adminConfig.resolve.alias['sylius/bundle'] = syliusBundles;
adminConfig.externals = Object.assign({}, adminConfig.externals, {
  window: 'window',
  document: 'document'
});
adminConfig.name = 'admin';

Encore.reset();

// Install theme to public accessible path
Encore.setOutputPath('public/bootstrap-theme')
  .setPublicPath('/bootstrap-theme')
  .addEntry('app', './themes/BootstrapTheme/assets/app.js')
  .copyFiles([
    {
      from: './public/bundles/fosckeditor',
      to: 'fosckeditor/[path][name].[ext]'
    }
  ])
  .disableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .enableSassLoader();

const bootstrapTheme = Encore.getWebpackConfig();
bootstrapTheme.name = 'bootstrapTheme';

module.exports = [shopConfig, adminConfig, bootstrapTheme];
