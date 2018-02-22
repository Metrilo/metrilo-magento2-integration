## Bump version
- module.xml
- composer.json

## Package the extension
ref: http://devdocs.magento.com/guides/v2.0/extension-dev-guide/package/package_module.html
`zip -r releases/metrilo-analytics_magento2_extension-1.1.0.zip ./ -x './.git/*' -x './magento2_validator.php' -x './.gitignore'`

## Upload it
Go to https://developer.magento.com/extension/extension/addNewVersionToExtension/id/22784/
