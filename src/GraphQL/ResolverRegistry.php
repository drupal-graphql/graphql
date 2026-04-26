<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\Resolver\ResolverInterface;
use GraphQL\Executor\Executor;
use GraphQL\Type\Definition\ImplementingType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

/**
 * Contains all the mappings how to resolve a GraphQL request.
 */
class ResolverRegistry implements ResolverRegistryInterface {

  /**
   * Nested list of field resolvers.
   *
   * Contains a nested list of callables, keyed by type and field name.
   *
   * @var array<string, array<string, \Drupal\graphql\GraphQL\Resolver\ResolverInterface>>
   */
  protected array $fieldResolvers = [];

  /**
   * List of type resolvers for abstract types.
   *
   * Contains a list of callables keyed by the name of the abstract type.
   *
   * @var array<callable>
   */
  protected array $typeResolvers = [];

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
   * Custom scalar definitions keyed by type name.
   *
   * @var array<string, \Drupal\graphql\GraphQL\CustomScalarInterface<mixed>>
   */
  protected array $customScalars = [];

  /**
   * ResolverRegistry constructor.
   *
   * @param callable|null $defaultFieldResolver
   *   (optional) The default field resolver.
   * @param callable|null $defaultTypeResolver
   *   (optional) The default type resolver.
   */
  public function __construct(
    ?callable $defaultFieldResolver = NULL,
    ?callable $defaultTypeResolver = NULL,
  ) {
    $this->defaultFieldResolver = $defaultFieldResolver ?: [
      $this,
      'resolveFieldDefault',
    ];
    $this->defaultTypeResolver = $defaultTypeResolver ?: [
      $this,
      'resolveTypeDefault',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function resolveField(mixed $value, array $args, ResolveContext $context, ResolveInfo $info, FieldContext $field): mixed {
    // First, check if there is a resolver registered for this field.
    if ($resolver = $this->getRuntimeFieldResolver($value, $args, $context, $info)) {
      return $resolver->resolve($value, $args, $context, $info, $field);
    }

    return call_user_func($this->defaultFieldResolver, $value, $args, $context, $info, $field);
  }

  /**
   * {@inheritdoc}
   */
  public function resolveType(mixed $value, ResolveContext $context, ResolveInfo $info): ?string {
    // First, check if there is a resolver registered for this abstract type.
    if ($resolver = $this->getRuntimeTypeResolver($value, $context, $info)) {
      if (($type = $resolver($value, $context, $info)) !== NULL) {
        return $type;
      }
    }

    return call_user_func($this->defaultTypeResolver, $value, $context, $info);
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldResolver(string $type, string $field, ResolverInterface $resolver): static {
    $this->fieldResolvers[$type][$field] = $resolver;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldResolver(string $type, string $field): ?ResolverInterface {
    return $this->fieldResolvers[$type][$field] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllFieldResolvers(): array {
    return $this->fieldResolvers;
  }

  /**
   * {@inheritdoc}
   */
  public function addTypeResolver(string $abstract, callable $resolver): static {
    $this->typeResolvers[$abstract] = $resolver;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTypeResolver(string $type): ?callable {
    return $this->typeResolvers[$type] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldResolverWithInheritance(Type $type, string $fieldName): ?ResolverInterface {
    if ($resolver = $this->getFieldResolver($type->toString(), $fieldName)) {
      return $resolver;
    }

    if (!$type instanceof ImplementingType) {
      return NULL;
    }

    // Go through the interfaces implemented for the type on which this field is
    // resolved and check if they lead to a field resolution.
    foreach ($type->getInterfaces() as $interface) {
      if ($resolver = $this->getFieldResolverWithInheritance($interface, $fieldName)) {
        return $resolver;
      }
    }

    return NULL;
  }

  /**
   * Returns the field resolver that should be used at runtime.
   */
  protected function getRuntimeFieldResolver(mixed $value, array $args, ResolveContext $context, ResolveInfo $info): ?ResolverInterface {
    return $this->getFieldResolverWithInheritance($info->parentType, $info->fieldName);
  }

  /**
   * Resolves a default value for a field.
   */
  protected function resolveFieldDefault(mixed $value, array $args, ResolveContext $context, ResolveInfo $info, FieldContext $field): mixed {
    return Executor::defaultFieldResolver($value, $args, $context, $info);
  }

  /**
   * Returns the type resolver that should be used on runtime.
   */
  protected function getRuntimeTypeResolver(mixed $value, ResolveContext $context, ResolveInfo $info): ?callable {
    return $this->getTypeResolver(Type::getNamedType($info->returnType)->toString());
  }

  /**
   * Returns NULL as default type.
   */
  protected function resolveTypeDefault(mixed $value, ResolveContext $context, ResolveInfo $info): ?string {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function addCustomScalar(string $typeName, CustomScalarInterface $scalar): static {
    // We serialize the custom scalar implementation since PHP or PHPStan does
    // not provide a way for us to enforce this. This ensures that scalars that
    // are not serializable fail loudly and do not block downstream
    // serialization of the assembled schema where they would create
    // hard-to-debug errors.
    // This is a slight performance overhead which we accept with the
    // expectation that custom scalars will initially be low volume and this
    // overhead is eliminated once registry caching is implemented.
    try {
      serialize($scalar);
    }
    catch (\Exception $e) {
      $class = get_class($scalar);
      throw new \InvalidArgumentException(
        "Custom scalar '$typeName' backed by '$class' must be serializable. " .
        "Ensure it implements __serialize() or has no non-serializable properties. ",
        0,
        $e
      );
    }
    $this->customScalars[$typeName] = $scalar;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomScalar(string $typeName): ?CustomScalarInterface {
    return $this->customScalars[$typeName] ?? NULL;
  }

}
