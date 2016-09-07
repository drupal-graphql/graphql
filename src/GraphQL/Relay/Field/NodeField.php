<?php

namespace Drupal\graphql\GraphQL\Relay\Field;

use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Relay\Fetcher\FetcherInterface;
use Youshido\GraphQL\Relay\Field\NodeField as NodeFieldBase;
use Youshido\GraphQL\Relay\Node;
use Youshido\GraphQL\Relay\NodeInterfaceType;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Object\ObjectType;
use Youshido\GraphQL\Type\Scalar\StringType;

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
    switch ($type) {
      case 'article':
        return \Drupal::entityTypeManager()->getStorage('node')->load($id);
    }

    throw new \Exception('The generic Relay node resolver is not yet implemented.');
  }

  /**
   * {@inheritdoc}
   */
  public function resolveType($object) {
    if ($object instanceof NodeInterface) {
      switch ($object->bundle()) {
        case 'sane_article':
          return new ObjectType([
            'name' => 'Article',
            'fields' => [
              'id' => new GlobalIdField('article'),
              'title' => [
                'type' => new NonNullType(new StringType()),
                'resolve' => function (NodeInterface $node) {
                  return $node->getTitle();
                },
              ],
            ],
            'interfaces' => [new NodeInterfaceType()],
          ]);
      }
    }

    // @todo Implement this.
    throw new \Exception('The generic Relay type resolver is not yet implemented.');
  }
}