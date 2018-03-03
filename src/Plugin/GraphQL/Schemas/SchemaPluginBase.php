<?php

namespace Drupal\graphql\Plugin\GraphQL\Schemas;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\SortArray;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\FieldPluginManager;
use Drupal\graphql\Plugin\MutationPluginManager;
use Drupal\graphql\Plugin\SchemaBuilderInterface;
use Drupal\graphql\Plugin\SchemaPluginInterface;
use Drupal\graphql\Plugin\TypePluginManagerAggregator;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class SchemaPluginBase extends PluginBase implements SchemaPluginInterface, SchemaBuilderInterface, ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * @var \Drupal\graphql\Plugin\FieldPluginManager
   */
  protected $fieldManager;

  /**
   * @var \Drupal\graphql\Plugin\MutationPluginManager
   */
  protected $mutationManager;

  /**
   * @var \Drupal\graphql\Plugin\TypePluginManagerAggregator
   */
  protected $typeManagers;

  /**
   * @var array
   */
  protected $typeMap;

  /**
   * @var array
   */
  protected $typeReferenceMap;

  /**
   * @var array
   */
  protected $fieldAssocationMap;

  /**
   * @var array
   */
  protected $typeAssocationMap;

  /**
   * @var array
   */
  protected $fieldMap;

  /**
   * @var array
   */
  protected $mutationMap;

  /**
   * @var array
   */
  protected $fields = [];

  /**
   * @var array
   */
  protected $mutations = [];

  /**
   * @var array
   */
  protected $types = [];

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.graphql.field'),
      $container->get('plugin.manager.graphql.mutation'),
      $container->get('graphql.type_manager_aggregator')
    );
  }

  /**
   * SchemaPluginBase constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param array $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\graphql\Plugin\FieldPluginManager $fieldManager
   * @param \Drupal\graphql\Plugin\MutationPluginManager $mutationManager
   * @param \Drupal\graphql\Plugin\TypePluginManagerAggregator $typeManagers
   */
  public function __construct(
    $configuration,
    $pluginId,
    $pluginDefinition,
    FieldPluginManager $fieldManager,
    MutationPluginManager $mutationManager,
    TypePluginManagerAggregator $typeManagers
  ) {
    $this->fieldManager = $fieldManager;
    $this->mutationManager = $mutationManager;
    $this->typeManagers = $typeManagers;

    // Construct the optimized plugin definitions for building the schema.
    $this->typeMap = $this->buildTypeMap(iterator_to_array($this->typeManagers));
    $this->typeReferenceMap = $this->buildTypeReferenceMap($this->typeMap);
    $this->typeAssocationMap = $this->buildTypeAssociationMap($this->typeMap);
    $this->fieldAssocationMap = $this->buildFieldAssociationMap($this->fieldManager, $this->typeMap);
    $this->fieldMap = $this->buildFieldMap($this->fieldManager, $this->fieldAssocationMap);
    $this->mutationMap = $this->buildMutationMap($this->mutationManager);
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    $config = new SchemaConfig();

    if ($this->hasMutations()) {
      $config->setMutation(new ObjectType([
        'name' => 'MutationRoot',
        'fields' => function () {
          return $this->getMutations();
        },
      ]));
    }

    $config->setQuery(new ObjectType([
      'name' => 'QueryRoot',
      'fields' => function () {
        return $this->getFields('Root');
      },
    ]));

    $config->setTypes(function () {
      return $this->getTypes();
    });

    $config->setTypeLoader(function ($name) {
      return $this->getType($name);
    });

    return new Schema($config);
  }

  /**
   * @return bool
   */
  public function hasFields($type) {
    return isset($this->fieldAssocationMap[$type]);
  }

  /**
   * @return bool
   */
  public function hasMutations() {
    return !empty($this->mutationMap);
  }

  /**
   * @return bool
   */
  public function hasType($name) {
    return isset($this->typeMap[$name]);
  }

  /**
   * @return array
   */
  public function getFields($parent) {
    if (isset($this->fieldAssocationMap[$parent])) {
      return $this->processFields(array_map(function ($id) {
        return $this->fieldMap[$id];
      }, $this->fieldAssocationMap[$parent]));
    }

    return [];
  }

  /**
   * @return array
   */
  public function getMutations() {
    return $this->processMutations($this->mutationMap);
  }

  /**
   * @return array
   */
  public function getTypes() {
    return array_map(function ($name) {
      return $this->getType($name);
    }, array_keys($this->typeMap));
  }

  /**
   * Retrieve the list of derivatives associated with a composite type.
   *
   * @return string[]
   *   The list of possible sub typenames.
   */
  public function getSubTypes($name) {
    return isset($this->typeAssocationMap[$name]) ? $this->typeAssocationMap[$name] : [];
  }

  /**
   * Resolve the matching type.
   */
  public function resolveType($name, $value, $context, $info) {
    if (!isset($this->typeAssocationMap[$name])) {
      return NULL;
    }

    foreach ($this->typeAssocationMap[$name] as $type) {
      // TODO: Avoid loading the type for the check. Make it static!
      if (isset($this->typeMap, $type) && $instance = $this->buildType($this->typeMap[$type])) {
        if ($instance->isTypeOf($value, $context, $info)) {
          return $instance;
        }
      }
    }

    return NULL;
  }

  /**
   * @param $name
   *
   * @return mixed
   */
  public function getType($name) {
    if (isset($this->typeMap[$name])) {
      return $this->buildType($this->typeMap[$name]);
    }

    do {
      if (isset($this->typeReferenceMap[$name])) {
        return $this->buildType($this->typeMap[$this->typeReferenceMap[$name]]);
      }
    } while (($pos = strpos($name, ':')) !== FALSE && $name = substr($name, 0, $pos));

    throw new \LogicException(sprintf('Missing type %s.', $name));
  }

  /**
   * @param $mutations
   *
   * @return array
   */
  public function processMutations($mutations) {
    return array_map([$this, 'buildMutation'], $mutations);
  }

  /**
   * @param $fields
   *
   * @return array
   */
  public function processFields($fields) {
    return array_map([$this, 'buildField'], $fields);
  }

  /**
   * @param $args
   *
   * @return array
   */
  public function processArguments($args) {
    return array_map(function ($arg) {
      return [
        'type' => $this->processType($arg['type']),
      ] + $arg;
    }, $args);
  }

  /**
   * @param $type
   *
   * @return mixed
   */
  public function processType($type) {
    list($type, $decorators) = $type;

    return array_reduce($decorators, function ($type, $decorator) {
      return $decorator($type);
    }, $this->getType($type));
  }

  /**
   * @param $type
   *
   * @return \Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase
   */
  protected function buildType($type) {
    if (!isset($this->types[$type['id']])) {
      $creator = [$type['class'], 'createInstance'];
      $manager = $this->typeManagers->getTypeManager($type['type']);
      $this->types[$type['id']] = $creator($this, $manager, $type['definition'], $type['id']);
    }

    return $this->types[$type['id']];
  }

  /**
   * @param $field
   *
   * @return mixed
   */
  protected function buildField($field) {
    if (!isset($this->fields[$field['id']])) {
      $creator = [$field['class'], 'createInstance'];
      $this->fields[$field['id']] = $creator($this, $this->fieldManager, $field['definition'], $field['id']);
    }

    return $this->fields[$field['id']];
  }

  /**
   * @param $mutation
   *
   * @return mixed
   */
  protected function buildMutation($mutation) {
    if (!isset($this->mutations[$mutation['id']])) {
      $creator = [$mutation['class'], 'createInstance'];
      $this->mutations[$mutation['id']] = $creator($this, $this->mutationManager, $mutation['definition'], $mutation['id']);
    }

    return $this->mutations[$mutation['id']];
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
