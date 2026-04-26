<?php

declare(strict_types=1);

namespace Drupal\graphql_composable\Plugin\GraphQL\DataProducer;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\graphql_composable\GraphQL\Response\ArticleResponse;

/**
 * Get the article from an article response.
 */
#[DataProducer(
  id: 'article_response_article',
  name: new TranslatableMarkup('Article Response Article'),
  description: new TranslatableMarkup('Get the article from an ArticleResponse.'),
  produces: new ContextDefinition(
    data_type: 'any',
    label: new TranslatableMarkup('Article'),
  ),
  consumes: [
    'response' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('ArticleResponse'),
    ),
  ],
)]
class ArticleResponseArticle extends DataProducerPluginBase {

  /**
   * Return the article from the response.
   *
   * @param \Drupal\graphql_composable\GraphQL\Response\ArticleResponse $response
   *   The response.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The article.
   */
  public function resolve(ArticleResponse $response): EntityInterface {
    return $response->article();
  }

}
