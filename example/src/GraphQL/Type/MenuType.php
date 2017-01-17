<?php

namespace Drupal\graphql_example\GraphQL\Type;

use Drupal\graphql\GraphQL\Type\AbstractObjectType;
use Drupal\graphql_example\GraphQL\Field\Menu\MenuDescriptionField;
use Drupal\graphql_example\GraphQL\Field\Menu\MenuLinksField;
use Drupal\graphql_example\GraphQL\Field\Menu\MenuNameField;
use Drupal\graphql_example\GraphQL\Relay\Field\GlobalIdField;
use Drupal\graphql_example\GraphQL\Relay\Type\NodeInterfaceType;

class MenuType extends AbstractObjectType {

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    $config->addField(new GlobalIdField('menu'));
    $config->addField(new MenuNameField());
    $config->addField(new MenuDescriptionField());
    $config->addField(new MenuLinksField());
  }

  /**
   * {@inheritdoc}
   */
  public function getInterfaces() {
    return [
      new NodeInterfaceType(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'Menu';
  }
}
