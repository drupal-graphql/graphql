<?php

namespace Drupal\graphql\GraphQL\Reducers;

use Symfony\Component\DependencyInjection\ContainerInterface;


class ReducerManager {

  /**
   * The service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Available query reducer ids.
   *
   * @var string[]
   */
  protected $reducers;

  /**
   * Constructs a ReducerManager object.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The current service container.
   * @param string[] $reducers
   *   An array of the available query reducer ids.
   */
  public function __construct(ContainerInterface $container, array $reducers) {
    $this->container = $container;
    $this->reducers = $reducers;
  }

  /**
   * Provides an array of available query reducer ids.
   *
   * @return string[]
   *   An array of available query reducer ids.
   */
  public function getAll() {
    return $this->reducers;
  }

  /**
   * Provides an array of available query reducer services.
   *
   * @return \Youshido\GraphQL\Execution\Visitor\AbstractQueryVisitor[]
   *   An array of available query reducer services.
   */
  public function getAllServices() {
    return array_map(function($id) {
      return $this->getService($id);
    }, $this->reducers);
  }

  /**
   * Retrieves a query reducer service from the container.
   *
   * @param string $id
   *   The reducer id, which together with the service id prefix allows the
   *   corresponding query reducer service to be retrieved.
   *
   * @return \Youshido\GraphQL\Execution\Visitor\AbstractQueryVisitor
   *   The requested query visitor service.
   */
  protected function getService($id) {
    return $this->container->get("graphql.reducer.$id");
  }

}
