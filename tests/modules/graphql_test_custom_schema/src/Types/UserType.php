<?php

namespace Drupal\graphql_test_custom_schema\Types;

use Drupal\graphql\GraphQL\Relay\Field\GlobalIdField;
use Drupal\graphql\GraphQL\Type\AbstractObjectType;
use Drupal\graphql_test_custom_schema\Fields\UsernameField;

/**
 * Defines user data type.
 */
class UserType extends AbstractObjectType {

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    $config->addField(new GlobalIdField('user'));
    $config->addField(new UsernameField());
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'User';
  }

}
