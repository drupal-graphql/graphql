<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\node\Entity\Node;

/**
 * Tests some custom schema.
 */
class CustomSchemaTest extends QueryTestBase  {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql_test_custom_schema'];

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    parent::register($container);

    $container->setParameter('http.response.debug_cacheability_headers', TRUE);
  }

  public function testSchemaWithCaching() {
    Node::create([
      'type' => 'article',
      'title' => 'giraffe',
    ])->save();

    $query = <<<GQL
{
  articleById(id: 1) {
    title
  }
}
    
GQL;

    $response = $this->query($query);
    $data = json_decode($response->getContent(), TRUE);
    $this->assertEquals([
      'data' => [
        'articleById' => [
          'title' => 'giraffe',
        ],
      ],
    ], $data);

    $this->assertEquals('config:user.role.anonymous node:1', $response->headers->get('X-Drupal-Cache-Tags', NULL));
  }

}
