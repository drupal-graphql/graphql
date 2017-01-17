<?php

namespace Drupal\graphql_example\GraphQL\Field\Root;

use Drupal\graphql_example\GraphQL\Field\SelfAwareField;
use Drupal\graphql_example\GraphQL\Type\MenuType;
use Drupal\system\MenuInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Youshido\GraphQL\Config\Field\FieldConfig;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\InputField;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Scalar\StringType;

class MenuByNameField extends SelfAwareField implements ContainerAwareInterface {
  use ContainerAwareTrait;

  /**
   * {@inheritdoc}
   */
  public function build(FieldConfig $config) {
    $config->addArgument(new InputField([
      'name' => 'name',
      'description' => 'The machine readable name of the menu.',
      'type' => new NonNullType(new StringType()),
    ]));
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    $entity = $this->loadMenuByName($args['name']);

    if ($entity instanceof MenuInterface) {
      return $entity;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'menuByName';
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return new MenuType();
  }

  /**
   * Helper function to load an entity by its path.
   *
   * @param $name
   *   The internal path or url alias pointing to an entity.
   * @return \Drupal\Core\Entity\EntityInterface|NULL
   *   The loaded entity object or NULL if no entity can be mapped to that path.
   */
  protected function loadMenuByName($name) {
    /** @var \Drupal\Core\Entity\EntityTypeManager $entityTypeManager */
    $entityTypeManager = $this->container->get('entity_type.manager');
    $entityStorage = $entityTypeManager->getStorage('menu');

    return $entityStorage->load($name);
  }
}