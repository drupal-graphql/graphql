<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\graphql\Entity\ServerInterface;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Server\OperationParams;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Context that is provided during resolving the GraphQL tree.
 */
class ResolveContext implements RefinableCacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;

  /**
   * List of available contexts keyed by path and context name.
   */
  protected array $contexts;

  /**
   * The context language.
   */
  protected ?string $language = NULL;

  /**
   * ResolveContext constructor.
   *
   * @param \Drupal\graphql\Entity\ServerInterface $server
   *   The GraphQL server configuration.
   * @param \GraphQL\Server\OperationParams $operation
   *   The operation parameters to perform.
   * @param \GraphQL\Language\AST\DocumentNode $document
   *   The parsed schema document.
   * @param string $type
   *   The type.
   * @param array $config
   *   Configuration.
   */
  public function __construct(
    protected ServerInterface $server,
    protected OperationParams $operation,
    protected DocumentNode $document,
    protected string $type,
    protected array $config,
  ) {
    $this->addCacheContexts(['user.permissions']);
  }

  /**
   * Returns the GraphQL server config entity.
   */
  public function getServer(): ServerInterface {
    return $this->server;
  }

  /**
   * Returns the current operation parameters.
   */
  public function getOperation(): OperationParams {
    return $this->operation;
  }

  /**
   * Returns the parsed GraphQL schema.
   */
  public function getDocument(): DocumentNode {
    return $this->document;
  }

  /**
   * Returns the type.
   */
  public function getType(): string {
    return $this->type;
  }

  /**
   * Returns the current context language.
   */
  public function getContextLanguage(): ?string {
    return $this->language;
  }

  /**
   * Sets the current context language.
   *
   * @return $this
   */
  public function setContextLanguage(string $language) {
    $this->language = $language;
    return $this;
  }

  /**
   * Sets a contextual value for the current field and its descendants.
   *
   * Allows field resolvers to set contextual values which can be inherited by
   * their descendants.
   *
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The resolve info object.
   * @param string $name
   *   The name of the context.
   * @param mixed $value
   *   The value of the context.
   *
   * @return $this
   */
  public function setContextValue(ResolveInfo $info, string $name, mixed $value) {
    $key = implode('.', $info->path);
    $this->contexts[$key][$name] = $value;

    return $this;
  }

  /**
   * Get a contextual value for the current field.
   *
   * Allows field resolvers to inherit contextual values from their ancestors.
   *
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The resolve info object.
   * @param string $name
   *   The name of the context.
   *
   * @return mixed
   *   The current value of the given context or NULL if it's not set.
   */
  public function getContextValue(ResolveInfo $info, string $name): mixed {
    $path = $info->path;

    do {
      $key = implode('.', $path);
      if (isset($this->contexts[$key]) && array_key_exists($name, $this->contexts[$key])) {
        return $this->contexts[$key][$name];
      }

      array_pop($path);
    } while (count($path));

    return NULL;
  }

  /**
   * Checks whether contextual value for the current field exists.
   *
   * Also checks ancestors of the field.
   *
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The resolve info object.
   * @param string $name
   *   The name of the context.
   *
   * @return bool
   *   TRUE if the context exists, FALSE Otherwise.
   */
  public function hasContextValue(ResolveInfo $info, string $name): bool {
    $path = $info->path;

    do {
      $key = implode('.', $path);
      if (isset($this->contexts[$key]) && array_key_exists($name, $this->contexts[$key])) {
        return TRUE;
      }

      array_pop($path);
    } while (count($path));

    return FALSE;
  }

}
