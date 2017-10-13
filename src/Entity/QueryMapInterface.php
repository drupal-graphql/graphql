<?php

namespace Drupal\graphql\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a GraphQLQueryMap entity.
 */
interface QueryMapInterface extends ConfigEntityInterface
{

  /**
   * Returns a single GraphQL query from the map.
   *
   * @param $queryId
   * @return string|null
   *   A single GraphQL query.
   */
  function getQuery($queryId);

  /**
   * Checks if the query map with version ID exists.
   *
   * @param int $version
   *   The GraphQL query map version ID.
   *
   * @return bool
   *   TRUE if a query map with the given ID exists, FALSE otherwise.
   */
  public static function exists($version);
}
