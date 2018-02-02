<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Drupal\graphql\Utility\StringHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\InputField;

/**
 * @GraphQLField(
 *   id = "entity_rendered",
 *   name = "entityRendered",
 *   type = "String",
 *   secure = true,
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\EntityRenderedDeriver",
 * )
 */
class EntityRendered extends FieldPluginBase  implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    EntityTypeManagerInterface $entityTypeManager,
    RendererInterface $renderer
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entityTypeManager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function buildArguments(PluggableSchemaBuilderInterface $schemaBuilder) {
    $arguments = parent::buildArguments($schemaBuilder);

    if (empty($arguments['mode'])) {
      $definition = $this->getPluginDefinition();
      $type = StringHelper::camelCase($definition['entity_type'], 'display', 'mode', 'id');

      if ($type = $schemaBuilder->findByName($type, [GRAPHQL_ENUM_PLUGIN])) {
        $arguments['mode'] = new InputField([
          'name' => 'mode',
          'type' => $type->getDefinition($schemaBuilder),
        ]);
      }
    }

    return $arguments;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ContentEntityInterface) {
      $mode = isset($args['mode']) ? $args['mode'] : 'full';
      $language = isset($args['language']) ? $args['language'] : $value->language()->getId();

      $builder = $this->entityTypeManager->getViewBuilder($value->getEntityTypeId());
      $rendered = $builder->view($value, $mode, $language);
      yield $this->renderer->render($rendered);
    }
  }

}
