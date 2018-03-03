<?php

namespace Drupal\graphql\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Component\Utility\SortArray;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\graphql\Plugin\FieldPluginManager;
use Drupal\graphql\Plugin\MutationPluginManager;
use Drupal\graphql\Plugin\TypePluginManagerAggregator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PluggableSchemaDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * @var
   */
  private $basePluginId;

  protected $fieldManager;

  protected $mutationManager;

  protected $typeManagers;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $basePluginId,
      $container->get('plugin.manager.graphql.field'),
      $container->get('plugin.manager.graphql.mutation'),
      $container->get('graphql.type_manager_aggregator')
    );
  }

  /**
   * SchemaPluginBase constructor.
   *
   * @param $basePluginId
   * @param \Drupal\graphql\Plugin\FieldPluginManager $fieldManager
   * @param \Drupal\graphql\Plugin\MutationPluginManager $mutationManager
   * @param \Drupal\graphql\Plugin\TypePluginManagerAggregator $typeManagers
   */
  public function __construct(
    $basePluginId,
    FieldPluginManager $fieldManager,
    MutationPluginManager $mutationManager,
    TypePluginManagerAggregator $typeManagers
  ) {
    $this->basePluginId = $basePluginId;
    $this->fieldManager = $fieldManager;
    $this->mutationManager = $mutationManager;
    $this->typeManagers = $typeManagers;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    // Construct the optimized data representation for building the schema.
    $typeMap = $this->buildTypeMap(iterator_to_array($this->typeManagers));
    $typeReferenceMap = $this->buildTypeReferenceMap($typeMap);
    $typeAssocationMap = $this->buildTypeAssociationMap($typeMap);
    $fieldAssocationMap = $this->buildFieldAssociationMap($this->fieldManager, $typeMap);
    $fieldMap = $this->buildFieldMap($this->fieldManager, $fieldAssocationMap);
    $mutationMap = $this->buildMutationMap($this->mutationManager);

    $this->derivatives[$this->basePluginId] = [
      'type_map' => $typeMap,
      'type_reference_map' => $typeReferenceMap,
      'type_association_map' => $typeAssocationMap,
      'field_association_map' => $fieldAssocationMap,
      'field_map' => $fieldMap,
      'mutation_map' => $mutationMap,
    ] + $basePluginDefinition;

    return $this->derivatives;
  }

  /**
   * @return array
   */
  protected function buildTypeMap(array $managers) {
    // First collect all definitions by their name, overwriting those with
    // lower weights by their higher weighted counterparts. We also collect
    // the class from the plugin definition to be able to statically create
    // the type instance without loading the plugin managers at all at
    // run-time.
    $types = array_reduce(array_keys($managers), function ($carry, $type) use ($managers) {
      $manager = $managers[$type];
      $definitions = $manager->getDefinitions();

      return array_reduce(array_keys($definitions), function ($carry, $id) use ($type, $definitions) {
        $current = $definitions[$id];
        $name = $current['name'];

        if (empty($carry[$name]) || $carry[$name]['weight'] < $current['weight']) {
          $carry[$name] = [
            'type' => $type,
            'id' => $id,
            'class' => $current['class'],
            'weight' => !empty($current['weight']) ? $current['weight'] : 0,
            'reference' => !empty($current['type']) ? $current['type'] : NULL,
          ];
        }

        return $carry;
      }, $carry);
    }, []);

    // Retrieve the plugins run-time definitions. These will help us to prevent
    // plugin instantiation at run-time unless a plugin is actually called from
    // the graphql query execution. Plugins should take care of not having to
    // instantiate their plugin instances during schema composition.
    return array_map(function ($type) use ($managers) {
      $manager = $managers[$type['type']];
      /** @var \Drupal\graphql\Plugin\TypePluginInterface $instance */
      $instance = $manager->getInstance(['id' => $type['id']]);

      return $type + [
        'definition' => $instance->getDefinition(),
      ] + $type;
    }, $types);
  }

  /**
   * @return array
   */
  protected function buildTypeReferenceMap(array $types) {
    $references = array_reduce(array_keys($types), function ($references, $name) use ($types) {
      $current = $types[$name];
      $reference = $current['reference'];

      if (!empty($reference) && (empty($references[$reference]) || $references[$reference]['weight'] < $current['weight'])) {
        $references[$reference] = [
          'name' => $name,
          'weight' => !empty($current['weight']) ? $current['weight'] : 0,
        ];
      }

      return $references;
    }, []);

    return array_map(function ($reference) {
      return $reference['name'];
    }, $references);
  }

  /**
   * @return array
   */
  protected function buildFieldAssociationMap(FieldPluginManager $manager, $types) {
    $definitions = $manager->getDefinitions();
    $fields = array_reduce(array_keys($definitions), function ($carry, $id) use ($definitions) {
      $current = $definitions[$id];
      $parents = $current['parents'] ?: ['Root'];

      return array_reduce($parents, function ($carry, $parent) use ($current, $id) {
        // Allow plugins to define a different name for each parent.
        if (strpos($parent, ':') !== FALSE) {
          list($parent, $name) = explode(':', $parent);
        }

        $name = isset($name) ? $name : $current['name'];
        if (empty($carry[$parent][$name]) || $carry[$parent][$name]['weight'] < $current['weight']) {
          $carry[$parent][$name] = [
            'id' => $id,
            'weight' => !empty($current['weight']) ? $current['weight'] : 0,
          ];
        }

        return $carry;
      }, $carry);
    }, []);

    // Only return fields for types that are actually fieldable.
    $fieldable = [GRAPHQL_TYPE_PLUGIN, GRAPHQL_INTERFACE_PLUGIN];
    $fields = array_intersect_key($fields, array_filter($types, function ($type) use ($fieldable) {
      return in_array($type['type'], $fieldable);
    }) + ['Root' => NULL]);

    // We only need the plugin ids in this map.
    return array_map(function ($fields) {
      return array_map(function ($field) {
        return $field['id'];
      }, $fields);
    }, $fields);
  }

  /**
   * @return array
   */
  protected function buildTypeAssociationMap(array $types) {
    $assocations = array_filter(array_map(function ($type) use ($types) {
      // If this is an object type, just return a mapping for it's interfaces.
      if ($type['type'] === 'type') {
        return array_map(function () use ($type) {
          return [$type['definition']['name']];
        }, array_flip($type['definition']['interfaces']));
      }

      // For interfaces, find all object types that declare to implement it.
      if ($type['type'] === 'interface') {
        return [$type['definition']['name'] => array_values(array_map(function ($type) {
          return $type['definition']['name'];
        }, array_filter($types, function ($subType) use ($type) {
          return $subType['type'] === 'type' && in_array($type['definition']['name'], $subType['definition']['interfaces']);
        })))];
      }

      // Union types combine the two approaches above.
      if ($type['type'] === 'union') {
        $explicit = $type['definition']['types'];

        $implicit = array_values(array_map(function ($type) {
          return $type['definition']['name'];
        }, array_filter($types, function ($subType) use ($type) {
          return $subType['type'] === 'type' && in_array($type['definition']['name'], $subType['definition']['unions']);
        })));

        return [$type['definition']['name'] => array_merge($explicit, $implicit)];
      }

      return [];
    }, $types));

    $assocations = array_map('array_unique', array_reduce($assocations, 'array_merge_recursive', []));
    $assocations = array_map(function ($parent) use ($types) {
      $children = array_map(function ($child) use ($types) {
        return $types[$child] + ['name' => $child];
      }, $parent);

      uasort($children,[SortArray::class, 'sortByWeightElement']);
      $children = array_reverse($children);

      return array_map(function ($child) {
        return $child['name'];
      }, $children);
    }, $assocations);

    return $assocations;
  }

  /**
   * @return array
   */
  protected function buildFieldMap(FieldPluginManager $manager, $association) {
    return array_reduce($association, function ($carry, $fields) use ($manager) {
      return array_reduce($fields, function ($carry, $id) use ($manager) {
        if (!isset($carry[$id])) {
          $instance = $manager->getInstance(['id' => $id]);
          $definition = $manager->getDefinition($id);

          $carry[$id] = [
            'id' => $id,
            'class' => $definition['class'],
            'definition' => $instance->getDefinition(),
          ];
        }

        return $carry;
      }, $carry);
    }, []);
  }

  /**
   * @return array
   */
  protected function buildMutationMap(MutationPluginManager $manager) {
    $definitions = $manager->getDefinitions();
    $mutations = array_reduce(array_keys($definitions), function ($carry, $id) use ($definitions) {
      $current = $definitions[$id];
      $name = $current['name'];

      if (empty($carry[$name]) || $carry[$name]['weight'] < $current['weight']) {
        $carry[$name] = [
          'id' => $id,
          'class' => $current['class'],
          'weight' => !empty($current['weight']) ? $current['weight'] : 0,
        ];
      }

      return $carry;
    }, []);

    return array_map(function ($definition) use ($manager) {
      $id = $definition['id'];
      $instance = $manager->getInstance(['id' => $id]);

      return [
        'definition' => $instance->getDefinition(),
      ] + $definition;
    }, $mutations);
  }

}