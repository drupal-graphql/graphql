<?php

namespace Drupal\graphql_core\GraphQL;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\graphql\GraphQL\CacheableValue;
use Drupal\graphql_core\GraphQL\Traits\ArgumentAwarePluginTrait;
use Drupal\graphql_core\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql_core\GraphQL\Traits\NamedPluginTrait;
use Drupal\graphql_core\GraphQL\Traits\PluginTrait;
use Drupal\graphql_core\GraphQLPluginInterface;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Youshido\GraphQL\Config\Field\FieldConfig;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\AbstractField;

/**
 * Base class for graphql field plugins.
 */
abstract class FieldPluginBase extends AbstractField implements GraphQLPluginInterface, CacheableDependencyInterface {
  use PluginTrait;
  use CacheablePluginTrait;
  use NamedPluginTrait;
  use ArgumentAwarePluginTrait;

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    $result = iterator_to_array($this->resolveValues($value, $args, $info));
    if ($this->getPluginDefinition()['multi']) {
      return new CacheableValue($result, $this->getCacheDependencies($value, $args, $result));
    }
    else {
      if ($result) {
        return new CacheableValue(reset($result), $this->getCacheDependencies($value, $args, $result));
      }
      else {
        return new CacheableValue(NULL, $this->getCacheDependencies($value, $args, $result));
      }
    }
  }

  /**
   * Retrieve the list of cache dependencies for a given value and arguments.
   *
   * @param mixed $result
   *   The result of the field.
   * @param mixed $value
   *   The parent value.
   * @param array $args
   *   The arguments passed to the field.
   *
   * @return array
   *   A list of cacheable dependencies.
   */
  protected function getCacheDependencies($result, $value, array $args) {
    // Default implementation just returns the value itself.
    return isset($result) ? [$result, $value] : [$value];
  }

  /**
   * Retrieve the list of field values.
   *
   * Always returns a list of field values. Even for single value fields.
   * Single/multi field handling is responsibility of the base class.
   *
   * @param mixed $value
   *   The current object value.
   * @param array $args
   *   Field arguments.
   * @param \Youshido\GraphQL\Execution\ResolveInfo $info
   *   The resolve info object.
   *
   * @return \Generator
   *   The value generator.
   */
  protected abstract function resolveValues($value, array $args, ResolveInfo $info);

  /**
   * {@inheritdoc}
   */
  public function buildConfig(GraphQLSchemaManagerInterface $schemaManager) {
    $this->config = new FieldConfig([
      'name' => $this->buildName(),
      'type' => $this->buildType($schemaManager),
      'args' => $this->buildArguments($schemaManager),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->config->getType();
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->buildName();
  }

  /**
   * {@inheritdoc}
   */
  public function build(FieldConfig $config) {
    // May be overridden, but not required any more.
  }
}
