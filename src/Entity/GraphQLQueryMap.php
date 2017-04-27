<?php

namespace Drupal\graphql\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the GraphQLQueryMap entity.
 *
 * @ConfigEntityType(
 *   id = "graphql_query_map",
 *   label = @Translation("GraphQL Query Map"),
 *   handlers = {
 *     "list_builder" = "Drupal\graphql\Controller\GraphQLQueryMapListBuilder",
 *     "form" = {
 *       "import" = "Drupal\graphql\Form\GraphQLQueryMapImportForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *       "inspect" = "Drupal\graphql\Form\GraphQLQueryMapForm",
 *     }
 *   },
 *   config_prefix = "graphql_query_map",
 *   admin_permission = "administer graphql queries",
 *   entity_keys = {
 *     "id" = "version"
 *   },
 *   config_export = {
 *     "version",
 *     "queryMap",
 *   },
 *   links = {
 *     "inspect-form" = "/admin/structure/graphql/{graphql_query_map}",
 *     "import-form" = "/admin/structure/graphql/import",
 *     "delete-form" = "/admin/structure/graphql/{graphql_query_map}/delete",
 *     "collection" = "/admin/structure/graphql",
 *   }
 * )
 */
class GraphQLQueryMap extends ConfigEntityBase implements GraphQLQueryMapInterface {

  /**
   * The GraphQL query map version ID.
   *
   * @var string
   */
  public $version;

  /**
   * The GraphQL query map.
   *
   * @var array
   */
  public $queryMap = [];

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->version;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery($queryId) {
    if (isset($this->queryMap[$queryId])) {
      return $this->queryMap[$queryId];
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function exists($id) {
    return (bool) static::load($id);
  }

}
