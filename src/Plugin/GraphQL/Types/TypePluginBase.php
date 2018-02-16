<?php

namespace Drupal\graphql\Plugin\GraphQL\Types;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilder;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\DescribablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

abstract class TypePluginBase extends PluginBase implements TypeSystemPluginInterface {
  use CacheablePluginTrait;
  use DescribablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(PluggableSchemaBuilder $builder, $definition, $id) {
    return new ObjectType([
      'fields' => function () use ($builder, $definition) {
        $fields = $builder->getFieldsByType($definition['name']);

        if (!empty($definition['interfaces'])) {
          $inherited = array_map(function ($name) use ($builder) {
            return $builder->getFieldsByType($name);
          }, $definition['interfaces']);

          $inherited = call_user_func_array('array_merge', $inherited);
          return array_merge($inherited, $fields);
        }

        return $fields;
      },
      'interfaces' => function () use ($builder, $definition) {
        return array_map(function ($name) use ($builder) {
          return $builder->getTypeByName($name);
        }, $definition['interfaces']);
      },
      'isTypeOf' => function ($value, $context, ResolveInfo $info) use ($builder, $id) {
        $instance = $builder->getPluginInstance(GRAPHQL_TYPE_PLUGIN, $id);
        return $instance->applies($value, $context, $info);
      },
    ] + $definition);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinition() {
    $definition = $this->getPluginDefinition();

    return [
      'name' => $definition['name'],
      'description' => $this->buildDescription($definition),
      'interfaces' => $this->buildInterfaces($definition),
    ];
  }

  /**
   * @param $definition
   *
   * @return array
   */
  protected function buildInterfaces($definition) {
    return array_unique($definition['interfaces']);
  }

  /**
   * Checks whether this type applies to a given object.
   *
   * @param mixed $object
   *   The object to check against.
   * @param mixed $context
   *   The execution context.
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The resolve info object.
   *
   * @return null|bool
   *   TRUE if this type applies to the given object or FALSE if it doesn't.
   */
  public function applies($object, $context, ResolveInfo $info) {
    return NULL;
  }

}
