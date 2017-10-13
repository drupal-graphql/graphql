<?php

namespace Drupal\graphql_batched_test;

/**
 * Empty UserDatabaseInterface implementation.
 *
 * Replaced by a prophecy in tests.
 */
class UserDatabase implements UserDataBaseInterface {

  /**
   * {@inheritdoc}
   */
  public function fetchUsers(array $ids) {}

}
