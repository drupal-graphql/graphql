<?php

namespace Drupal\graphql\Plugin\GraphQL\Fields;

use Drupal\graphql\GraphQL\Batching\BatchedFieldInterface;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Drupal\graphql\GraphQL\SecureFieldInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Traits\ArgumentAwarePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\NamedPluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\PluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Youshido\GraphQL\Config\Field\FieldConfig;
use Youshido\GraphQL\Execution\DeferredResolver;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\AbstractField;

/**
 * Base class for graphql field plugins.
 */
abstract class FieldPluginBase extends AbstractField implements TypeSystemPluginInterface, SecureFieldInterface {
  use PluginTrait;
  use CacheablePluginTrait;
  use NamedPluginTrait;
  use ArgumentAwarePluginTrait;

  /**
   * Dummy implementation for `getBatchId` in `BatchedFieldInterface`.
   *
   * This provides an empty implementation of `getBatchId` in case the subclass
   * implements `BatchedFieldInterface`. In may cases this will suffice since
   * the batches are already grouped by the class implementing `resolveBatch`.
   * `getBatchId` is only necessary for cases where batch grouping depends on
   * runtime arguments.
   *
   * @param mixed $parent
   *   The parent value in the result tree.
   * @param array $arguments
   *   The list of arguments.
   * @param ResolveInfo $info
   *   The graphql resolve info object.
   *
   * @return string
   *   The batch key.
   */
  public function getBatchId($parent, array $arguments, ResolveInfo $info) {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition) {
    $this->constructPlugin($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    if ($this instanceof BatchedFieldInterface) {
      $result = $this->getBatchedFieldResolver()->add($this, $value, $args, $info);
      return new DeferredResolver(function() use ($result, $args, $info, $value) {
        $result = iterator_to_array($this->resolveValues($result(), $args, $info));
        return $this->cacheable($result, $value, $args);
      });
    }
    $result = iterator_to_array($this->resolveValues($value, $args, $info));
    return $this->cacheable($result, $value, $args);
  }

  /**
   * Wrap the result in a CacheableValue.
   *
   * @param mixed $result
   *   The field result.
   * @param mixed $value
   *   The parent value.
   * @param array $args
   *   The field arguments.
   *
   * @return CacheableValue
   *   The cacheable value.
   */
  protected function cacheable($result, $value, array $args) {
    if ($this->getPluginDefinition()['multi']) {
      return new CacheableValue($result, $this->getCacheDependencies($result, $value, $args));
    }

    if ($result) {
      return new CacheableValue(reset($result), $this->getCacheDependencies($result, $value, $args));
    }

    return new CacheableValue(NULL, $this->getCacheDependencies($result, $value, $args));
  }

  /**
   * Retrieve the list of cache dependencies for a given value and arguments.
   *
   * @param mixed $result
   *   The result of the field.
   * @param mixed $parent
   *   The parent value.
   * @param array $args
   *   The arguments passed to the field.
   *
   * @return array
   *   A list of cacheable dependencies.
   */
  protected function getCacheDependencies($result, $parent, array $args) {
    // Default implementation just returns the value itself.
    return [$result];
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
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    // Allow overriding this class without having to declare this method.
    yield NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfig(SchemaBuilderInterface $schemaManager) {
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

  /**
   * {@inheritdoc}
   */
  public function isSecure() {
    return isset($this->getPluginDefinition()['secure']) && $this->getPluginDefinition()['secure'];
  }

}
