<?php

declare(strict_types=1);

namespace Drupal\graphql_composable\Plugin\GraphQL\DataProducer;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\graphql_examples\Wrappers\QueryConnection;
use GraphQL\Deferred;

/**
 * Get the items of a connection.
 */
#[DataProducer(
  id: 'connection_items',
  name: new TranslatableMarkup('Connection Items'),
  description: new TranslatableMarkup('The items of a connection.'),
  produces: new ContextDefinition(
    data_type: 'any',
    label: new TranslatableMarkup('Items'),
  ),
  consumes: [
    'connection' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Connection'),
    ),
  ],
)]
class ConnectionItems extends DataProducerPluginBase {

  /**
   * Return the items of a Connection.
   *
   * @param \Drupal\graphql_examples\Wrappers\QueryConnection $connection
   *   The response.
   *
   * @return array|\GraphQL\Deferred
   *   The items.
   */
  public function resolve(QueryConnection $connection): array|Deferred {
    return $connection->items();
  }

}
