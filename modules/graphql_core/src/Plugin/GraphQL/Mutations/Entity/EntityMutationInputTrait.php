<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Mutations\Entity;

use Drupal\graphql_core\Plugin\GraphQL\InputTypes\Mutations\EntityInput;
use Drupal\graphql_core\Plugin\GraphQL\InputTypes\Mutations\EntityInputField;
use Youshido\GraphQL\Type\Scalar\AbstractScalarType;

trait EntityMutationInputTrait {

  /**
   * Extract entity values from the resolver args.
   *
   * Loops over all input values and assigns them to their original field names.
   *
   * @param array $inputValue
   *   The entity values provided through the resolver args.
   * @param \Drupal\graphql_core\Plugin\GraphQL\InputTypes\Mutations\EntityInput $inputType
   *   The input type.
   *
   * @return array
   *   The extracted entity values with their proper, internal field names.
   */
  protected function extractEntityInput(array $inputValue, EntityInput $inputType) {
    $fields = $inputType->getPluginDefinition()['fields'];
    return array_reduce(array_keys($inputValue), function($carry, $current) use ($fields, $inputValue, $inputType) {
      $isMulti = $fields[$current]['multi'];
      $fieldName = $fields[$current]['field_name'];
      $fieldValue = $inputValue[$current];
      $fieldType = $inputType->getField($current)->getType()->getNamedType();

      if ($fieldType instanceof AbstractScalarType) {
        return $carry + [$fieldName => $fieldValue];
      }

      if ($fieldType instanceof EntityInputField) {
        $fieldValue = $isMulti ? array_map(function($value) use ($fieldType) {
          return $this->extractEntityFieldInput($value, $fieldType);
        }, $fieldValue) : $this->extractEntityFieldInput($fieldValue, $fieldType);

        return $carry + [$fieldName => $fieldValue];
      }

      return $carry;
    }, []);
  }

  /**
   * Extract property values from field values from the resolver args.
   *
   * Loops over all field properties and assigns them to their original property
   * names.
   *
   * @param array $fieldValue
   *   The field values keyed by property name.
   * @param \Drupal\graphql_core\Plugin\GraphQL\InputTypes\Mutations\EntityInputField $fieldType
   *   The field type.
   *
   * @return array
   *   The extracted field values with their proper, internal property names.
   */
  protected function extractEntityFieldInput(array $fieldValue, EntityInputField $fieldType) {
    $properties = $fieldType->getPluginDefinition()['fields'];
    return array_reduce(array_keys($fieldValue), function($carry, $current) use ($properties, $fieldValue) {
      $key = $properties[$current]['property_name'];
      $value = $fieldValue[$current];

      return $carry + [$key => $value];
    }, []);
  }

}