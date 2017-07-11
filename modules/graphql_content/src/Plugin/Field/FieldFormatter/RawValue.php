<?php

namespace Drupal\graphql_content\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'raw_value' formatter.
 *
 * @FieldFormatter(
 *   id = "raw_value",
 *   label = @Translation("Raw value (GraphQL use only)"),
 *   deriver = "Drupal\graphql_content\Plugin\Deriver\RawValueFormatterDeriver"
 * )
 */
class RawValue extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    return ['#markup' => $this->t('Raw value formatter only makes sense in graphql context.')];
  }

}
