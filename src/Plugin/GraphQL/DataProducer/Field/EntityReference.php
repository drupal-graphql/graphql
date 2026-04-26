<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Field;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Buffers\EntityBuffer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Deferred;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Loads entities from an entity reference field.
 */
#[DataProducer(
  id: "entity_reference",
  name: new TranslatableMarkup("Entity reference"),
  description: new TranslatableMarkup("Loads entities from an entity reference field."),
  produces: new ContextDefinition(
    data_type: "entity",
    label: new TranslatableMarkup("Entity"),
    multiple: TRUE
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Parent entity")
    ),
    "field" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Field name")
    ),
    "language" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Entity language"),
      required: FALSE
    ),
    "bundle" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Entity bundle(s)"),
      required: FALSE,
      multiple: TRUE
    ),
    "access" => new ContextDefinition(
      data_type: "boolean",
      label: new TranslatableMarkup("Check access"),
      required: FALSE,
      default_value: TRUE
    ),
    "access_user" => new EntityContextDefinition(
      data_type: "entity:user",
      label: new TranslatableMarkup("User"),
      required: FALSE,
      default_value: NULL
    ),
    "access_operation" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Operation"),
      required: FALSE,
      default_value: "view"
    ),
  ]
)]
class EntityReference extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  use EntityReferenceTrait;

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
      $container->get('entity_type.manager'),
      $container->get('entity.repository'),
      $container->get('graphql.buffer.entity')
    );
  }

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param array $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository service.
   * @param \Drupal\graphql\GraphQL\Buffers\EntityBuffer $entityBuffer
   *   The entity buffer service.
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    array $pluginDefinition,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EntityRepositoryInterface $entityRepository,
    protected EntityBuffer $entityBuffer,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * Resolve entity references in the given field name.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The parent entity with the reference field.
   * @param string $field
   *   Name of the entity reference field.
   * @param string|null $language
   *   In which language to load the referenced entities.
   * @param array<int, string>|null $bundles
   *   Filter for entity bundles in the reference field.
   * @param bool|null $access
   *   Whether to check access to the referenced entities.
   * @param \Drupal\Core\Session\AccountInterface|null $accessUser
   *   The user to check access for.
   * @param string|null $accessOperation
   *   For example "view".
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   The GraphQL field context.
   *
   * @return \GraphQL\Deferred|array
   *   A promise that will return referenced entities or empty array if there
   *   aren't any.
   */
  public function resolve(EntityInterface $entity, string $field, ?string $language, ?array $bundles, ?bool $access, ?AccountInterface $accessUser, ?string $accessOperation, FieldContext $context): Deferred|array {
    if (!$entity instanceof FieldableEntityInterface || !$entity->hasField($field)) {
      return [];
    }

    $definition = $entity->getFieldDefinition($field);
    $type = $definition->getSetting('target_type');
    $values = $entity->get($field);
    if ($values instanceof EntityReferenceFieldItemListInterface) {
      $ids = array_map(function ($value) {
        return $value['target_id'];
      }, $values->getValue());

      $resolver = $this->entityBuffer->add($type, $ids);
      return new Deferred(function () use ($type, $language, $bundles, $access, $accessUser, $accessOperation, $resolver, $context) {
        return $this->getReferencedEntities($type, $language, $bundles, $access, $accessUser, $accessOperation, $resolver, $context);
      });
    }

    return [];
  }

}
