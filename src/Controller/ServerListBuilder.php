<?php

declare(strict_types=1);

namespace Drupal\graphql\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Admin page controller that shows the list of configured GraphQL servers.
 *
 * @package Drupal\graphql\Controller
 *
 * @codeCoverageIgnore
 */
class ServerListBuilder extends ConfigEntityListBuilder {

  /**
   * Create a new Server List Builder instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    EntityStorageInterface $storage,
    protected AccountProxyInterface $currentUser,
  ) {
    parent::__construct($entity_type, $storage);
  }

  /**
   * {@inheritDoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    return [
      'label' => $this->t('Label'),
    ] + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    return [
      'label' => $entity->label(),
    ] + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    $id = $entity->id();

    if ($this->currentUser->hasPermission('use graphql explorer')) {
      $operations['explorer'] = [
        'title' => 'Explorer',
        'weight' => 10,
        'url' => Url::fromRoute('graphql.explorer', ['graphql_server' => $id]),
      ];
    }

    if ($this->currentUser->hasPermission('use graphql voyager')) {
      $operations['voyager'] = [
        'title' => 'Voyager',
        'weight' => 10,
        'url' => Url::fromRoute('graphql.voyager', ['graphql_server' => $id]),
      ];
    }

    if ($this->currentUser->hasPermission("administer graphql configuration")) {
      $operations['persisted_queries'] = [
        'title' => 'Persisted queries',
        'weight' => 10,
        'url' => Url::fromRoute('entity.graphql_server.persisted_queries_form', ['graphql_server' => $id]),
      ];
    }

    return $operations;
  }

}
