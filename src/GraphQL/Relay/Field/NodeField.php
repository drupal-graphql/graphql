<?php

namespace Drupal\graphql\GraphQL\Relay\Field;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Relay\Fetcher\FetcherInterface;
use Youshido\GraphQL\Relay\Field\NodeField as NodeFieldBase;
use Youshido\GraphQL\Relay\Node;

class NodeField extends NodeFieldBase implements ContainerAwareInterface, FetcherInterface {

  use ContainerAwareTrait;

  /**
   * Constructs a NodeField object.
   */
  public function __construct() {
    return parent::__construct($this);
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    list($type, $id) = Node::fromGlobalId($args['id']);

    return $this->fetcher->resolveNode($type, $id);
  }

  /**
   * {@inheritdoc}
   */
  public function resolveNode($type, $id) {
    // @todo Implement this.
    throw new \Exception('The generic Relay node resolver is not yet implemented.');
  }

  /**
   * {@inheritdoc}
   */
  public function resolveType($object) {
    // @todo Implement this.
    throw new \Exception('The generic Relay type resolver is not yet implemented.');
  }
}