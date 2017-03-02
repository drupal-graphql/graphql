<?php

namespace Drupal\graphql_example\GraphQL\Type;

use Drupal\graphql\GraphQL\Type\AbstractObjectType;
use Drupal\graphql_example\GraphQL\Field\Page\PageBodyField;
use Drupal\graphql_example\GraphQL\Field\Page\PageNidField;
use Drupal\graphql_example\GraphQL\Field\Page\PageTitleField;
use Drupal\graphql_example\GraphQL\Relay\Field\GlobalIdField;
use Drupal\graphql_example\GraphQL\Relay\Type\NodeInterfaceType;

class PageType extends AbstractObjectType {

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    $config->addField(new GlobalIdField('page'));
    $config->addField(new PageNidField());
    $config->addField(new PageTitleField());
    $config->addField(new PageBodyField());
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
    return 'Page';
  }
}
