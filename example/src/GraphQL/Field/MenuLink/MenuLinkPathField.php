<?php

namespace Drupal\graphql_example\GraphQL\Field\MenuLink;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\link\LinkItemInterface;
use Drupal\graphql_example\GraphQL\Field\SelfAwareField;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Scalar\StringType;
use Drupal\link\Plugin\Field\FieldType\LinkItem;

class MenuLinkPathField extends SelfAwareField implements ContainerAwareInterface {
  use ContainerAwareTrait;

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    if ($value instanceof MenuLinkInterface) {
      $urlObject = $value->getUrlObject();

      if ($urlObject->isRouted()) {
        /** @var \Drupal\Core\Path\\AliasManagerInterface $aliasManager */
        $aliasManager = $this->container->get('path.alias_manager');
        $internalPath = $urlObject->getInternalPath();
        $alias = $aliasManager->getAliasByPath("/$internalPath");

        return strpos($alias, '/') === 0 ? $alias : "/$alias";
      }

      if (($uri = $urlObject->getUri()) && strpos($uri, 'base:') === 0) {
        $uri = substr($uri, 5);
        return strpos($uri, '/') === 0 ? $uri : "/$uri";
      }

      return $uri;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'path';
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return new NonNullType(new StringType());
  }
}