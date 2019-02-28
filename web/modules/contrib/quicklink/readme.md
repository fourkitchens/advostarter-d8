# Quicklink Drupal Module

## What is Quicklink?

The Drupal Quicklink module loads the
[Quicklink library](https://github.com/GoogleChromeLabs/quicklink) and provides
a Drupal administrative interface to configure it.


## Installation


### Manual Installation
1. Download the Drupal module and extract it to your modules folder.
2. Because of licensing restrictions, the Quicklink JavaScript library cannot
be hosted on Drupal.org.

By default this module will load the Quicklink JavaScript library from a CDN at
`https://unpkg.com/quicklink@1.0.0/dist/quicklink.umd.js`.

If you place a copy of this file into your local filesystem at
`/libraries/quicklink/dist/quicklink.umd.js`, this module will serve the local copy 
instead of the CDN copy.


### Composer (recommended)
If you manage your site with composer, and would like to install this module
with it, please verify the following steps. If you used the [Drupal composer
template](https://github.com/drupal-composer/drupal-project) to setup your
project, you most likely have this set up already.

1. Add or verify that `asset-packagist` is in the repositories section of your
composer.json file:

```
    "repositories": [
        {
            "type": "composer",
            "url": "https://packages.drupal.org/8"
        },
        {
            "type": "composer",
            "url": "https://asset-packagist.org"
        },
    }
```

2. Add or verify that `type:npm-asset`  is in the `extra` > `installer-path` >
`web/libraries/{$name}` section of your composer.json file:

```
    "extra": {
        "installer-paths": {
            "web/libraries/{$name}": [
                "type:drupal-library",
                "type:bower-asset",
                "type:npm-asset"
            ],
        }
    }
```

3. Add or verify that you have `npm-asset` in the `extra` > `installer-types`
section of your composer.json file:

```
    "installer-types": ["npm-asset", "bower-asset"],
```

4. Save your composer.json file.

5. Back at the command line, install the library with
`composer require oomphinc/composer-installers-extender npm-asset/quicklink`

6. Install the module with `composer require drupal/quicklink`

7. Enable the module at `admin/modules` or by running `drush en -y quicklink`


## How do I access the Quicklink admin interface?

The Quicklink module admin interface is located at
`admin/config/development/performance/quicklink`, and can be accessed from a tab on the development
/ performance settings page.


## What browsers does this support?

Without polyfills:
Chrome, Firefox.

With [Intersection Observer polyfill](https://github.com/w3c/IntersectionObserver/tree/master/polyfill):
Safari, Edge


## How to ignore certain links.

You can tell Quicklink to ignore certain links by adding them into the ignore
list at `admin/config/development/performance/quicklink`.

In addition, you can add a `noprefetch` attribute onto the `<a>` tag to tell
the library not to prefetch this link.
