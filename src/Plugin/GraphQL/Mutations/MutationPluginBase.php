<?php

namespace Drupal\graphql\Plugin\GraphQL\Mutations;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\GraphQL\Field\Field;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Traits\ArgumentAwarePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\NamedPluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;

/**
 * Base class for graphql mutation plugins.
 */
abstract class MutationPluginBase extends PluginBase implements TypeSystemPluginInterface {
  use CacheablePluginTrait;
  use NamedPluginTrait;
  use ArgumentAwarePluginTrait;

  /**
   * The field instance.
   *
   * @var \Drupal\graphql\GraphQL\Field\Field
   */
  protected $definition;

  /**
   * {@inheritdoc}
   */
  public function getDefinition(PluggableSchemaBuilderInterface $schemaBuilder) {
    if (!isset($this->definition)) {
      $definition = $this->getPluginDefinition();

      $this->definition = new Field($this, TRUE, [
        'name' => $this->buildName(),
        'description' => $this->buildDescription(),
        'type' => $this->buildType($schemaBuilder),
        'args' => $this->buildArguments($schemaBuilder),
        'isDeprecated' => !empty($definition['deprecated']),
        'deprecationReason' => !empty($definition['deprecated']) ? !empty($definition['deprecated']) : '',
      ]);
    }

    return $this->definition;
  }

}
