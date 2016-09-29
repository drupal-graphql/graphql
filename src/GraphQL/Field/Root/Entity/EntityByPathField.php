<?php

namespace Drupal\graphql\GraphQL\Field\Root\Entity;

use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Field\FieldBase;
use Drupal\graphql\GraphQL\Type\Entity\EntityInterfaceType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Scalar\StringType;

class EntityByPathField extends FieldBase implements ContainerAwareInterface {
  use ContainerAwareTrait;

  /**
   * Constructs an EntityByPathField object.
   */
  public function __construct() {
    $config = [
      'name' => 'entityByPath',
      'type' => new EntityInterfaceType(),
      'args' => [
        'path' => new NonNullType(new StringType()),
      ],
    ];

    parent::__construct($config);
  }

  /**
   * Resolve function for this field.
   *
   * Loads an entity by its entity id.
   *
   * @param $value
   *   The parent value. Irrelevant in this case.
   * @param array $args
   *   The array of arguments. Contains the id of the entity to load.
   * @param \Youshido\GraphQL\Execution\ResolveInfo $info
   *   The context information for which to resolve.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The loaded entity object or NULL if there is no entity with the given id.
   */
  public function resolve($value, array $args = [], ResolveInfo $info) {
    // Get the route definition from the internal path.
    if (!$route = Url::fromUri("internal:/${args['path']}")) {
      return NULL;
    }

    // Check if the route is a canonical entity route.
    $routeName = $route->getRouteName();
    list($prefix, $entityType, $suffix) = explode('.', $routeName);

    if (!($prefix === 'entity' && $suffix === 'canonical')) {
      return NULL;
    }

    /** @var \Drupal\Core\Entity\EntityTypeManager $entityTypeManager */
    $entityTypeManager = $this->container->get('entity_type.manager');
    $entityStorage = $entityTypeManager->getStorage($entityType);

    // Extract the entity id from the route parameters.
    $routeParameters = $route->getRouteParameters();
    $entityId = $routeParameters[$entityType];

    return $entityStorage->load($entityId);
  }
}
