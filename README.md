[![Build Status](https://travis-ci.org/contao-community-alliance/contao-polyfill-bundle.png)](https://travis-ci.org/contao-community-alliance/contao-polyfill-bundle)
[![Latest Version tagged](http://img.shields.io/github/tag/contao-community-alliance/contao-polyfill-bundle.svg)](https://github.com/contao-community-alliance/contao-polyfill-bundle/tags)
[![Latest Version on Packagist](http://img.shields.io/packagist/v/contao-community-alliance/contao-polyfill-bundle.svg)](https://packagist.org/packages/contao-community-alliance/contao-polyfill-bundle)
[![Installations via composer per month](http://img.shields.io/packagist/dm/contao-community-alliance/contao-polyfill-bundle.svg)](https://packagist.org/packages/contao-community-alliance/contao-polyfill-bundle)

Contao Polyfill Bundle
======================

This bundle allows you to use Contao features in earlier versions.

For every single feature there is the possibility to deactivate it. See the corresponding description.

The following functions are backported in the package:


Tagged Hooks
------------

This feature is automatically loaded in Contao versions less than 4.5.

What this feature entails, you can read [here][tagged_hooks_doc].

If you want to disable this, add the following to your config.yml.

```yaml
cca_polyfill45:
    tagged_hooks: false

```


Asset
-----

This feature is automatically loaded in Contao versions less than 4.5.

What this feature entails, you can read [here][asset_doc].

The function to include asset `$this->asset()` via the template is not supported. 
You can use the insert tag `{{asset::path}}` as a replacement.

If you want to disable this, add the following to your config.yml.

```yaml
cca_polyfill45:
    asset: false

```


[tagged_hooks_doc]: https://github.com/contao/core-bundle/commit/e700e191a19c68d67cfd1b0ee694d60c5f29baa0 "Read on github.com/contao/core-bundle"
[asset_doc]: https://github.com/contao/core-bundle/commit/eed0aea3682b2bba28ed26d796b18605b459445e "Read on github.com/contao/core-bundle"
