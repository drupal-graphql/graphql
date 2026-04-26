<?php

declare(strict_types=1);

namespace Drupal\graphql_composable\Plugin\GraphQL\DataProducer;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\graphql_examples\Wrappers\QueryConnection;

/**
 * Get the total number of items on a connection.
 */
#[DataProducer(
  id: 'connection_totals',
  name: new TranslatableMarkup('Connection Totals'),
  description: new TranslatableMarkup('The total number of items on a connection.'),
  produces: new ContextDefinition(
    data_type: 'any',
    label: new TranslatableMarkup('Totals'),
  ),
  consumes: [
    'connection' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Connection'),
    ),
  ],
)]
class ConnectionTotals extends DataProducerPluginBase {

  /**
   * Return the total number of items on a Connection.
   *
   * @param \Drupal\graphql_examples\Wrappers\QueryConnection $connection
   *   The response.
   *
   * @return int
   *   The number of items.
   */
  public function resolve(QueryConnection $connection): int {
    return $connection->total();
  }

}
