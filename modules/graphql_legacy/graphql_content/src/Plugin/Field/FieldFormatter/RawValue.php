<?php

namespace Drupal\graphql_content\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\graphql_content\GraphQLFieldFormatterBase;

/**
 * Plugin implementation of the 'raw_value' formatter.
 *
 * @FieldFormatter(
 *   id = "graphql_raw_value",
 *   label = @Translation("GraphQL Raw value"),
 *   deriver = "Drupal\graphql_content\Plugin\Deriver\RawValueFormatterDeriver"
 * )
 */
class RawValue extends GraphQLFieldFormatterBase {

}
