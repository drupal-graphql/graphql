<?php

namespace Drupal\graphql;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides access control permissions for all exposed GraphQL servers.
 */
class PermissionProvider {
  use StringTranslationTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * PermissionProvider constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Collects permissions for the server endpoints.
   */
  public function permissions(): array {
    $storage = $this->entityTypeManager->getStorage('graphql_server');
    /** @var \Drupal\graphql\Entity\ServerInterface[] $servers */
    $servers = $storage->loadMultiple();
    $permissions = [];

    foreach ($servers as $id => $server) {
      $params = ['%name' => $server->label()];

      $permissions["execute $id arbitrary graphql requests"] = [
        'title' => $this->t('%name: Execute arbitrary requests', $params),
        'description' => $this->t('Allows users to execute arbitrary requests on the %name endpoint.', $params),
      ];

      $permissions["execute $id persisted graphql requests"] = [
        'title' => $this->t('%name: Execute persisted requests', $params),
        'description' => $this->t('Allows users to execute persisted requests on the %name endpoint.', $params),
      ];

      $permissions["use $id graphql explorer"] = [
        'title' => $this->t('%name: Use explorer', $params),
        'description' => $this->t('Allows users use the explorer interface.', $params),
      ];

      $permissions["use $id graphql voyager"] = [
        'title' => $this->t('%name: Use voyager', $params),
        'description' => $this->t('Allows users to use the voyager interface.', $params),
      ];
    }

    return $permissions;
  }

}
