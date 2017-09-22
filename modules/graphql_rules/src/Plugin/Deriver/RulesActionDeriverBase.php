<?php

namespace Drupal\graphql_rules\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\rules\Core\RulesActionInterface;
use Drupal\rules\Core\RulesActionManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Derive fields from configured views.
 */
abstract class RulesActionDeriverBase extends DeriverBase implements ContainerDeriverInterface {

  /**
   * Returns plugin definition based on given action.
   *
   * @param \Drupal\rules\Core\RulesActionInterface $action
   *   The action plugin.
   * @param array $basePluginDefinition
   *   Base definition.
   *
   * @return array
   */
  protected abstract function getDefinition(RulesActionInterface $action, $basePluginDefinition);

  /**
   * The rules action manager service.
   *
   * @var \Drupal\rules\Core\RulesActionManager
   */
  protected $rulesActionManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('plugin.manager.rules_action')
    );
  }

  /**
   * Creates a ViewDeriver object.
   *
   * @param \Drupal\rules\Core\RulesActionManager $rulesActionManager
   *   The rules action manager service.
   */
  public function __construct(RulesActionManager $rulesActionManager) {
    $this->rulesActionManager = $rulesActionManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    foreach ($this->rulesActionManager->getDefinitions() as $actionId => $action) {
      /** @var \Drupal\rules\Core\RulesActionInterface $action */
      try {
        $action = $this->rulesActionManager->createInstance($actionId);

        $this->getDefinition($action, $basePluginDefinition);
      } catch (ServiceNotFoundException $ex) {
        /**
         * Some rules actions (eg. ban_ip) throw exceptions when their instance
         * is created.
         * @see https://www.drupal.org/node/2637052
         */
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
