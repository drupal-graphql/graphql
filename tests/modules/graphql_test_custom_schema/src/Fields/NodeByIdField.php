<?php

namespace Drupal\graphql_test_custom_schema\Fields;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\graphql_test_custom_schema\Types\ArticleType;
use Drupal\graphql_test_custom_schema\Types\EntityNodeInterfaceType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Youshido\GraphQL\Config\Field\FieldConfig;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\AbstractField;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Scalar\IntType;

class NodeByIdField extends AbstractField implements ContainerAwareInterface, RefinableCacheableDependencyInterface {

  use ContainerAwareTrait;
  use RefinableCacheableDependencyTrait;

  /**
   * {@inheritdoc}
   */
  public function build(FieldConfig $config) {
    parent::build($config);

    $config->addArgument('id', [
      'type' => new NonNullType(new IntType()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return new EntityNodeInterfaceType();
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'articleById';
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    /** @var EntityTypeManager $entityTypeManager */
    $entityTypeManager = $this->container->get('entity_type.manager');

    $entity = $entityTypeManager
      ->getStorage('node')
      ->load($args['id']);

    $this->addCacheableDependency($entity);
    return $entity;
  }
}
