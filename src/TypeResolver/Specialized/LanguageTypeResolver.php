<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\Specialized\LanguageTypeResolver.
 */

namespace Drupal\graphql\TypeResolver\Specialized;

use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\Plugin\DataType\Language;
use Drupal\graphql\TypeResolverInterface;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;

/**
 * Resolves typed data types.
 */
class LanguageTypeResolver implements TypeResolverInterface {
  /**
   * @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType
   */
  protected $languageType;

  /**
   * {@inheritdoc}
   */
  public function applies($type) {
    if ($type instanceof DataDefinitionInterface) {
      return $type->getDataType() === 'language';
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRecursive($type) {
    if ($type instanceof DataDefinitionInterface && $type->getDataType() === 'language') {
      return $this->getLanguageType();
    }

    return NULL;
  }

  /**
   * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType
   */
  protected function getLanguageType() {
    if (!isset($this->languageType)) {
      $this->languageType = new ObjectType(
        'Language', [
          'id' => [
            'type' => new NonNullModifier(Type::idType()),
            'resolve' => [__CLASS__, 'resolveId'],
          ],
          'name' => [
            'type' => new NonNullModifier(Type::stringType()),
            'resolve' => [__CLASS__, 'resolveName'],
          ],
          'direction' => [
            'type' => new NonNullModifier(Type::stringType()),
            'resolve' => [__CLASS__, 'resolveDirection'],
          ],
          'weight' => [
            'type' => new NonNullModifier(Type::intType()),
            'resolve' => [__CLASS__, 'resolveWeight'],
          ],
          'locked' => [
            'type' => new NonNullModifier(Type::booleanType()),
            'resolve' => [__CLASS__, 'resolveIsLocked'],
          ],
          'default' => [
            'type' => new NonNullModifier(Type::booleanType()),
            'resolve' => [__CLASS__, 'resolveIsDefault'],
          ],
        ]
      );
    }

    return $this->languageType;
  }

  /**
   * Gets the name of the language.
   *
   * @param \Drupal\Core\TypedData\Plugin\DataType\Language
   *   The language object.
   *
   * @return string The human-readable name of the language (in the language that was
   * The human-readable name of the language (in the language that was
   * used to construct this object).
   */
  public static function resolveName(Language $language) {
    return $language->getValue()->getName();
  }

  /**
   * Gets the ID (language code).
   *
   * @param \Drupal\Core\TypedData\Plugin\DataType\Language
   *   The language object.
   *
   * @return string
   *   The language code.
   */
  public static function resolveId(Language $language) {
    return $language->getValue()->getId();
  }

  /**
   * Gets the text direction (left-to-right or right-to-left).
   *
   * @param \Drupal\Core\TypedData\Plugin\DataType\Language
   *   The language object.
   *
   * @return string
   *   Either self::DIRECTION_LTR or self::DIRECTION_RTL.
   */
  public static function resolveDirection(Language $language) {
    return $language->getValue()->getDirection();
  }

  /**
   * Gets the weight of the language.
   *
   * @param \Drupal\Core\TypedData\Plugin\DataType\Language
   *   The language object.
   *
   * @return int
   *   The weight, used to order languages with larger positive weights sinking
   *   items toward the bottom of lists.
   */
  public static function resolveWeight(Language $language) {
    return $language->getValue()->getWeight();
  }

  /**
   * Returns whether this language is the default language.
   *
   * @param \Drupal\Core\TypedData\Plugin\DataType\Language
   *   The language object.
   *
   * @return bool
   *   Whether the language is the default language.
   */
  public static function resolveIsDefault(Language $language) {
    return $language->getValue()->isDefault();
  }

  /**
   * Returns whether this language is locked.
   *
   * @param \Drupal\Core\TypedData\Plugin\DataType\Language
   *   The language object.
   *
   * @return bool
   *   Whether the language is locked or not.
   */
  public static function resolveIsLocked(Language $language) {
    return $language->getValue()->isLocked();
  }
}
