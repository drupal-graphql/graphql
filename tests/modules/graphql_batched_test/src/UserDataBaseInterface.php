<?php

namespace Drupal\graphql_batched_test;

/**
 * Dummy user database service interface.
 */
interface UserDataBaseInterface {

  /**
   * Retrieve user objects by their id's.
   *
   * @param string[] $ids
   *   The list of ids.
   *
   * @return array
   *   The list of user objects.
   */
  public function fetchUsers(array $ids);

}
