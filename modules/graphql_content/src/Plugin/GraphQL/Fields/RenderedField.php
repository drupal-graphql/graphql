<?php

namespace Drupal\graphql_content\Plugin\GraphQL\Fields;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\RendererInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Generic field plugin for rendering entity fields to string values.
 *
 * @GraphQLField(
 *   id = "rendered_field",
 *   type = "String",
 *   nullable = true,
 *   weight = -1,
 *   deriver = "Drupal\graphql_content\Plugin\Deriver\DisplayedFieldDeriver"
 * )
 */
class RenderedField extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Renderer instance to render fields.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, EntityTypeManagerInterface $entityTypeManager, RendererInterface $renderer) {
    $this->entityTypeManager = $entityTypeManager;
    $this->renderer = $renderer;
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
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof FieldableEntityInterface) {
      if (!isset($value->_graphql_build)) {
        $value->_graphql_build = [$value->id() => []];
        $display = EntityViewDisplay::collectRenderDisplay($value, 'graphql');
        $builder = $this->entityTypeManager->getViewBuilder($value->getEntityTypeId());
        $builder->buildComponents($value->_graphql_build, [$value->id() => $value], [$value->bundle() => $display], 'graphql');
      }

      $field = $this->getPluginDefinition()['field'];
      if ($this->getPluginDefinition()['virtual']) {
        yield $this->renderer->renderRoot($value->_graphql_build[$value->id()][$field]);
      }
      else {
        foreach (Element::children($value->_graphql_build[$value->id()][$field]) as $index) {
          yield trim($this->renderer->renderRoot($value->_graphql_build[$value->id()][$field][$index]));
        }
      }
    }
  }
}
