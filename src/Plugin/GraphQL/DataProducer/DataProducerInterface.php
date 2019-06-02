<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * An interface defining a data producer.
 */
interface DataProducerInterface {

  /**
   * Resolve values for the fields.
   *
   * @param $value
   * @param $args
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return mixed
   * @throws \Exception
   */
  public function resolve($value, $args, ResolveContext $context, ResolveInfo $info);

}
