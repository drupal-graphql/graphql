<?php

declare(strict_types=1);

namespace Drupal\graphql_composable\Plugin\GraphQL\DataProducer;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Response\Response;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Get the violations from a response.
 */
#[DataProducer(
  id: 'response_violations',
  name: new TranslatableMarkup('Response Violations'),
  description: new TranslatableMarkup('Get the violations from a Response.'),
  produces: new ContextDefinition(
    data_type: 'any',
    label: new TranslatableMarkup('Violations'),
  ),
  consumes: [
    'response' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Response'),
    ),
  ],
)]
class ResponseViolations extends DataProducerPluginBase {

  /**
   * Return the violations from the response.
   *
   * @param \Drupal\graphql\GraphQL\Response\Response $response
   *   The response.
   *
   * @return array
   *   The list of violations.
   */
  public function resolve(Response $response): array {
    return $response->getViolations();
  }

}
