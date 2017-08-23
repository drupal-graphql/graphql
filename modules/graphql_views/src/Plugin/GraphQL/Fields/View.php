<?php

namespace Drupal\graphql_views\Plugin\GraphQL\Fields;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Expose views as root fields.
 *
 * @GraphQLField(
 *   id = "view",
 *   secure = true,
 *   nullable = true,
 *   multi = true,
 *   types = {"Root"},
 *   deriver = "Drupal\graphql_views\Plugin\Deriver\ViewDeriver"
 * )
 */
class View extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    EntityTypeManagerInterface $entityTypeManager
  ) {
    $this->entityTypeManager = $entityTypeManager;
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    $storage = $this->entityTypeManager->getStorage('view');
    $definition = $this->getPluginDefinition();

    /** @var \Drupal\views\Entity\View $view */
    if ($view = $storage->load($definition['view'])) {
      $executable = $view->getExecutable();
      $executable->setDisplay($definition['display']);

      // Set view contextual filters.
      /* @see \Drupal\graphql_views\Plugin\Deriver\ViewDeriverBase::getArgumentsInfo() */
      if (!empty($definition['arguments_info'])) {
        $viewArguments = [];
        foreach ($definition['arguments_info'] as $argumentId => $argumentInfo) {
          if (isset($args['contextual_filter'][$argumentId])) {
            $viewArguments[$argumentInfo['index']] = $args['contextual_filter'][$argumentId];
          }
          elseif (
            $value instanceof EntityInterface &&
            $value->getEntityTypeId() === $argumentInfo['entity_type'] &&
            (empty($argumentInfo['bundles']) ||
              in_array($value->bundle(), $argumentInfo['bundles'], TRUE))
          ) {
            $viewArguments[$argumentInfo['index']] = $value->id();
          }
          else {
            $viewArguments[$argumentInfo['index']] = NULL;
          }
        }
        $executable->setArguments($viewArguments);
      }

      // Prepare arguments for use as exposed form input.
      $input = array_filter([
        // Sorting arguments.
        'sort_by' => isset($args['sortBy']) ? $args['sortBy'] : NULL,
        'sort_order' => isset($args['sortDirection']) ? $args['sortDirection'] : NULL,
      ]);

      // If some filters are missing from the input, set them to an empty string
      // explicitly. Otherwise views module generates "Undefined index" notice.
      $filters = $executable->getDisplay()->getOption('filters');
      foreach ($filters as $filterKey => $filterRow) {
        $inputKey = $filterRow['expose']['identifier'];
        if (!isset($args['filter'][$filterKey])) {
          $input[$inputKey] = $filterRow['value'];
        } else {
          $input[$inputKey] = $args['filter'][$filterKey];
        }
      }

      $executable->setExposedInput($input);
      // This is a workaround for the Taxonomy Term filter which requires a full
      // exposed form to be sent OR the display being an attachment to just
      // accept input values.
      $executable->is_attachment = TRUE;
      $executable->exposed_raw_input = $input;

      if ($definition['paged']) {
        // Set paging parameters.
        $executable->setItemsPerPage($args['pageSize']);
        $executable->setCurrentPage($args['page']);
        $executable->execute();
        yield $executable;
      }
      else {
        $executable->execute();
        foreach ($executable->result as $row) {
          yield $row->_entity;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getCacheDependencies($result, $value, array $args) {
    // If the view is not paged, it's simply a list of rows. Since these are
    // entities, they should implement CacheableDependencyInterface anyways.
    if (!$this->getPluginDefinition()['paged']) {
      return parent::getCacheDependencies($result, $value, $args);
    }

    /** @var \Drupal\Views\ViewExecutable $executable */
    $executable = reset($result);
    $metadata = new CacheableMetadata();
    $metadata->setCacheTags($executable->getCacheTags());

    return $metadata;
  }

}
