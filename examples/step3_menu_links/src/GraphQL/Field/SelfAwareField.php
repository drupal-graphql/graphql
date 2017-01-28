<?php

namespace Drupal\graphql_example\GraphQL\Field;

use Youshido\GraphQL\Field\AbstractField;

abstract class SelfAwareField extends AbstractField {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $config = []) {
    parent::__construct([]);
  }
}