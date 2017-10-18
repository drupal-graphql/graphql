<?php

namespace Drupal\graphql_content;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Base class for GraphQL placeholder field formatters.
 */
class GraphQLFieldFormatterBase extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $definition = $this->getPluginDefinition();
    return [
      '#markup' => '<!-- GraphQL field formatter ' . $definition['label'] . '. -->',
    ];
  }
}