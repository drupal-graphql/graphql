<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition;

use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\Entity\BaseFieldOverride;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Retrieve the list of fields from a given entity definition.
 *
 * @DataProducer(
 *   id = "entity_definition_fields",
 *   name = @Translation("Entity definition fields"),
 *   description = @Translation("Return entity definition fields."),
 *   consumes = {
 *     "entity_definition" = @ContextDefinition("any",
 *       label = @Translation("Entity definition")
 *     ),
 *     "bundle_context" = @ContextDefinition("any",
 *       label = @Translation("Bundle context"),
 *       required = FALSE,
 *     ),
 *     "field_types_context" = @ContextDefinition("any",
 *       label = @Translation("Field types context"),
 *       required = FALSE,
 *     )
 *   },
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Entity definition field")
 *   )
 * )
 */
class Fields extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  protected $entityFieldManager;

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
      $container->get('entity_field.manager')
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
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManager $entity_field_manager
   *   The entity field manager service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    EntityTypeManager $entity_type_manager,
    EntityFieldManager $entity_field_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * Resolves the list of fields for a given entity.
   *
   * Respects the optional context parameters "bundle" and "field_types". If
   * bundle context is set it resolves the fields only for that entity bundle.
   * The same goes for field types when either base fields of configurable
   * fields may be returned.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_definition
   *   The entity type definition.
   * @param array|null $bundle_context
   *   Bundle context.
   * @param array|null $field_types_context
   *   Field types context.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $field_context
   *   Field context.
   */
  public function resolve(
    EntityTypeInterface $entity_definition,
    ?array $bundle_context = NULL,
    ?array $field_types_context = NULL,
    FieldContext $field_context
  ): \Iterator {
    $entity_definition->getBundleEntityType();
    if ($entity_definition instanceof ContentEntityType) {
      if ($bundle_context) {
        $key = $bundle_context['key'];
        $id = $entity_definition->id();
        $entity_id = $id . '.' . $id . '.' . $key;
        $fields = $this->entityFieldManager->getFieldDefinitions($id, $key);
      }
      else {
        $id = $entity_definition->id();
        $entity_id = $id . '.' . $id . '.default';
        $fields = $this->entityFieldManager->getFieldDefinitions($id, $id);
      }

      /** @var \Drupal\Core\Config\Entity\ConfigEntityStorage $form_display_context */
      $form_display_context = $this->entityTypeManager
        ->getStorage('entity_form_display')
        ->load($entity_id);

      $field_context->setContextValue('entity_form_display', $form_display_context);
      if ($field_types_context) {
        $field_types = $field_types_context['key'];
        foreach ($fields as $field) {
          if ($field_types === 'BASE_FIELDS') {
            if ($field instanceof BaseFieldDefinition) {
              yield $field;
            }
          }
          elseif ($field_types === 'FIELD_CONFIG') {
            if ($field instanceof FieldConfig || $field instanceof BaseFieldOverride) {
              yield $field;
            }
          }
          else {
            yield $field;
          }
        }
      }
      else {
        yield from $fields;
      }
    }
  }

}
