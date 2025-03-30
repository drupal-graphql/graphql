<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Execution;

use GraphQL\Executor\Promise\PromiseAdapter;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Type\Schema;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service to make our GraphQL executor, can be swapped out.
 */
class ExecutorFactory {

  /**
   * The service container.
   */
  protected ContainerInterface $container;

  /**
   * ExecutorFactory constructor.
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  /**
   * Factory method to make a new executor.
   */
  public function create(
    PromiseAdapter $adapter,
    Schema $schema,
    DocumentNode $document,
    mixed $root,
    ResolveContext $context,
    array $variables,
    ?string $operation,
    callable $resolver,
  ): Executor {
    return Executor::create($this->container,
      $adapter,
      $schema,
      $document,
      $context,
      $root,
      $variables,
      $operation,
      $resolver
    );
  }

}
