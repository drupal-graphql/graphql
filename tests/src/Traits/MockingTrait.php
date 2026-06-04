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
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql\Plugin\SchemaExtensionPluginInterface;
use Drupal\graphql\Plugin\SchemaExtensionPluginManager;
use Drupal\graphql\Plugin\SchemaPluginInterface;
use Drupal\graphql\Plugin\SchemaPluginManager;
use GraphQL\Language\Source;
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
   * @param array<\Drupal\graphql\Plugin\SchemaExtensionPluginInterface> $extensions
   *   An array of schema extension plugins.
   */
  protected function setUpSchema(string $schema, string $id = 'test', array $values = [], array $extensions = []): void {
    $this->mockSchema($id, $schema, $extensions);
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
   * @return \Drupal\graphql\Entity\ServerInterface
   *   The created server entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createTestServer(string $schema, string $endpoint, array $values = []): ServerInterface {
    $this->server = Server::create([
      'schema' => $schema,
      'name' => $this->randomMachineName(),
      'endpoint' => $endpoint,
    ] + $values);

    $this->server->save();

    return $this->server;
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
      ->onlyMethods(['getSchemaDefinition', 'registerResolvers', 'createResolverRegistry'])
      ->getMock();

    $this->schema->expects(static::any())
      ->method('getSchemaDefinition')
      ->willReturn(new Source($schema));

    // Create a shared reference to the registry so we can register resolvers
    // after SdlSchemaPluginBase registers them all.
    $this->registry = new ResolverRegistry();
    $this->schema->expects($this->any())
      ->method('createResolverRegistry')
      ->willReturn($this->registry);
  }

  /**
   * Mock schema plugin manager.
   */
  protected function mockSchemaPluginManager(string $id): void {
    $this->schemaPluginManager = $this->getMockBuilder(SchemaPluginManager::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getDefinitions', 'createInstance'])
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
   * Mock a schema extension plugin.
   *
   * @param string $id
   *   The plugin ID.
   * @param string|null $baseDefinition
   *   The base schema definition for the plugin.
   * @param string|null $extensionDefinition
   *   The extension definition for the plugin.
   *
   * @return \Drupal\graphql\Plugin\SchemaExtensionPluginInterface
   *   The mocked extension plugin.
   */
  protected function mockSchemaExtension(string $id, ?string $baseDefinition, ?string $extensionDefinition): SchemaExtensionPluginInterface {
    $extension = $this->getMockBuilder(SdlSchemaExtensionPluginBase::class)
      ->setConstructorArgs([
        [],
        $id,
        [],
        $this->container->get('module_handler'),
      ])
      ->onlyMethods(['getBaseDefinition', 'getExtensionDefinition', 'registerResolvers'])
      ->getMock();

    $extension->expects(static::any())
      ->method('getBaseDefinition')
      ->willReturn($baseDefinition ? new Source($baseDefinition) : NULL);
    $extension->expects(static::any())
      ->method('getExtensionDefinition')
      ->willReturn($extensionDefinition ? new Source($extensionDefinition) : NULL);

    return $extension;
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
      // To make sure we don't suddenly break people's tests we trigger a
      // deprecation here but still forward to our deprecated class.
      @trigger_error("Calling MockingTrait::mockResolver() with a callable is deprecated in graphql:5.0.0 and is removed from graphql:6.0.0. Create a test data producer class instead. See https://www.drupal.org/node/3576383", E_USER_DEPRECATED);
      // @phpstan-ignore-next-line
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
