<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "settings" from a given field definition.
 */
#[DataProducer(
  id: 'entity_definition_field_settings',
  name: new TranslatableMarkup('Entity definition field settings'),
  description: new TranslatableMarkup('Return entity definition field settings.'),
  produces: new ContextDefinition(
    data_type: 'string',
    label: new TranslatableMarkup('Entity definition field settings'),
  ),
  consumes: [
    'entity_definition_field' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Entity definition field'),
    ),
    'entity_form_display_context' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Entity form display context'),
      required: FALSE,
    ),
  ],
)]
class Settings extends DataProducerPluginBase {

  /**
   * Resolves the field settings.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $entity_definition_field
   *   The entity field definition.
   * @param \Drupal\Core\Entity\Entity\EntityFormDisplay|null $entity_form_display_context
   *   Entity form display context.
   *
   * @return \Iterator
   *   Field settings.
   */
  public function resolve(
    FieldDefinitionInterface $entity_definition_field,
    ?EntityFormDisplay $entity_form_display_context,
  ): \Iterator {
    $settings = $entity_definition_field->getSettings();

    if ($entity_form_display_context) {
      $content = $entity_form_display_context->get('content');
      $field_id = $entity_definition_field->getName();
      if (isset($content[$field_id])) {
        $form_settings = $content[$field_id]['settings'];
        $settings['form_settings'] = $form_settings;
      }
    }

    foreach ($settings as $key => $value) {
      yield [
        'key' => $key,
        'value' => $value,
      ];
    }
  }

}
