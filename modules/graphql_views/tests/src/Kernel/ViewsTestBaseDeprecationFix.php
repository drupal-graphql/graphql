<?php

namespace Drupal\Tests\graphql_views\Kernel;

use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;


if (version_compare(\Drupal::VERSION, '8.4', '<')) {
  abstract class ViewsTestBaseDeprecationFix extends GraphQLFileTestBase {
    use \Drupal\taxonomy\Tests\TaxonomyTestTrait;
  }

} else {

  abstract class ViewsTestBaseDeprecationFix extends GraphQLFileTestBase {
    use \Drupal\Tests\taxonomy\Functional\TaxonomyTestTrait;
  }
}
