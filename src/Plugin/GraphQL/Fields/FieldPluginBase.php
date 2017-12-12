<?php

namespace Drupal\graphql\Plugin\GraphQL\Fields;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\GraphQL\Batching\BatchedFieldInterface;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Drupal\graphql\GraphQL\Field\BatchedField;
use Drupal\graphql\GraphQL\Field\Field;
use Drupal\graphql\GraphQL\SecureFieldInterface;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Traits\ArgumentAwarePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\NamedPluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Youshido\GraphQL\Execution\DeferredResolver;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Base class for field plugins.
 */
abstract class FieldPluginBase extends PluginBase implements TypeSystemPluginInterface, SecureFieldInterface {
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

      $config = [
        'name' => $this->buildName(),
        'description' => $this->buildDescription(),
        'type' => $this->buildType($schemaBuilder),
        'args' => $this->buildArguments($schemaBuilder),
        'isDeprecated' => !empty($definition['deprecated']),
        'deprecationReason' => !empty($definition['deprecated']) ? !empty($definition['deprecated']) : '',
      ];

      if ($this instanceof BatchedFieldInterface) {
        $this->definition = new BatchedField($this, $this->isSecure(), $config);
      }
      else {
        $this->definition = new Field($this, $this->isSecure(), $config);
      }
    }

    return $this->definition;
  }

  /**
   * {@inheritdoc}
   */
  public function isSecure() {
    return isset($this->getPluginDefinition()['secure']) && $this->getPluginDefinition()['secure'];
  }

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
  public function resolve($value, array $args, ResolveInfo $info) {
    if ($this instanceof BatchedFieldInterface) {
      $result = $this->getBatchedFieldResolver($value, $args, $info)->add($this, $value, $args, $info);
      return new DeferredResolver(function() use ($result, $args, $info, $value) {
        $result = iterator_to_array($this->resolveValues($result(), $args, $info));
        return $this->cacheable($result, $value, $args);
      });
    }
    return $this->resolveDeferred([$this, 'resolveValues'], $value, $args, $info);
  }

  /**
   * {@inheritdoc}
   */
  public function resolveDeferred(callable $callback, $value, array $args, ResolveInfo $info) {
    $result = $callback($value, $args, $info);
    if (is_callable($result)) {
      return new DeferredResolver(function () use ($result, $args, $info, $value) {
        return $this->resolveDeferred($result, $value, $args, $info);
      });
    }
    return $this->cacheable(iterator_to_array($result), $value, $args);
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

}
