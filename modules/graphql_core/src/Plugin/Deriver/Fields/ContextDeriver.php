<?php

namespace Drupal\graphql_core\Plugin\Deriver\Fields;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\graphql\Utility\StringHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;

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
    return new static($container->get('graphql.context_repository'));
  }

  /**
   * ContextDeriver constructor.
   *
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $contextRepository
   *   The context repository service.
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
        $this->derivatives[$id] = [
          'name' => StringHelper::propCase($id, 'context'),
          'context_id' => $id,
          'type' => $context->getContextDefinition()->getDataType(),
        ];
        // Add cache contexts, if available
        if ($context instanceof CacheableDependencyInterface) {
          $this->derivatives[$id]['response_cache_contexts'] = $context->getCacheContexts();
        }
        // Add default base
        $this->derivatives[$id] += $basePluginDefinition;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
