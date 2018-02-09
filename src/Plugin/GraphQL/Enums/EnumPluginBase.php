<?php

namespace Drupal\graphql\Plugin\GraphQL\Enums;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\GraphQL\Type\EnumType;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\NamedPluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;

/**
 * Base class for enum plugins.
 */
abstract class EnumPluginBase extends PluginBase implements TypeSystemPluginInterface {
  use NamedPluginTrait;
  use CacheablePluginTrait;

  /**
   * The type instance.
   *
   * @var \Drupal\graphql\GraphQL\Type\EnumType
   */
  protected $definition;

  /**
   * {@inheritdoc}
   */
  public function getDefinition(PluggableSchemaBuilderInterface $schemaBuilder) {
    if (!isset($this->definition)) {
      $this->definition = new EnumType($this, $schemaBuilder, [
        'name' => $this->buildName(),
        'description' => $this->buildDescription(),
        'values' => $this->buildValues($schemaBuilder),
      ]);
    }

    return $this->definition;
  }

  /**
   * Build the values for the enum.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface $schemaBuilder
   *   The schema builder.
   *
   * @return array
   *   The list of possible values for the enum.
   */
  public function buildValues(PluggableSchemaBuilderInterface $schemaBuilder) {
    $values = $this->getPluginDefinition()['values'];
    $output = [];

    foreach ($values as $value => $definition) {
      $item = [
        'value' => $value,
        'name' => is_array($definition) ? $definition['name'] : $definition,
      ];

      if (is_array($definition) && !empty($definition['description'])) {
        $item['description'] = $definition['description'];
      }

      $output[] = $item;
    }

    return $output;
  }

}
