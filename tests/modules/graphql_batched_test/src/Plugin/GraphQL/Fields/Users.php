<?php

namespace Drupal\graphql_batched_test\Plugin\GraphQL\Fields;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql_batched_test\UserDatabaseBuffer;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * A basic user field.
 *
 * @GraphQLField(
 *   id = "users",
 *   secure = true,
 *   name = "users",
 *   type = "User",
 *   multi = true,
 *   arguments = {
 *     "uids" = {
 *       "type" = "String",
 *       "multi" = true
 *     }
 *   }
 * )
 */
class Users extends FieldPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The user database.
   *
   * @var \Drupal\graphql_batched_test\UserDatabaseBuffer
   */
  protected $userDatabaseBuffer;

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    $uids = $this->getRequirementsFromArgs($value, $args);
    $deferred = $this->userDatabaseBuffer->add($uids);
    return function () use ($deferred) {
      foreach ($deferred() as $row) {
        yield $row;
      }
    };
  }

  /**
   * Extract the list of required user ids from field arguments.
   *
   * @param mixed $parent
   *   The parent value.
   * @param array $arguments
   *   The field arguments.
   *
   * @return string[]
   *   The required user ids.
   */
  protected function getRequirementsFromArgs($parent, array $arguments) {
    if ($parent) {
      return array_merge(
        isset($parent['friends']) ? $parent['friends'] : [],
        isset($parent['foe']) ? [$parent['foe']] : []
      );
    }
    return array_key_exists('uids', $arguments) ? $arguments['uids'] : [];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $pluginId,
    $pluginDefinition
  ) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('graphql_batched_test.user_database_buffer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    UserDataBaseBuffer $userDataBaseBuffer
  ) {
    $this->userDatabaseBuffer = $userDataBaseBuffer;
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }


}