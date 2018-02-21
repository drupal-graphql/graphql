<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use GraphQL\Type\Definition\ResolveInfo;

class ResolveContext implements RefinableCacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;

  /**
   * Read-only list of global values.
   *
   * @var array
   */
  protected $globals;

  /**
   * @var array
   */
  protected $contexts = [];

  /**
   * ResolveContext constructor.
   *
   * @param array $globals
   */
  public function __construct(array $globals = []) {
    $this->globals = $globals;
  }

  /**
   * @param $name
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   * @param null $default
   *
   * @return mixed|null
   */
  public function getContext($name, ResolveInfo $info, $default = NULL) {
    $operation = isset($info->operation->name->value) ? $info->operation->name->value : $info->operation->operation;
    $path = $info->path;

    do {
      $key = implode('.', $path);
      if (isset($this->contexts[$operation][$key][$name])) {
        return $this->contexts[$operation][$key][$name];
      }
    } while (array_pop($path) && !empty($path));

    return $default;
  }

  /**
   * @param $name
   * @param $value
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return $this
   */
  public function setContext($name, $value, ResolveInfo $info) {
    $operation = isset($info->operation->name->value) ? $info->operation->name->value : $info->operation->operation;
    $key = implode('.', $info->path);
    $this->contexts[$operation][$key][$name] = $value;

    return $this;
  }

  /**
   * @param $name
   * @param null $default
   *
   * @return mixed|null
   */
  public function getGlobal($name, $default = NULL) {
    if (isset($this->globals[$name])) {
      return $this->globals[$name];
    }

    return $default;
  }

}