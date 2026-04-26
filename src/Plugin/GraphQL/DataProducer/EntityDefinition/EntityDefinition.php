<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Gets entity definition for a given entity type.
 */
#[DataProducer(
  id: "entity_definition",
  name: "Entity definition",
  description: "Return entity definitions for given entity type.",
  consumes: [
    "entity_type" => new ContextDefinition(
      data_type: "string",
      label: "Entity type"
    ),
    "bundle" => new ContextDefinition(
      data_type: "string",
      label: "Bundle",
      required: FALSE
    ),
    "field_types" => new ContextDefinition(
      data_type: "string",
      label: "Field types (ALL, BASE_FIELDS, FIELD_CONFIG)",
      default_value: "ALL",
      required: FALSE
    ),
  ],
  produces: new ContextDefinition(
    data_type: "any",
    label: "Entity definition"
  )
)]
class EntityDefinition extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.bundle.info'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * EntityLoad constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $plugin_id
   *   The plugin id.
   * @param array $plugin_definition
   *   The plugin definition array.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   Information about entity type bundles.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    protected EntityTypeBundleInfoInterface $entityTypeBundleInfo,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Resolves entity definition for a given entity type.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string|null $bundle
   *   Optional. The entity bundle which are stored as a context for upcoming
   *   data producers deeper in hierarchy.
   * @param string|null $field_types
   *   Optional. The field types to retrieve (base fields, configurable fields,
   *   or both) which are stored as a context for upcoming data producers deeper
   *   in hierarchy.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $field_context
   *   Field context.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface
   *   The entity definition.
   */
  public function resolve(
    string $entity_type,
    ?string $bundle,
    ?string $field_types,
    FieldContext $field_context,
  ): EntityTypeInterface {
    if ($bundle) {
      $bundle_info = $this->entityTypeBundleInfo->getBundleInfo($entity_type);
      if (isset($bundle_info[$bundle])) {
        $bundle_context = $bundle_info[$bundle];
        $bundle_context['key'] = $bundle;
        $field_context->setContextValue('bundle', $bundle_context);
      }
    }

    if ($field_types) {
      $field_context->setContextValue('field_types', $field_types);
    }

    return $this->entityTypeManager->getDefinition($entity_type);
  }

}
