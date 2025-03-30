<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataTrait;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\typed_data\DataFetcherTrait;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Resolves a property path.
 *
 * @todo Delete this resolver. This is a plugin already.
 */
class Path implements ResolverInterface {

  use TypedDataTrait;
  use DataFetcherTrait;

  /**
   * Name of the context.
   */
  protected string|DataDefinitionInterface $type;

  /**
   * Source resolver.
   */
  protected mixed $path;

  /**
   * Resolver.
   */
  protected mixed $value;

  /**
   * Path constructor.
   */
  public function __construct(string $type, mixed $path, ?ResolverInterface $value = NULL) {
    $this->type = $type;
    $this->path = $path;
    $this->value = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(mixed $value, array $args, ResolveContext $context, ResolveInfo $info, FieldContext $field): mixed {
    $value = $this->value ?? new ParentValue();
    $value = $value->resolve($value, $args, $context, $info, $field);
    $metadata = new BubbleableMetadata();

    $type = $this->type instanceof DataDefinitionInterface ? $this->type : DataDefinition::create($this->type);
    $data = $this->getTypedDataManager()->create($type, $value);
    $output = $this->getDataFetcher()->fetchDataByPropertyPath($data, $this->path, $metadata)->getValue();

    $context->addCacheableDependency($metadata);
    if ($output instanceof CacheableDependencyInterface) {
      $context->addCacheableDependency($output);
    }

    return $output;
  }

}
