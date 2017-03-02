<?php

namespace Drupal\graphql_example\GraphQL\Field\Root;

use Drupal\graphql\GraphQL\UncacheableValue;
use Drupal\graphql_example\GraphQL\Field\SelfAwareField;
use Drupal\graphql_example\GraphQL\Type\PageType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Youshido\GraphQL\Config\Field\FieldConfig;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\InputField;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Scalar\IntType;

class PageByIdField extends SelfAwareField implements ContainerAwareInterface {
  use ContainerAwareTrait;

  /**
   * {@inheritdoc}
   */
  public function build(FieldConfig $config) {
    $config->addArgument(new InputField([
      'name' => 'id',
      'description' => 'The id of the page.',
      'type' => new NonNullType(new IntType()),
    ]));
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    /** @var \Drupal\Core\Entity\EntityTypeManager $entityTypeManager */
    $entityTypeManager = $this->container->get('entity_type.manager');
    $entityStorage = $entityTypeManager->getStorage('node');

    /** @var \Drupal\node\NodeInterface $node */
    if (($node = $entityStorage->load($args['id'])) && $node->bundle() === 'page') {
      return $node;
    }

    return new UncacheableValue(NULL);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'pageById';
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return new PageType();
  }
}