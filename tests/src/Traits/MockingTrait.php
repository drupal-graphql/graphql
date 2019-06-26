<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\graphql\GraphQL\Resolver\Callback;
use Drupal\graphql\GraphQL\Resolver\ResolverInterface;
use Drupal\graphql\GraphQL\Resolver\Value;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlExtendedSchemaPluginBase;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase;
use Drupal\graphql\Plugin\SchemaPluginManager;
use Drupal\graphql\Entity\Server;
use Drupal\graphql\GraphQL\ResolverRegistry;

trait MockingTrait {

  /**
   * @var \Drupal\graphql\Entity\Server
   */
  protected $server;

  /**
   * @var \Drupal\graphql\GraphQL\ResolverRegistry
   */
  protected $registry;

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject
   */
  protected $schema;

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject
   */
  protected $schemaPluginManager;

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject
   */
  protected $dataProducerPluginManager;

  /**
   * Turn a value into a result promise.
   *
   * @param mixed $value
   *   The return value. Can also be a value callback.
   *
   * @return \PHPUnit_Framework_MockObject_Stub_ReturnCallback
   *   The return callback promise.
   */
  protected function toPromise($value) {
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
   * @return \PHPUnit_Framework_MockObject_Stub_ReturnCallback
   *   The return callback promise.
   */
  protected function toBoundPromise($value, $scope) {
    return $this->toPromise(is_callable($value) ? \Closure::bind($value, $scope, $scope) : $value);
  }

  /**
   * Setup server with schema.
   *
   * @param string $schema
   *   GraphQL schema description.
   * @param string $id
   *   Schema id.
   * @param bool $development
   */
  protected function setUpSchema($schema, $id = 'test', $development = FALSE) {
    $this->mockSchema($id, $schema, $development);
    $this->mockSchemaPluginManager($id);
    $this->createTestServer($id, '/graphql/' . $id, $development);

    $this->schemaPluginManager->method('createInstance')
      ->with($this->equalTo($id))
      ->will($this->returnValue($this->schema));

    $this->container->set('plugin.manager.graphql.schema', $this->schemaPluginManager);
  }

  /**
   * Setup server with extended schema.
   *
   * @param string $schema
   *   Base schema.
   * @param string $schemaExtension
   *   Schema extension.
   * @param string $id
   *   Schema id.
   * @param bool $development
   */
  protected function setUpExtendedSchema($schema, $schemaExtension, $id = 'test', $development = FALSE) {
    $this->mockExtendedSchema($id, $schema, $schemaExtension, $development);
    $this->mockSchemaPluginManager($id);
    $this->createTestServer($id, '/graphql/' . $id, $development);

    $this->schemaPluginManager->method('createInstance')
      ->with($this->equalTo($id))
      ->will($this->returnValue($this->schema));

    $this->container->set('plugin.manager.graphql.schema', $this->schemaPluginManager);
  }

  /**
   * Create test server.
   *
   * @param $schema
   * @param $endpoint
   * @param bool $debug
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createTestServer($schema, $endpoint, $debug = FALSE) {
    $this->server = Server::create([
      'schema' => $schema,
      'name' => $schema,
      'endpoint' => $endpoint,
      'debug' => $debug,
    ]);

    $this->server->save();
  }

  /**
   * Mock a schema instance.
   *
   * @param string $id
   *   The schema id.
   * @param string $schema
   *   GraphQL schema.
   * @param boolean $development
   *   Schema development mode.
   */
  protected function mockSchema($id, $schema, $development = FALSE) {
    $this->schema = $this->getMockBuilder(SdlSchemaPluginBase::class)
      ->setConstructorArgs([
        [],
        $id,
        [],
        $this->container->get('cache.graphql.ast'),
        ['development' => $development]
      ])
      ->setMethods(['getSchemaDefinition', 'getResolverRegistry'])
      ->getMockForAbstractClass();

    $this->schema->expects(static::any())
      ->method('getSchemaDefinition')
      ->willReturn($schema);

    $this->registry = new ResolverRegistry([]);
    $this->schema->expects($this->any())
      ->method('getResolverRegistry')
      ->willReturn($this->registry);
  }

  /**
   * Mock an extended schema instance.
   *
   * @param string $id
   *   The schema id.
   * @param string $schema
   *   GraphQL schema.
   * @param string $schemaExtension
   *   GraphQL extended schema.
   * @param boolean $development
   *   Schema development mode.
   */
  protected function mockExtendedSchema($id, $schema, $schemaExtension, $development = FALSE) {
    $this->schema = $this->getMockBuilder(SdlExtendedSchemaPluginBase::class)
      ->setConstructorArgs([
        [],
        $id,
        [],
        $this->container->get('cache.graphql.ast'),
        ['development' => $development]
      ])
      ->setMethods(['getSchemaDefinition', 'getExtendedSchemaDefinition', 'getResolverRegistry'])
      ->getMockForAbstractClass();

    $this->schema->expects(static::any())
      ->method('getSchemaDefinition')
      ->willReturn($schema);

    $this->schema->expects(static::any())
      ->method('getExtendedSchemaDefinition')
      ->willReturn($schemaExtension);

    $this->registry = new ResolverRegistry();
    $this->schema->expects($this->any())
      ->method('getResolverRegistry')
      ->willReturn($this->registry);
  }

  /**
   * Mock schema plugin manager.
   *
   * @param $id
   */
  protected function mockSchemaPluginManager($id) {
    $this->schemaPluginManager = $this->getMockBuilder(SchemaPluginManager::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->schemaPluginManager->expects($this->any())
      ->method('getDefinitions')
      ->will($this->returnValue([
        $id => [
          'id' => $id,
          'name' => 'Test schema',
          'provider' => 'graphql',
          'class' => '\Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase'
        ]
      ]));
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
  protected function mockResolver($type, $field, $resolver = NULL) {
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
  protected function mockTypeResolver($type, callable $resolver) {
    $this->registry->addTypeResolver($type, $resolver);
  }

}
