<?php

namespace Drupal\graphql\Plugin\GraphQL\Fields;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\graphql\GraphQL\ValueWrapperInterface;
use Drupal\graphql\Plugin\FieldPluginInterface;
use Drupal\graphql\Plugin\FieldPluginManager;
use Drupal\graphql\Plugin\SchemaBuilder;
use Drupal\graphql\Plugin\GraphQL\Traits\ArgumentAwarePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\DeprecatablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\DescribablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\TypedPluginTrait;
use GraphQL\Deferred;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ResolveInfo;

abstract class FieldPluginBase extends PluginBase implements FieldPluginInterface {
  use CacheablePluginTrait;
  use DescribablePluginTrait;
  use TypedPluginTrait;
  use ArgumentAwarePluginTrait;
  use DeprecatablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(SchemaBuilder $builder, FieldPluginManager $manager, $definition, $id) {
    return [
      'description' => $definition['description'],
      'deprecationReason' => $definition['deprecationReason'],
      'type' => $builder->processType($definition['type']),
      'args' => $builder->processArguments($definition['args']),
      'resolve' => function ($value, array $args, $context, ResolveInfo $info) use ($manager, $id) {
        $instance = $manager->createInstance($id);
        return $instance->resolve($value, $args, $context, $info);
      },
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinition() {
    $definition = $this->getPluginDefinition();

    return [
      'type' => $this->buildType($definition),
      'description' => $this->buildDescription($definition),
      'args' => $this->buildArguments($definition),
      'deprecationReason' => $this->buildDeprecationReason($definition),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, $context, ResolveInfo $info) {
    // If not resolving in a trusted environment, check if the field is secure.
    if (isset($context['secure']) && empty($context['secure'])) {
      $definition = $this->getPluginDefinition();
      if (empty($definition['secure'])) {
        throw new \Exception(sprintf("Unable to resolve insecure field '%s'.", $info->fieldName));
      }
    }

    return $this->resolveDeferred([$this, 'resolveValues'], $value, $args, $info);
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveDeferred(callable $callback, $value, array $args, ResolveInfo $info) {
    $result = $callback($value, $args, $info);
    if (is_callable($result)) {
      return new Deferred(function () use ($result, $args, $info, $value) {
        return $this->resolveDeferred($result, $value, $args, $info);
      });
    }

    // Extract the result array.
    $result = iterator_to_array($result);

    // TODO: Extract cache dependencies.

    return $this->unwrapResult($result, $info);
  }

  /**
   * Unwrap the resolved values.
   *
   * @param array $result
   *   The resolved values.
   * @param \GraphQL\Type\Definition\ResolveInfo $info
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
    $type = $info->returnType;
    if ($type instanceof ListOfType || ($type instanceof NonNull && $type->getWrappedType() instanceof ListOfType)) {
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
   * @param \GraphQL\Type\Definition\ResolveInfo $info
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
   * @param \GraphQL\Type\Definition\ResolveInfo $info
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
