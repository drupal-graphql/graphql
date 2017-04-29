<?php

namespace Drupal\graphql_test_custom_schema\Types;

use Drupal\graphql_test_custom_schema\Fields\ArticleTitleField;
use Drupal\graphql_test_custom_schema\Fields\NodeIdField;
use Youshido\GraphQL\Type\Object\AbstractObjectType;

class ArticleType extends AbstractObjectType {

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    $config->addField(new NodeIdField());
    $config->addField(new ArticleTitleField());
  }

  public function getInterfaces() {
    return [
      new NodeInterfaceType(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'Article';
  }
}
