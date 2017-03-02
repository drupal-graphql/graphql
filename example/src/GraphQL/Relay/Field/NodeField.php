<?php

namespace Drupal\graphql_example\GraphQL\Relay\Field;

use Drupal\Core\Url;
use Drupal\graphql_example\GraphQL\Field\SelfAwareField;
use Drupal\graphql_example\GraphQL\Relay\Type\NodeInterfaceType;
use Drupal\graphql_example\RouteObjectWrapper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Youshido\GraphQL\Config\Field\FieldConfig;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\InputField;
use Youshido\GraphQL\Relay\Node;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Scalar\IdType;

class NodeField extends SelfAwareField implements ContainerAwareInterface {
  use ContainerAwareTrait;

  /**
   * {@inheritdoc}
   */
  public function build(FieldConfig $config) {
    $config->addArgument(new InputField([
      'name' => 'id',
      'type' => new NonNullType(new IdType()),
      'description' => 'The ID of an object.',
    ]));
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'node';
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return 'Fetches an object given its ID.';
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return new NodeInterfaceType();
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    list($type, $id) = Node::fromGlobalId($args['id']);

    switch ($type) {
      case 'menu':
        return $this->resolveMenu($id);

      case 'page':
        return $this->resolvePage($id);

      default:
        return NULL;
    }
  }

  /**
   * Helper function to load nodes of type 'menu'.
   *
   * @param $id
   *   The id of the menu to be loaded.
   *
   * @return \Drupal\system\MenuInterface|null
   *   The loaded menu or NULL.
   */
  protected function resolveMenu($id) {
    /** @var \Drupal\system\MenuInterface $entity */
    $entity = $this->resolveEntity($id, 'menu');
    return $entity;
  }

  /**
   * Helper function to load nodes of type 'page'.
   *
   * @param $id
   *   The id of the menu to be loaded.
   *
   * @return \Drupal\node\NodeInterface|null
   *   The loaded page or NULL.
   */
  protected function resolvePage($id) {
    /** @var \Drupal\node\NodeInterface $entity */
    $entity = $this->resolveEntity($id, 'node', ['page']);
    return $entity;
  }

  /**
   * Helper function to resolve an entity object of a given set of bundles.
   *
   * @param $id
   *   The id of the node to load.
   * @param $type
   *   The bundle to restrict to.
   * @param null|string[] $bundles
   *   An array of bundle names to restrict loading to or NULL if any bundle
   *   is permitted.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The loaded entity object or NULL.
   */
  protected function resolveEntity($id, $type, $bundles = NULL) {
    /** @var \Drupal\Core\Entity\EntityTypeManager $entityTypeManager */
    $entityTypeManager = $this->container->get('entity_type.manager');
    /** @var \Drupal\Core\Entity\EntityStorageInterface $entityStorage */
    $entityStorage = $entityTypeManager->getStorage($type);

    if (!$entity = $entityStorage->load($id)) {
      return NULL;
    }

    if (!empty($bundles) && !in_array($entity->bundle(), $bundles, TRUE)) {
      return NULL;
    }

    return $entity->access('view') ? $entity : NULL;
  }
}
