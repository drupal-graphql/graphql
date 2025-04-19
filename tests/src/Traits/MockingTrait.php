<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Traits;

use Drupal\Tests\RandomGeneratorTrait;
use Drupal\graphql\Entity\Server;
use Drupal\graphql\Entity\ServerInterface;
use Drupal\graphql\GraphQL\Resolver\Callback;
use Drupal\graphql\GraphQL\Resolver\ResolverInterface;
use Drupal\graphql\GraphQL\Resolver\Value;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\DataProducerPluginManager;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase;
use Drupal\graphql\Plugin\SchemaExtensionPluginManager;
use Drupal\graphql\Plugin\SchemaPluginInterface;
use Drupal\graphql\Plugin\SchemaPluginManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;

/**
 * Contains helpers for setting up mock servers and schemas for testing.
 */
trait MockingTrait {
  use RandomGeneratorTrait;

  /**
   * The server under test.
   */
  protected ServerInterface $server;

  /**
   * The resolver registry.
   */
  protected ResolverRegistry $registry;

  /**
   * The schema plugin under test.
   */
  protected MockObject&SchemaPluginInterface $schema;

  /**
   * The schema plugin manager.
   */
  protected MockObject&SchemaPluginManager $schemaPluginManager;

  /**
   * The data producer plugin manager.
   */
  protected MockObject&DataProducerPluginManager $dataProducerPluginManager;

  /**
   * Turn a value into a result promise.
   *
   * @param mixed $value
   *   The return value. Can also be a value callback.
   *
   * @return \PHPUnit\Framework\MockObject\Stub\ReturnCallback
   *   The return callback promise.
   */
  protected function toPromise(mixed $value): ReturnCallback {
    // @phpstan-ignore-next-line
    return $this->returnCallback(is_callable($value) ? $value : function () use ($value) {
      yield $value;
    });
  }

  /**
   * Turn a value into a bound result promise.
   *
   * @param mixed $value
   *   The return value. Can also be a value callback.
   * @param mixed $scope
   *   The resolver's bound object and class scope.
   *
   * @return \PHPUnit\Framework\MockObject\Stub\ReturnCallback
   *   The return callback promise.
   */
  protected function toBoundPromise(mixed $value, mixed $scope): ReturnCallback {
    return $this->toPromise(is_callable($value) ? \Closure::bind($value, $scope, $scope) : $value);
  }

  /**
   * Setup server with schema.
   *
   * @param string $schema
   *   GraphQL schema description.
   * @param string $id
   *   Schema id.
   * @param array $values
   *   Server entity values.
   */
  protected function setUpSchema(string $schema, string $id = 'test', array $values = []): void {
    $this->mockSchema($id, $schema);
    $this->mockSchemaPluginManager($id);
    $this->createTestServer($id, '/graphql/' . $id, $values);

    $this->schemaPluginManager->method('createInstance')
      ->with($this->equalTo($id))
      ->willReturn($this->schema);

    $this->container->set('plugin.manager.graphql.schema', $this->schemaPluginManager);
  }

  /**
   * Create test server.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createTestServer(string $schema, string $endpoint, array $values = []): void {
    $this->server = Server::create([
      'schema' => $schema,
      'name' => $this->randomMachineName(),
      'endpoint' => $endpoint,
    ] + $values);

    $this->server->save();
  }

  /**
   * Mock a schema instance.
   *
   * @param string $id
   *   The schema id.
   * @param string $schema
   *   The schema.
   * @param array<\Drupal\graphql\Plugin\SchemaExtensionPluginInterface> $extensions
   *   An array of schema extension plugins.
   */
  protected function mockSchema(string $id, string $schema, array $extensions = []): void {
    /** @var \PHPUnit\Framework\MockObject\MockObject $extensionManager */
    $extensionManager = $this->getMockBuilder(SchemaExtensionPluginManager::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getExtensions'])
      ->getMock();

    $extensionManager->expects(static::any())
      ->method('getExtensions')
      ->willReturn($extensions);

    $this->schema = $this->getMockBuilder(SdlSchemaPluginBase::class)
      ->setConstructorArgs([
        [],
        $id,
        [],
        $this->container->get('cache.graphql.ast'),
        $this->container->get('module_handler'),
        $extensionManager,
        ['development' => FALSE],
        $this->container->get('event_dispatcher'),
      ])
      ->onlyMethods(['getSchemaDefinition', 'getResolverRegistry'])
      ->getMock();

    $this->schema->expects(static::any())
      ->method('getSchemaDefinition')
      ->willReturn($schema);

    $this->registry = new ResolverRegistry();
    $this->schema->expects($this->any())
      ->method('getResolverRegistry')
      ->willReturn($this->registry);
  }

  /**
   * Mock schema plugin manager.
   */
  protected function mockSchemaPluginManager(string $id): void {
    $this->schemaPluginManager = $this->getMockBuilder(SchemaPluginManager::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->schemaPluginManager->expects($this->any())
      ->method('getDefinitions')
      ->willReturn([
        $id => [
          'id' => $id,
          'name' => 'Test schema',
          'provider' => 'graphql',
          'class' => '\Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase',
        ],
      ]);
  }

  /**
   * Mock data producer field.
   *
   * @param string $type
   *   Parent Type.
   * @param string $field
   *   Field name.
   * @param mixed|\Drupal\graphql\GraphQL\Resolver\ResolverInterface $resolver
   *   Resolver.
   */
  protected function mockResolver(string $type, string $field, mixed $resolver = NULL): void {
    if (is_callable($resolver)) {
      $resolver = new Callback($resolver);
    }

    if (!($resolver instanceof ResolverInterface)) {
      $resolver = new Value($resolver);
    }

    $this->registry->addFieldResolver($type, $field, $resolver);
  }

  /**
   * Mock type resolver.
   *
   * @param string $type
   *   Parent Type.
   * @param callable $resolver
   *   Type resolver.
   */
  protected function mockTypeResolver(string $type, callable $resolver): void {
    $this->registry->addTypeResolver($type, $resolver);
  }

}
