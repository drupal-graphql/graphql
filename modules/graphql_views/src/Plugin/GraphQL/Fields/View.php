<?php

namespace Drupal\graphql_views\Plugin\GraphQL\Fields;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
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
 *   parents = {"Root"},
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
        $arguments = $this->extractContextualFilters($value, $args);
        $executable->setArguments($arguments);
      }

      $filters = $executable->getDisplay()->getOption('filters');;
      $input = $this->extractExposedInput($value, $args, $filters);
      $executable->setExposedInput($input);

      // This is a workaround for the Taxonomy Term filter which requires a full
      // exposed form to be sent OR the display being an attachment to just
      // accept input values.
      $executable->is_attachment = TRUE;
      $executable->exposed_raw_input = $input;

      if (!empty($definition['paged'])) {
        // Set paging parameters.
        $executable->setItemsPerPage($args['pageSize']);
        $executable->setCurrentPage($args['page']);
      }

      yield $executable->render($definition['display']);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getCacheDependencies($result, $value, array $args) {
    $result = reset($result);
    return [$result['cache']];
  }

  /**
   * Retrieves the contextual filter argument from the parent value or args.
   *
   * @param $value
   *   The resolved parent value.
   * @param $args
   *   The arguments provided to the field.
   *
   * @return array
   *   An array of arguments containing the contextual filter value from the
   *   parent or provided args if any.
   */
  protected function extractContextualFilters($value, $args) {
    $definition = $this->getPluginDefinition();
    $arguments = [];

    foreach ($definition['arguments_info'] as $argumentId => $argumentInfo) {
      if (isset($args['contextualFilter'][$argumentId])) {
        $arguments[$argumentInfo['index']] = $args['contextualFilter'][$argumentId];
      }
      elseif (
        $value instanceof EntityInterface &&
        $value->getEntityTypeId() === $argumentInfo['entity_type'] &&
        (empty($argumentInfo['bundles']) ||
          in_array($value->bundle(), $argumentInfo['bundles'], TRUE))
      ) {
        $arguments[$argumentInfo['index']] = $value->id();
      }
      else {
        $arguments[$argumentInfo['index']] = NULL;
      }
    }

    return $arguments;
  }

  /**
   * Retrieves sort and filter arguments from the provided field args.
   *
   * @param $value
   *   The resolved parent value.
   * @param $args
   *   The array of arguments provided to the field.
   * @param $filters
   *   The available filters for the configured view.
   *
   * @return array
   *   The array of sort and filter arguments to execute the view with.
   */
  protected function extractExposedInput($value, $args, $filters) {
    // Prepare arguments for use as exposed form input.
    $input = array_filter([
      // Sorting arguments.
      'sort_by' => isset($args['sortBy']) ? $args['sortBy'] : NULL,
      'sort_order' => isset($args['sortDirection']) ? $args['sortDirection'] : NULL,
    ]);

    // If some filters are missing from the input, set them to an empty string
    // explicitly. Otherwise views module generates "Undefined index" notice.
    foreach ($filters as $filterKey => $filterRow) {
      if (!isset($filterRow['expose']['identifier'])) {
        continue;
      }

      $inputKey = $filterRow['expose']['identifier'];
      if (!isset($args['filter'][$inputKey])) {
        $input[$inputKey] = $filterRow['value'];
      } else {
        $input[$inputKey] = $args['filter'][$inputKey];
      }
    }

    return $input;
  }

}
