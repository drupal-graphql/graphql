<?php

namespace Drupal\graphql\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * @ConfigEntityType(
 *   id = "graphql_query_map",
 *   label = @Translation("Query map"),
 *   handlers = {
 *     "list_builder" = "Drupal\graphql\Controller\QueryMapListBuilder",
 *     "form" = {
 *       "import" = "Drupal\graphql\Form\EntityQueryMapImportForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *       "inspect" = "Drupal\graphql\Form\EntityQueryMapForm",
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
 *     "inspect-form" = "/admin/config/graphql/query-maps/{graphql_query_map}",
 *     "import-form" = "/admin/config/graphql/query-maps/import",
 *     "delete-form" = "/admin/config/graphql/query-maps/{graphql_query_map}/delete",
 *     "collection" = "/admin/config/graphql/query-maps",
 *   }
 * )
 */
class QueryMap extends ConfigEntityBase implements QueryMapInterface {

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
