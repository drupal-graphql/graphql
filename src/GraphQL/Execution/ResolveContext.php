<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\graphql\Entity\ServerInterface;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Server\OperationParams;
use GraphQL\Type\Definition\ResolveInfo;

class ResolveContext implements RefinableCacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;

  /**
   * @var \Drupal\graphql\Entity\ServerInterface
   */
  protected $server;

  /**
   * @var array
   */
  protected $config;

  /**
   * @var array
   */
  protected $contexts;

  /**
   * @var \GraphQL\Server\OperationParams
   */
  protected $operation;

  /**
   * @var \GraphQL\Language\AST\DocumentNode
   */
  protected $document;

  /**
   * @var string
   */
  protected $type;

  /**
   * @var string
   */
  protected $language;

  /**
   * ResolveContext constructor.
   *
   * @param \Drupal\graphql\Entity\ServerInterface $server
   * @param \GraphQL\Server\OperationParams $operation
   * @param \GraphQL\Language\AST\DocumentNode $document
   * @param $type
   * @param array $config
   */
  public function __construct(
    ServerInterface $server,
    OperationParams $operation,
    DocumentNode $document,
    $type,
    array $config
  ) {
    $this->addCacheContexts(['user.permissions']);

    $this->server = $server;
    $this->config = $config;
    $this->operation = $operation;
    $this->document = $document;
    $this->type = $type;
  }

  /**
   * @return \Drupal\graphql\Entity\ServerInterface
   */
  public function getServer() {
    return $this->server;
  }

  /**
   * @return \GraphQL\Server\OperationParams
   */
  public function getOperation() {
    return $this->operation;
  }

  /**
   * @return \GraphQL\Language\AST\DocumentNode
   */
  public function getDocument() {
    return $this->document;
  }

  /**
   * @return string
   */
  public function getType(): string {
    return $this->type;
  }

  /**
   * @return string
   */
  public function getContextLanguage() {
    return $this->language;
  }

  /**
   * @param $language
   *
   * @return string
   */
  public function setContextLanguage($language) {
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
   * @param $value
   *   The value of the context.
   *
   * @return $this
   */
  public function setContextValue(ResolveInfo $info, $name, $value) {
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
  public function getContextValue(ResolveInfo $info, $name) {
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
   * @return boolean
   *   TRUE if the context exists, FALSE Otherwise.
   */
  public function hasContextValue(ResolveInfo $info, $name) {
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
