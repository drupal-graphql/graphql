<?php

namespace Drupal\graphql_batched_test\Plugin\GraphQL\Fields;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql_batched_test\UserDataBaseInterface;
use Drupal\graphql\GraphQL\Batching\BatchedFieldResolver;
use Drupal\graphql\GraphQL\Batching\BatchedFieldInterface;
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
class Users extends FieldPluginBase implements ContainerFactoryPluginInterface, BatchedFieldInterface {

  /**
   * The user database.
   *
   * @var \Drupal\graphql_batched_test\UserDataBaseInterface
   */
  protected $userDatabase;

  /**
   * The batched field resolver.
   *
   * @var \Drupal\graphql\GraphQL\Batching\BatchedFieldResolver
   */
  protected $batchedFieldResolver;

  /**
   * {@inheritdoc}
   */
  public function getBatchedFieldResolver() {
    return $this->batchedFieldResolver;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveBatch(array $batch) {
    // Turn the list of method arguments into a list of user requirements.
    $resultMap = array_map(function($item) {
      return $this->getRequirementsFromArgs($item['parent'], $item['arguments']);
    }, $batch);

    // Reduce this list into one list of users to fetch.
    $uids = array_unique(array_reduce($resultMap, 'array_merge', []));

    // Sort them so we can predict the input argument.
    sort($uids);

    // Actually fetch the users.
    $users = $this->userDatabase->fetchUsers($uids);

    // Map the fetched users back into the result map.
    return array_map(function($item) use ($users) {
      return array_map(function($uid) use ($users) {
        return $users[$uid];
      }, $item);
    }, $resultMap);
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
      $container->get('graphql_batched_test.user_database'),
      $container->get('graphql.batched_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    UserDataBaseInterface $userDataBase,
    BatchedFieldResolver $batchedFieldResolver
  ) {
    $this->batchedFieldResolver = $batchedFieldResolver;
    $this->userDatabase = $userDataBase;
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    foreach ($value as $user) {
      yield $user;
    }
  }

}