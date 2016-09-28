<?php

namespace Drupal\graphql_test_custom_schema\Types;

use Drupal\graphql\GraphQL\Relay\Field\GlobalIdField;
use Drupal\graphql_test_custom_schema\Fields\ArticleTitleField;
use Youshido\GraphQL\Relay\NodeInterfaceType;
use Youshido\GraphQL\Type\Object\AbstractObjectType;

class ArticleType extends AbstractObjectType {
  protected $interfaces;

  /**
   * ArticleType constructor.
   * @param array $config
   */
  public function __construct($config) {
    parent::__construct($config);

    foreach ($config['interfaces'] as $interface) {
      $interface->addPossibleType($this);
    }
  }

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
  public function getName() {
    return 'Article';
  }
}
