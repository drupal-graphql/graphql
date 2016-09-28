<?php

namespace Drupal\graphql\GraphQL\Field;

use Youshido\GraphQL\Field\AbstractField;

abstract class FieldBase extends AbstractField {
  /**
   * The type that this field resolves to.
   *
   * @var \Youshido\GraphQL\Type\TypeInterface;
   */
  protected $typeCache;

  /**
   * The name of this field.
   *
   * @var string
   */
  protected $nameCache;

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->typeCache ?: ($this->typeCache = $this->getConfigValue('type'));
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->nameCache ?: ($this->nameCache = $this->getConfigValue('name'));
  }
}