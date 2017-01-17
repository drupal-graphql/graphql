<?php

namespace Drupal\graphql_example\GraphQL\Type;

use Drupal\graphql\GraphQL\Type\AbstractObjectType;
use Drupal\graphql_example\GraphQL\Field\MenuTreeItem\MenuTreeItemLinkField;
use Drupal\graphql_example\GraphQL\Field\MenuTreeItem\MenuTreeItemSubtreeField;

class MenuTreeItemType extends AbstractObjectType {

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    $config->addField(new MenuTreeItemLinkField());
    $config->addField(new MenuTreeItemSubtreeField());
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'MenuTreeItem';
  }
}
