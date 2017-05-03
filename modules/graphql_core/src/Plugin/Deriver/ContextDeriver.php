<?php

namespace Drupal\graphql_core\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create GraphQL context fields based on available Drupal contexts.
 */
class ContextDeriver extends DeriverBase implements ContainerDeriverInterface {
  /**
   * The context repository service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static($container->get('graphql_core.context_repository'));
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(ContextRepositoryInterface $contextRepository) {
    $this->contextRepository = $contextRepository;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    if (empty($this->derivatives)) {
      foreach ($this->contextRepository->getAvailableContexts() as $id => $context) {
        $dataType = $context->getContextDefinition()->getDataType();

        $this->derivatives[$id] = [
          'name' => graphql_core_propcase($id) . 'Context',
          'context_id' => $id,
          'nullable' => TRUE,
          'multi' => FALSE,
          'data_type' => $dataType,
        ] + $basePluginDefinition;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
