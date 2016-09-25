<?php

namespace Drupal\graphql_test_custom_schema\Types;

use Drupal\graphql\GraphQL\Relay\Field\GlobalIdField;
use Drupal\graphql_test_custom_schema\Fields\ArticleTitleField;
use Youshido\GraphQL\Relay\NodeInterfaceType;
use Youshido\GraphQL\Type\Object\AbstractObjectType;

class ArticleType extends AbstractObjectType {

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    $config->addField(new GlobalIdField('article'));
    $config->addField(new ArticleTitleField());
  }

  /**
   * {@inheritdoc}
   */
  public function getInterfaces() {
    return [new NodeInterfaceType()];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'Article';
  }
}
