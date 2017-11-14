<?php

namespace Drupal\graphql\Plugin\GraphQL\Interfaces;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\GraphQL\Type\InterfaceType;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\FieldablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\NamedPluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;

/**
 * Base class for GraphQL interface plugins.
 */
abstract class InterfacePluginBase extends PluginBase implements TypeSystemPluginInterface {
  use CacheablePluginTrait;
  use NamedPluginTrait;
  use FieldablePluginTrait;

  /**
   * The type instance.
   *
   * @var \Drupal\graphql\GraphQL\Type\InterfaceType
   */
  protected $definition;

  /**
   * {@inheritdoc}
   */
  public function getDefinition(PluggableSchemaBuilderInterface $schemaBuilder) {
    if (!isset($this->definition)) {
      $this->definition = new InterfaceType($this, [
        'name' => $this->buildName(),
        'description' => $this->buildDescription(),
      ]);

      $this->definition->addFields($this->buildFields($schemaBuilder));
    }

    return $this->definition;
  }

}
