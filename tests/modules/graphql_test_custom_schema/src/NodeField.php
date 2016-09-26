<?php

namespace Drupal\graphql_test_custom_schema;

use Drupal\node\NodeInterface;
use Drupal\graphql\GraphQL\Relay\Field\NodeField as NodeFieldBase;
use Drupal\graphql_test_custom_schema\Types\ArticleType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Relay\Fetcher\FetcherInterface;
use Youshido\GraphQL\Relay\Node;

class NodeField extends NodeFieldBase implements ContainerAwareInterface, FetcherInterface {

  /**
   * Constructs a NodeField object.
   */
  public function __construct() {
    return parent::__construct();
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
    $entityTypeManager = $this->container->get('entity_type.manager');

    switch ($type) {
      case 'article':
        return $entityTypeManager
          ->getStorage('node')
          ->load($id);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveType($object) {
    if ($object instanceof NodeInterface) {
      switch ($object->bundle()) {
        case 'sane_article':
          return new ArticleType();
      }
    }

    return NULL;
  }
}
