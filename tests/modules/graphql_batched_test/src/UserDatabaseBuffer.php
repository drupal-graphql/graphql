<?php

namespace Drupal\graphql_batched_test;

use Drupal\graphql\GraphQL\Batching\BufferBase;
use Zend\Stdlib\ArrayObject;

class UserDatabaseBuffer extends BufferBase {

  /**
   * @var \Drupal\graphql_batched_test\UserDataBaseInterface
   */
  protected $userDataBase;

  public function __construct(UserDataBaseInterface $userDataBase) {
    $this->userDataBase = $userDataBase;
  }

  /**
   * Fetch user id's.
   */
  function add(array $ids) {
    return $this->createBufferResolver(new ArrayObject([
      'uids' => $ids,
    ]));
  }

  protected function resolveBufferArray(array $buffer) {
    $uids = array_reduce(array_map(function ($item) {
      return $item['uids'];
    }, $buffer), 'array_merge', []);

    $users = $this->userDataBase->fetchUsers($uids);

    $result = array_map(function ($item) use ($users) {
      $comb = array_combine($item['uids'], $item['uids']);
      $result = array_map(function ($uid) use ($users) {
        return $users[$uid];
      }, $comb);
      return $result;
    }, $buffer);

    return $result;
  }

}