<?php

namespace Drupal\graphql\Plugin\GraphQL\Fields;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\graphql\GraphQL\Field\Field;
use Drupal\graphql\GraphQL\SecureFieldInterface;
use Drupal\graphql\GraphQL\ValueWrapperInterface;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Traits\ArgumentAwarePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\NamedPluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Youshido\GraphQL\Exception\ResolveException;
use Youshido\GraphQL\Execution\DeferredResolver;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\ListType\ListType;

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

      $this->definition = new Field($this, $schemaBuilder, $config);
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
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    // If not resolving in a trusted environment, check if the field is secure.
    $container = $info->getExecutionContext()->getContainer();
    if ($container->has('secure') && !$container->get('secure') && !$this->isSecure()) {
      throw new ResolveException(sprintf("Unable to resolve insecure field '%s'.", $info->getField()->getName()));
    }

    return $this->resolveDeferred([$this, 'resolveValues'], $value, $args, $info);
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveDeferred(callable $callback, $value, array $args, ResolveInfo $info) {
    $result = $callback($value, $args, $info);
    if (is_callable($result)) {
      return new DeferredResolver(function () use ($result, $args, $info, $value) {
        return $this->resolveDeferred($result, $value, $args, $info);
      });
    }

    // Extract the result array.
    $result = iterator_to_array($result);

    // Commit the cache dependencies into the processor's cache collector.
    if ($dependencies = $this->getCacheDependencies($result, $value, $args, $info)) {
      $container = $info->getExecutionContext()->getContainer();
      if ($container->has('metadata') && $metadata = $container->get('metadata')) {
        if ($metadata instanceof RefinableCacheableDependencyInterface) {
          array_walk($dependencies, [$metadata, 'addCacheableDependency']);
        }
      }
    }

    return $this->unwrapResult($result, $info);
  }

  /**
   * Unwrap the resolved values.
   *
   * @param array $result
   *   The resolved values.
   * @param \Youshido\GraphQL\Execution\ResolveInfo $info
   *   The resolve info object.
   *
   * @return mixed
   *   The extracted values (an array of values in case this is a list, an
   *   arbitrary value if it isn't).
   */
  protected function unwrapResult($result, ResolveInfo $info) {
    $result = array_map(function ($item) {
      return $item instanceof ValueWrapperInterface ? $item->getValue() : $item;
    }, $result);

    // If this is a list, return the result as an array.
    $type = $info->getReturnType()->getNullableType();
    if ($type instanceof ListType) {
      return $result;
    }

    return !empty($result) ? reset($result) : NULL;
  }

  /**
   * Retrieve the list of cache dependencies for a given value and arguments.
   *
   * @param array $result
   *   The result of the field.
   * @param mixed $parent
   *   The parent value.
   * @param array $args
   *   The arguments passed to the field.
   * @param \Youshido\GraphQL\Execution\ResolveInfo $info
   *   The resolve info object.
   *
   * @return array
   *   A list of cacheable dependencies.
   */
  protected function getCacheDependencies(array $result, $parent, array $args, ResolveInfo $info) {
    return array_filter($result, function ($item) {
      return $item instanceof CacheableDependencyInterface;
    });
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
