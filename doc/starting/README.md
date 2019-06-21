# Installation

The module requires installation via `composer`in order to pull in the dependencies for the module to work ,most notably the [webonyx/graphql-php library](https://github.com/webonyx/graphql-php).

1. Install the module running `composer require drupal/graphql dev-8.x-4.x`. Currently in development the version should be available as 8.x-4.x soon.
2. Enable the GraphQL module in extensions.
3. Login and navigate to `/admin/config/graphql` to create a new server.
4. At this point you can either start with the test schema provided by the module (see the Introduction section) or start right away making your own custom schema as we will describe in the next section.

