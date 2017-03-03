<?php

namespace Drupal\graphql_test_custom_schema\Fields;

use Drupal\graphql_test_custom_schema\Types\UserType;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\AbstractField;

/**
 * Provides a current user field.
 */
class CurrentUserField extends AbstractField implements ContainerAwareInterface {
  use ContainerAwareTrait;

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) :UserInterface {
    $account = $this->container->get('current_user')->getAccount();
    $user = $this->container->get('entity_type.manager')->getStorage('user')->load($account->id());

    return $user;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'currentUser';
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return new UserType();
  }

}
