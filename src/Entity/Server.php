<?php

namespace Drupal\graphql\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Server\OperationParams;
use GraphQL\Server\RequestError;
use GraphQL\Server\ServerConfig;
use GraphQL\Validator\DocumentValidator;

/**
 * @ConfigEntityType(
 *   id = "graphql_server",
 *   label = @Translation("Server"),
 *   handlers = {
 *     "list_builder" = "Drupal\graphql\Controller\ServerListBuilder",
 *     "form" = {
 *       "edit" = "Drupal\graphql\Form\ServerForm",
 *       "create" = "Drupal\graphql\Form\ServerForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "graphql_servers",
 *   admin_permission = "administer graphql configuration",
 *   entity_keys = {
 *     "id" = "name",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "name",
 *     "label",
 *     "schema",
 *     "endpoint",
 *     "debug",
 *     "batching"
 *   },
 *   links = {
 *     "collection" = "/admin/config/graphql/servers",
 *     "create-form" = "/admin/config/graphql/servers/create",
 *     "edit-form" = "/admin/config/graphql/servers/manage/{graphql_server}",
 *     "delete-form" = "/admin/config/graphql/servers/manage/{graphql_server}/delete"
 *   }
 * )
 */
class Server extends ConfigEntityBase implements ServerInterface {

  /**
   * The server's machine-readable name.
   *
   * @var string
   */
  public $name;

  /**
   * The server's human-readable name.
   *
   * @var string
   */
  public $label;

  /**
   * Whether the server is in debug mode.
   *
   * @var string
   */
  public $debug = FALSE;

  /**
   * Whether the server allows query batching.
   *
   * @var string
   */
  public $batching = TRUE;

  /**
   * The server's endpoint.
   *
   * @var string
   */
  public $endpoint;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function configuration() {
    $manager = \Drupal::service('plugin.manager.graphql.schema');
    /** @var \Drupal\graphql\Plugin\SchemaPluginInterface $plugin */
    $plugin = $manager->createInstance($this->get('schema'));

    // Create the server config.
    $config = ServerConfig::create();
    $config->setDebug(!!$this->get('debug'));
    $config->setQueryBatching(!!$this->get('batching'));
    $config->setValidationRules($this->getValidationRules());
    $config->setPersistentQueryLoader($this->getPersistedQueryLoader());
    $config->setContext($plugin->getContext());
    $config->setRootValue($plugin->getRootValue());
    $config->setSchema($plugin->getSchema());

    if ($resolver = $plugin->getFieldResolver()) {
      $config->setFieldResolver($resolver);
    }

    return $config;
  }

  /**
   * Returns a callable for loading persisted queries.
   *
   * @return callable
   *   The persisted query loader.
   */
  protected function getPersistedQueryLoader() {
    return function ($id, OperationParams $params) {
      throw new RequestError('Persisted queries are currently not supported');
    };
  }

  /**
   * Returns the validation rules to use for the query.
   *
   * May return a callable to allow the schema to decide the validation rules
   * independently for each query operation.
   *
   * @code
   *
   * public function getValidationRules() {
   *   return function (OperationParams $params, DocumentNode $document, $operation) {
   *     if (isset($params->queryId)) {
   *       // Assume that pre-parsed documents are already validated. This allows
   *       // us to store pre-validated query documents e.g. for persisted queries
   *       // effectively improving performance by skipping run-time validation.
   *       return [];
   *     }
   *
   *     return array_values(DocumentValidator::defaultRules());
   *   };
   * }
   *
   * @endcode
   *
   * @return array|callable
   *   The validation rules or a callable factory.
   */
  protected function getValidationRules() {
    return function (OperationParams $params, DocumentNode $document, $operation) {
      if (isset($params->queryId)) {
        // Assume that pre-parsed documents are already validated. This allows
        // us to store pre-validated query documents e.g. for persisted queries
        // effectively improving performance by skipping run-time validation.
        return [];
      }

      return array_values(DocumentValidator::defaultRules());
    };
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    \Drupal::service('router.builder')->setRebuildNeeded();
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage,$entities);
    \Drupal::service('router.builder')->setRebuildNeeded();
  }
}
