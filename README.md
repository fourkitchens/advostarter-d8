This pile of Drupal is the canonical source of our custom upstream on Pantheon. Any updates to the upstream happen here. 👋

## Create a new project using this upstream

Create a new site on Pantheon, and choose Advostarter D8 for your upstream.

## Add or update a contrib project

New modules for our upstream should be added using Composer. The following handy table is from Lightning's documentation.

| Task                                            | Drush                                         | Composer                                          |
|-------------------------------------------------|-----------------------------------------------|---------------------------------------------------|
| Installing a contrib project (latest version)   | ```drush pm-download PROJECT```               | ```composer require drupal/PROJECT```         |
| Installing a contrib project (specific version) | ```drush pm-download PROJECT-8.x-1.0-beta3``` | ```composer require drupal/PROJECT:1.0.0-beta3``` |
| Updating all contrib projects and Drupal core   | ```drush pm-update```                         | ```composer update```                             |
| Updating a single contrib project               | ```drush pm-update PROJECT```                 | ```composer update drupal/PROJECT```              |
| Updating Drupal core                            | ```drush pm-update drupal```                  | ```composer update drupal/core```                 |

### Specifying a version
you can specify a version from the command line with:

    $ composer require drupal/<modulename>:<version> 

For example:

    $ composer require drupal/ctools:3.0.0-alpha26
    $ composer require drupal/token:1.x-dev 

In these examples, the composer version 3.0.0-alpha26 maps to the drupal.org version 8.x-3.0-alpha26 and 1.x-dev maps to 8.x-1.x branch on drupal.org.

If you specify a branch, such as 1.x you must add -dev to the end of the version.

## Push updates downstream

For Pantheon to show new commits from this project as mergeable updates to downstream repos, you'll need to create a new Release by adding a Git tag. Create a new release after running any security updates.

## Localhost setup

We're following Lightning's directory structure here, so we have a nested docroot. To get this running locally, you'll need to either specify the docroot (`/web`) in your server configuration file (e.g httpd-vhosts.conf) or follow [these directions](https://www.thinktandem.io/blog/2017/05/20/using-pantheon-s-nested-docroot-with-kalabox/) for Kalabox.
