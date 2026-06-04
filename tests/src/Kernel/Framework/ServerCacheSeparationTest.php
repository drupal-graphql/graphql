<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\graphql\Entity\ServerInterface;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\GraphQL\Schema\ComposableSchema;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql\Plugin\SchemaExtensionPluginManager;
use Drupal\graphql\Plugin\SchemaPluginManager;
use GraphQL\Language\Source;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests result-cache separation between GraphQL servers.
 *
 * @group graphql
 */
class ServerCacheSeparationTest extends GraphQLTestBase {

  /**
   * First test server.
   */
  protected ServerInterface $serverA;

  /**
   * Second test server.
   */
  protected ServerInterface $serverB;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $schemaA = <<<GQL
      type NodeQuery {
        nodeLabel: String
      }
      schema {
        query: NodeQuery
      }
    GQL;
    $schema_a_plugin = $this->mockSchemaForServer('node', $schemaA);
    $schema_configuration_a = ['composable' => ['extensions' => ['node' => 'node']]];
    $this->serverA = $this->createTestServer('composable', '/graphql/node', ['schema_configuration' => $schema_configuration_a]);

    $schemaB = <<<GQL
      type TermQuery {
        termLabel: String
      }
      schema {
        query: TermQuery
      }
    GQL;
    $schema_b_plugin = $this->mockSchemaForServer('term', $schemaB);
    $schema_configuration_b = ['composable' => ['extensions' => ['term' => 'term']]];
    $this->serverB = $this->createTestServer('composable', '/graphql/term', ['schema_configuration' => $schema_configuration_b]);

    $schema_plugin_manager = $this->mockPluginManager();
    $schema_plugin_manager->method('createInstance')
      ->willReturnCallback(fn ($plugin_id, $plugin_config) => match ([$plugin_id, $plugin_config['server_id']]) {
        ['composable', $this->serverA->id()] => $schema_a_plugin,
        ['composable', $this->serverB->id()] => $schema_b_plugin,
        default => NULL,
      });

    $this->container->set('plugin.manager.graphql.schema', $schema_plugin_manager);
  }

  /**
   * Demonstrates that two servers must not share cached introspection results.
   */
  public function testServerCachesAreSeparated(): void {
    $query = '{ __schema { queryType { name } } }';

    // Make a request to the first server, this will populate the cache for
    // server A.
    $node_response = $this->query($query, $this->serverA);
    $this->assertSame(200, $node_response->getStatusCode());
    $node_data = json_decode($node_response->getContent(), TRUE);
    $this->assertSame('NodeQuery', $node_data['data']['__schema']['queryType']['name']);

    // Make a request to the second server, this should not return the cached
    // result from server A, but instead return the correct result for server B.
    $term_response = $this->query($query, $this->serverB);
    $this->assertSame(200, $term_response->getStatusCode());
    $term_data = json_decode($term_response->getContent(), TRUE);
    $this->assertSame('TermQuery', $term_data['data']['__schema']['queryType']['name']);
  }

  /**
   * Returns a schema plugin mock for a specific server schema.
   *
   * @param string $id
   *   Server key.
   * @param string $schema
   *   SDL schema.
   */
  protected function mockSchemaForServer(string $id, string $schema): ComposableSchema {
    $extension_manager = $this->getMockBuilder(SchemaExtensionPluginManager::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['createInstance'])
      ->getMock();

    $schema_extension_plugin = $this->getMockBuilder(SdlSchemaExtensionPluginBase::class)
      ->disableOriginalConstructor()
      ->getMock();

    $extension_manager->expects($this->any())
      ->method('createInstance')
      ->with($id)
      ->willReturn($schema_extension_plugin);

    $mock = $this->getMockBuilder(ComposableSchema::class)
      ->setConstructorArgs([
        [],
        'composable',
        [],
        $this->container->get('cache.graphql.ast'),
        $this->container->get('module_handler'),
        $extension_manager,
        ['development' => FALSE],
        $this->container->get('event_dispatcher'),
      ])
      ->onlyMethods(['getSchemaDefinition', 'getResolverRegistry', 'getConfiguration'])
      ->getMock();

    $mock->expects(static::any())
      ->method('getSchemaDefinition')
      ->willReturn(new Source($schema));

    $registry = new ResolverRegistry();
    $mock->expects($this->any())
      ->method('getResolverRegistry')
      ->willReturn($registry);

    $mock->expects($this->any())
      ->method('getConfiguration')
      ->willReturn([
        'extensions' => [$id => $id],
        'server_id' => $id,
      ]);

    return $mock;
  }

  /**
   * Returns a mocked schema plugin manager.
   *
   * @return \Drupal\graphql\Plugin\SchemaPluginManager|\PHPUnit\Framework\MockObject\MockObject
   *   The mocked schema plugin manager.
   */
  protected function mockPluginManager(): SchemaPluginManager|MockObject {
    $schema_plugin_manager = $this->getMockBuilder(SchemaPluginManager::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getDefinitions', 'createInstance'])
      ->getMock();

    $schema_plugin_manager->expects($this->any())
      ->method('getDefinitions')
      ->willReturn([
        'composable' => [
          'id' => 'composable',
          'name' => 'Composable schema',
          'provider' => 'graphql',
          'class' => ComposableSchema::class,
        ],
      ]);

    return $schema_plugin_manager;
  }

}
