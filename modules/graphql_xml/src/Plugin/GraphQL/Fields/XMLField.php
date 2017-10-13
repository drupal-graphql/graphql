<?php

namespace Drupal\graphql_xml\Plugin\GraphQL\Fields;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\text\Plugin\Field\FieldType\TextItemBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Expose text fields as xml documents.
 *
 * @GraphQLField(
 *   id = "xml_field",
 *   secure = true,
 *   field_formatter = "graphql_xml",
 *   type = "XMLElement",
 *   deriver = "Drupal\graphql_content\Plugin\Deriver\FieldFormatterDeriver"
 * )
 */
class XMLField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ContentEntityInterface) {
      foreach ($value->get($this->getPluginDefinition()['field']) as $field) {
        if ($field instanceof TextItemBase) {
          $document = new \DOMDocument();
          $document->loadXML('<div>' . $field->getValue()['value'] . '</div>');
          yield $document->documentElement;
        }
      }
    }
  }

}
