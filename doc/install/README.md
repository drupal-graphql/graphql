# Installing

The simple way to install the module and its dependency is to use Composer, assuming you installed Drupal itself as described on [Using Composer to manage Drupal site dependencies](https://www.drupal.org/node/2718229).

## Preparing your Drupal instance

If you used the `drupal-composer/drupal-project` template, skip directly to step
"[Adding the module](#adding-the-module)" below.

Otherwise, if you used the `drupal/drupal` method - e.g. by
just cloning Drupal from its git repository - you have to add the
"[installer paths]" configuration, by adding this fragment to your
`composer.json` at the first level of the file:

    "extra": {
        "installer-paths": {
            "modules/contrib/{$name}": ["type:drupal-module"],
            "modules/custom/{$name}": ["type:drupal-custom-module"],
            "profiles/contrib/{$name}": ["type:drupal-profile"],
            "themes/contrib/{$name}": ["type:drupal-theme"],
            "themes/custom/{$name}": ["type:drupal-custom-theme"]
        }
    }

This will ensure that the GraphQL module is correctly installed in the
`modules/contrib/graphql` directory. Without this configuration, the module
would default to being installed in `modules/graphql`, which is not the
recommended practice.

[installer paths]: https://www.drupal.org/node/2718229#installer-dirs


## Adding the module

Until the module is stable enough to have releases on drupal.org, its
development versions are located on
[https://github.com/fubhy/graphql-drupal](https://github.com/fubhy/graphql-drupal)
which has no entry on [Packagist](https://packagist.org/search/). This means you
need to tell Composer whence to download the module.

To do this, edit your `composer.json` file, and add the Drupal GraphQL
repository location, at the first level of the file:

    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/fubhy/graphql-drupal"
        }
    ],

Ensure you can run [Composer](https://getcomposer.org/download/). Then, go to
the command line, and type (the `$ ` is your shell prompt, do not type it):

    $ cd (the_project_root_directory)
    $ composer require drupal/graphql:8.x-3.x-dev

This will take some time, and end by something like:

    [...snip...]
    Writing lock file
    Generating autoload files
    > Drupal\Core\Composer\Composer::preAutoloadDump
    > Drupal\Core\Composer\Composer::ensureHtaccess
    $

At this point, all is ready, you can just enable the GraphQL module from the
Drupal UI, Drush or Drupal console.


## Checking installation

You can check that the installation process succeeded, by ensuring the module
and its dependency are present:

    ls -d vendor/youshido/graphql web/modules/contrib/graphql

This should return something like this:

    vendor/youshido/graphql  web/modules/contrib/graphql

If you aren't using the `drupal-composer/drupal-project` boilerplate, change `web/modules/contrib/graphql` to `modules/contrib/graphql`.

If you do not obtain these two paths, something went wrong with the Composer
installation process, and you will need to fix it before you can enable the
module.
