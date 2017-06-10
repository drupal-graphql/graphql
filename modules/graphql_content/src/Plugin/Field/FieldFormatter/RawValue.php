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
 *   label = @Translation("Raw value"),
 *   deriver = "Drupal\graphql_content\Plugin\Deriver\RawValueFormatterDeriver"
 * )
 */
class RawValue extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    // TODO The following code triggers and error in Drupal\Core\Render\Element::children,
    //      line 92 "'value' is an invalid render key".

    foreach ($items as $delta => $item) {
      $element[] = $item->getValue();
    }

    return $element;
  }

}
