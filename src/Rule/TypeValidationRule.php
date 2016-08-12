<?php

namespace Drupal\graphql\Rule;

use Youshido\GraphQL\Type\TypeService;
use Youshido\GraphQL\Validator\ConfigValidator\Rules\TypeValidationRule as BaseTypeValidationRule;

class TypeValidationRule extends BaseTypeValidationRule {

  /**
   * {@inheritdoc}
   */
  public function validate($data, $ruleInfo) {
    if (!is_string($ruleInfo)) {
      return false;
    }

    if (($ruleInfo == TypeService::TYPE_CALLABLE) && (is_callable($data) || (is_array($data) && count($data) == 2 && substr($data[0], 0, 1) == '@'))) {
      return true;
    }

    return parent::validate($data, $ruleInfo);
  }
}