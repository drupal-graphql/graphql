<?php

namespace Drupal\graphql_rules\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\rules\Entity\RulesComponentConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derive fields from configured views.
 */
abstract class RulesMutationDeriverBase extends DeriverBase implements ContainerDeriverInterface {

  /**
   * Returns plugin definition based on given action.
   *
   * @param \Drupal\rules\Entity\RulesComponentConfig $rulesComponent
   *   The action plugin.
   * @param array $basePluginDefinition
   *   Base definition.
   *
   * @return array
   */
  protected abstract function getDefinition(RulesComponentConfig $rulesComponent, $basePluginDefinition);

  /**
   * The rules component storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager')->getStorage('rules_component')
    );
  }

  /**
   * Creates a ViewDeriver object.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $rulesComponentStorage
   *   The rules component storage service.
   */
  public function __construct(EntityStorageInterface $rulesComponentStorage) {
    $this->storage = $rulesComponentStorage;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    foreach ($this->loadRulesComponents() as $rulesComponent) {
      $this->getDefinition($rulesComponent, $basePluginDefinition);
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

  /**
   * Returns the rules component storage.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   */
  protected function getStorage() {
    return $this->storage;
  }

  /**
   * Returns all rules components.
   *
   * @return \Drupal\rules\Entity\RulesComponentConfig[]
   */
  protected function loadRulesComponents() {
    $query = $this->getStorage()->getQuery()
      ->sort('id');

    return RulesComponentConfig::loadMultiple($query->execute());
  }

}
