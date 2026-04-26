<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\Entity\BaseFieldOverride;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\field\Entity\FieldConfig;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Retrieve the list of fields from a given entity definition.
 */
#[DataProducer(
  id: 'entity_definition_fields',
  name: new TranslatableMarkup('Entity definition fields'),
  description: new TranslatableMarkup('Return entity definition fields.'),
  produces: new ContextDefinition(
    data_type: 'any',
    label: new TranslatableMarkup('Entity definition field'),
  ),
  consumes: [
    'entity_definition' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Entity definition'),
    ),
    'bundle_context' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Bundle context'),
      required: FALSE,
    ),
    'field_types_context' => new ContextDefinition(
      data_type: 'string',
      label: new TranslatableMarkup('Field types context'),
      required: FALSE,
    ),
  ],
)]
class Fields extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EntityFieldManagerInterface $entityFieldManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
   * @param string|null $field_types_context
   *   Field types context.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $field_context
   *   Field context.
   */
  public function resolve(
    EntityTypeInterface $entity_definition,
    ?array $bundle_context,
    ?string $field_types_context,
    FieldContext $field_context,
  ): \Iterator {

    if ($entity_definition instanceof ContentEntityTypeInterface) {
      $entity_type_id = $entity_definition->id();
      if ($bundle_context) {
        $key = $bundle_context['key'];
        $fields = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $key);

        // Set entity form default display as context.
        $form_display_id = $entity_type_id . '.' . $key . '.default';
        $form_display_context = $this->entityTypeManager
          ->getStorage('entity_form_display')
          ->load($form_display_id);
        $field_context->setContextValue('entity_form_display', $form_display_context);
      }
      else {
        $fields = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $entity_type_id);
      }

      if ($field_types_context) {
        foreach ($fields as $field) {
          if ($field_types_context === 'BASE_FIELDS') {
            if ($field instanceof BaseFieldDefinition) {
              yield $field;
            }
          }
          elseif ($field_types_context === 'FIELD_CONFIG') {
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
