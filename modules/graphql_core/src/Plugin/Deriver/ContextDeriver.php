<?php

namespace Drupal\graphql_core\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
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
   * A schema manager instance to identify graphql return types.
   *
   * @var \Drupal\graphql_core\GraphQLSchemaManagerInterface
   */
  protected $schemaManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static($container->get('graphql_core.context_repository'), $container->get('graphql_core.schema_manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(ContextRepositoryInterface $contextRepository, GraphQLSchemaManagerInterface $schemaManager) {
    $this->schemaManager = $schemaManager;
    $this->contextRepository = $contextRepository;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    if (empty($this->derivatives)) {
      foreach ($this->contextRepository->getAvailableContexts() as $id => $context) {

        $data_type = $context->getContextDefinition()->getDataType();

        $this->derivatives[$id] = [
          'name' => graphql_core_propcase($id) . 'Context',
          'context_id' => $id,
          'nullable' => TRUE,
          'multi' => FALSE,
          'data_type' => $data_type,
        ] + $basePluginDefinition;
      }
    }
    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
