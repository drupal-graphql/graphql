<?php

namespace Drupal\graphql\GraphQL;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Error\InvariantViolation;
use GraphQL\Executor\Executor;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Schema;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerInterface;

class ResolverRegistry implements ResolverRegistryInterface {

  /**
   * Nested list of field resolvers.
   *
   * Contains a nested list of callables, keyed by type and field name.
   *
   * @var callable[]
   */
  protected $fieldResolvers = [];

  /**
   * List of type resolvers for abstract types.
   *
   * Contains a list of callables keyed by the name of the abstract type.
   *
   * @var callable[]
   */
  protected $typeResolvers = [];

  /**
   * The type definitions.
   *
   * @var \Drupal\Core\Plugin\Context\ContextDefinition[]
   */
  protected $dataTypes = [];

  /**
   * The default field resolver.
   *
   * Used as a fallback if a specific field resolver can't be found.
   *
   * @var callable
   */
  protected $defaultFieldResolver;

  /**
   * The default type resolver.
   *
   * Used as a fallback if a specific type resolver can't be found.
   *
   * @var callable
   */
  protected $defaultTypeResolver;

  /**
   * ResolverRegistry constructor.
   *
   * @param $dataTypes
   * @param callable|null $defaultFieldResolver
   * @param callable|null $defaultTypeResolver
   */
  public function __construct($dataTypes, callable $defaultFieldResolver = NULL, callable $defaultTypeResolver = NULL) {
    $this->dataTypes = $dataTypes;
    $this->defaultFieldResolver = $defaultFieldResolver ?: [$this, 'resolveFieldDefault'];
    $this->defaultTypeResolver = $defaultTypeResolver ?: [$this, 'resolveTypeDefault'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateCompliance(Schema $schema) {
    $messages = [];

    foreach ($schema->getTypeMap() as $typeName => $type) {
      // Check that all registered fields on all object types have a corresponding
      // field resolver in the registry.
      if ($type instanceof ObjectType && strpos($typeName, '__') !== 0) {
        foreach ($type->getFields() as $fieldName => $field) {
          if (!$this->getFieldResolver($typeName, $fieldName)) {
            $messages[] = sprintf(
              'Potentially missing field resolver for field %s on ' .
              'type %s. The query engine will try to resolve the field using ' .
              'the default field resolver.',
              $fieldName,
              $typeName
            );
          }
        }
      }

      // Check that all abstract (union / interface) types have a corresponding
      // type resolver.
      if ($type instanceof InterfaceType || $type instanceof UnionType) {
        if (!$this->getTypeResolver($typeName)) {
          $messages[] = sprintf(
            'Potentially missing type resolver for type %s. Ideally, ' .
            'each abstract type has a dedicated type resolver associated ' .
            'with it. The query engine will try to infer the concrete type ' .
            'by traversing all implementing types at run-time. This might ' .
            'a negative performance impact and might result in a run-time ' .
            'error if the concrete type can not be inferred.',
            $typeName
          );
        }
      }
    }

    // Check that there are no excess field resolvers in the registry.
    foreach ($this->fieldResolvers as $typeName => $fields) {
      foreach ($fields as $fieldName => $resolver) {
        try {
          $type = $schema->getType($typeName);
        }
        catch (InvariantViolation $error) {
          $messages[] = sprintf('Field resolver for field %s invalidly registered on non-existent type %s.', $fieldName, $typeName);

          continue;
        }

        if ($type instanceof ObjectType) {
          if (!array_key_exists($fieldName, $type->getFields())) {
            $messages[] = sprintf('Excess field resolver for field %s on type %s.', $fieldName, $typeName);
          }
        }
        else {
          $messages[] = sprintf('Field resolver for field %s invalidly registered on non-object type %s.', $fieldName, $typeName);
        }
      }
    }

    // Check that there are no excess type resolvers in the registry.
    foreach ($this->typeResolvers as $typeName => $resolver) {
      try {
        $type = $schema->getType($typeName);
      }
      catch (InvariantViolation $error) {
        $messages[] = sprintf('Type resolver for invalidly registered on non-existent type %s.', $typeName);

        continue;
      }

      if ($type instanceof InterfaceType || $type instanceof UnionType) {
        $messages[] = sprintf('Type resolver invalidly registered on non-abstract type %s.', $typeName);
      }
    }

    return $messages;
  }

  /**
   * @param string $type
   * @param string $field
   * @param callable $resolver
   *
   * @return $this
   */
  public function addFieldResolver($type, $field, DataProducerInterface $proxy_resolver) {
    $this->fieldResolvers[$type][$field] = $proxy_resolver;
    return $this;
  }

  /**
   * @param string $type
   * @param string $field
   *
   * @return callable|null
   */
  public function getFieldResolver($type, $field) {
    return $this->fieldResolvers[$type][$field] ?? NULL;
  }

  /**
   * @param $value
   * @param $args
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return callable|null
   */
  protected function getRuntimeFieldResolver($value, $args, ResolveContext $context, ResolveInfo $info) {
    return $this->getFieldResolver($info->parentType->name, $info->fieldName);
  }

  /**
   * {@inheritdoc}
   */
  public function resolveField($value, $args, ResolveContext $context, ResolveInfo $info) {
    // First, check if there is a resolver registered for this field.
    if ($resolver = $this->getRuntimeFieldResolver($value, $args, $context, $info)) {
      if (!$resolver instanceof DataProducerInterface) {
        throw new \LogicException(sprintf('Field resolver for field %s on type %s is not callable.', $info->fieldName, $info->parentType->name));
      }
      return $resolver->resolve($value, $args, $context, $info);
    }

    return call_user_func($this->defaultFieldResolver, $value, $args, $context, $info);
  }

  /**
   * @param $value
   * @param $args
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return mixed|null
   */
  public function resolveFieldDefault($value, $args, ResolveContext $context, ResolveInfo $info) {
    return Executor::defaultFieldResolver($value, $args, $context, $info);
  }

  /**
   * @param $abstract
   * @param callable $resolver
   *
   * @return $this
   */
  public function addTypeResolver($abstract, callable $resolver) {
    $this->typeResolvers[$abstract] = $resolver;
    return $this;
  }

  /**
   * @param string $type
   *
   * @return callable|null
   */
  public function getTypeResolver($type) {
    return $this->typeResolvers[$type] ?? NULL;
  }

  /**
   * @param $value
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return callable|null
   */
  public function getRuntimeTypeResolver($value, ResolveContext $context, ResolveInfo $info) {
    return $this->getTypeResolver(Type::getNamedType($info->returnType)->name);
  }

  /**
   * {@inheritdoc}
   */
  public function resolveType($value, ResolveContext $context, ResolveInfo $info) {
    // First, check if there is a resolver registered for this abstract type.
    if ($resolver = $this->getRuntimeTypeResolver($value, $context, $info)) {
      if (!is_callable($resolver)) {
        throw new \LogicException(sprintf('Type resolver for type %s is not callable.', $info->parentType->name));
      }

      if (($type = $resolver($value, $context, $info)) !== NULL) {
        return $type;
      }
    }

    return call_user_func($this->defaultTypeResolver, $value, $context, $info);
  }

  /**
   * @param $value
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return null
   */
  protected function resolveTypeDefault($value, ResolveContext $context, ResolveInfo $info) {
    $abstract = Type::getNamedType($info->returnType);
    $types = $info->schema->getPossibleTypes($abstract);

    foreach ($types as $type) {
      $name = $type->name;

      // TODO: Warn about performance impact of generic type resolution?
      if (isset($this->dataTypes[$name]) && $definition = $this->dataTypes[$name]) {
        if ($definition->isSatisfiedBy(new Context($definition, $value))) {
          return $name;
        }
      }
    }

    return NULL;
  }
}
