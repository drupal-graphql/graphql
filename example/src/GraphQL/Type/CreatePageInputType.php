<?php

namespace Drupal\graphql_example\GraphQL\Type;

use Youshido\GraphQL\Field\Field;
use Youshido\GraphQL\Type\InputObject\AbstractInputObjectType;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Scalar\StringType;

class CreatePageInputType extends AbstractInputObjectType {

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    $config->addField(new Field([
      'name' => 'title',
      'type' => new NonNullType(new StringType()),
    ]));

    $config->addField(new Field([
      'name' => 'body',
      'type' => new StringType(),
    ]));
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'CreatePageInput';
  }
}
