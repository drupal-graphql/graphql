<?php

namespace Drupal\graphql_example\GraphQL\Type;

use Drupal\graphql\GraphQL\Type\AbstractObjectType;
use Drupal\graphql_example\GraphQL\Relay\Field\GlobalIdField;
use Drupal\graphql_example\GraphQL\Relay\Type\NodeInterfaceType;
use Youshido\GraphQL\Field\Field;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\Scalar\StringType;

class CreatePageResponseType extends AbstractObjectType {

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    $config->addField(new Field([
      'name' => 'page',
      'type' => new PageType(),
    ]));

    $config->addField(new Field([
      'name' => 'errors',
      'type' => new ListType(new StringType()),
    ]));
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'CreatePageResponse';
  }
}
