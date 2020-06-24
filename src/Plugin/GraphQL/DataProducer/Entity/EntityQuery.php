<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Error\UserError;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds and executes Drupal entity query.
 *
 * @DataProducer(
 *   id = "entity_query",
 *   name = @Translation("Load entities"),
 *   description = @Translation("Loads entities."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Entity IDs"),
 *     multiple = TRUE
 *   ),
 *   consumes = {
 *     "type" = @ContextDefinition("string",
 *       label = @Translation("Entity type")
 *     ),
 *     "limit" = @ContextDefinition("integer",
 *       label = @Translation("Limit"),
 *       required = FALSE,
 *       default_value = 10
 *     ),
 *     "offset" = @ContextDefinition("integer",
 *       label = @Translation("Offset"),
 *       required = FALSE,
 *       default_value = 0
 *     ),
 *     "owned_only" = @ContextDefinition("boolean",
 *       label = @Translation("Query only owned entities"),
 *       required = FALSE,
 *       default_value = FALSE
 *     ),
 *     "conditions" = @ContextDefinition("any",
 *       label = @Translation("Conditions"),
 *       multiple = TRUE,
 *       required = FALSE,
 *       default_value = {}
 *     ),
 *     "allowed_filters" = @ContextDefinition("string",
 *       label = @Translation("Allowed filters"),
 *       multiple = TRUE,
 *       required = FALSE,
 *       default_value = {}
 *     ),
 *     "language" = @ContextDefinition("string",
 *       label = @Translation("Entity languages(s)"),
 *       multiple = TRUE,
 *       required = FALSE
 *     ),
 *     "bundles" = @ContextDefinition("any",
 *       label = @Translation("Entity bundle(s)"),
 *       multiple = TRUE,
 *       required = FALSE
 *     ),
 *     "sorts" = @ContextDefinition("any",
 *       label = @Translation("Sorts"),
 *       multiple = TRUE,
 *       default_value = {},
 *       required = FALSE
 *     )
 *   }
 * )
 */
class EntityQuery extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * EntityLoad constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param array $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    array $pluginDefinition,
    EntityTypeManager $entityTypeManager,
    AccountInterface $current_user
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $current_user;
  }

  /**
   * Resolves the entity query.
   *
   * @param string $type
   *   Entity type.
   * @param int $limit
   *   Maximum number of queried entities.
   * @param int|null $offset
   *   Offset to start with.
   * @param bool|null $ownedOnly
   *   Query only entities owned by current user.
   * @param array|null $conditions
   *   List of conditions to filter the entities.
   * @param array|null $allowedFilters
   *   List of fields to be used in conditions to restrict access to data.
   * @param string|null $language
   *   Language of queried entities.
   * @param array|null $bundles
   *   List of bundles to be filtered.
   * @param array|null $sorts
   *   List of sorts.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   The caching context related to the current field.
   *
   * @return array
   *   The list of ids that match this query.
   *
   * @throws \GraphQL\Error\UserError
   *   No bundles defined for given entity type.
   */
  public function resolve(string $type, int $limit = 10, ?int $offset, ?bool $ownedOnly, ?array $conditions, ?array $allowedFilters, ?string $language, ?array $bundles, ?array $sorts, FieldContext $context): array {
    // Make sure offset is zero or positive.
    $offset = max($offset ?: 0, 0);

    $entity_type = $this->entityTypeManager->getStorage($type);
    $query = $entity_type->getQuery()
      ->range($offset, $limit);

    // Query only those entities which are owned by current user, if desired.
    if ($ownedOnly) {
      $query->condition('uid', $this->currentUser->id());
      // Add user cacheable dependencies.
      $account = $this->currentUser->getAccount();
      $context->addCacheableDependency($account);
      // Cache response per user to make sure the user related result is shown.
      $context->addCacheContexts(['user']);
    }

    // Ensure that access checking is performed on the query.
    $query->accessCheck(TRUE);

    if (isset($bundles)) {
      $bundle_key = $entity_type->getEntityType()->getKey('bundle');
      if (!$bundle_key) {
        throw new UserError('No bundles defined for given entity type.');
      }
      $query->condition($bundle_key, $bundles, "IN");
    }
    if (isset($language)) {
      $query->condition('langcode', $language);
    }

    foreach ($conditions as $condition) {
      if (!in_array($condition['field'], $allowedFilters)) {
        throw new UserError("Field '{$condition['field']}' is not allowed as filter.");
      }
      $operation = isset($condition['operator']) ? $condition['operator'] : NULL;
      $query->condition($condition['field'], $condition['value'], $operation);
    }

    foreach ($sorts as $sort) {
      if (!empty($sort['field'])) {
        if (!empty($sort['direction']) && strtolower($sort['direction']) == 'desc') {
          $direction = 'DESC';
        }
        else {
          $direction = 'ASC';
        }
        $query->sort($sort['field'], $direction);
      }
    }

    $ids = $query->execute();

    return $ids;
  }

}
