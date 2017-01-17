<?php

namespace Drupal\graphql_example\GraphQL\Type;

use Drupal\graphql\GraphQL\Type\AbstractObjectType;
use Drupal\graphql_example\GraphQL\Field\MenuLink\MenuLinkDescriptionField;
use Drupal\graphql_example\GraphQL\Field\MenuLink\MenuLinkIsExpandedField;
use Drupal\graphql_example\GraphQL\Field\MenuLink\MenuLinkIsRoutedField;
use Drupal\graphql_example\GraphQL\Field\MenuLink\MenuLinkLabelField;
use Drupal\graphql_example\GraphQL\Field\MenuLink\MenuLinkPathField;

class MenuLinkType extends AbstractObjectType {

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    $config->addField(new MenuLinkLabelField());
    $config->addField(new MenuLinkDescriptionField());
    $config->addField(new MenuLinkPathField());
    $config->addField(new MenuLinkIsRoutedField());
    $config->addField(new MenuLinkIsExpandedField());
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'MenuLink';
  }
}
