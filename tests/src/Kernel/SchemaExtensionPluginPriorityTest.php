<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\Resolver\ResolverInterface;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\GraphQL\Schema\ComposableSchema;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Tests ordering schema extension plugins by priority.
 *
 * @group graphql
 */
class SchemaExtensionPluginPriorityTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'graphql_schema_extension_test',
  ];

  /**
   * Tests that the schema extension plugin manager orders plugins by priority.
   */
  public function testSchemaExtensionPluginPriority(): void {
    /** @var \Drupal\graphql\Plugin\SchemaExtensionPluginManager $schemaExtensionPluginManager */
    $schemaExtensionPluginManager = $this->container->get('plugin.manager.graphql.schema_extension');
    $extensions = $schemaExtensionPluginManager->getExtensions('composable');

    // Check that the returned extensions are ordered by priority.
    $expected_order = [
      'high_priority_test' => 'high_priority_test',
      'test' => 'test',
      'low_priority_test' => 'low_priority_test',
    ];
    $actual_order = array_map(static fn ($extension) => $extension->getPluginId(), $extensions);
    static::assertEquals($expected_order, $actual_order);
  }

  /**
   * Tests that schema extension plugins can be overridden.
   *
   * When using a composable schema, it should be possible to override field
   * definitions from other schema extension plugins. This is done by setting a
   * lower priority on the overriding plugin so that it will be processed after
   * the plugin it is overriding.
   *
   * @dataProvider composableSchemaExtensionOverridingProvider
   */
  public function testComposableSchemaExtensionOverriding(array $extensions, string $expected_result): void {
    $schemaPlugin = $this->getMockedSchemaPlugin($extensions);
    $registry = new ResolverRegistry();
    $schemaPlugin->getSchema($registry);
    $resolver = $registry->getFieldResolver('TestType', 'pluginId');
    $result = $this->resolve($resolver);
    static::assertEquals($expected_result, $result);
  }

  /**
   * Data provider for testComposableSchemaExtensionOverriding().
   */
  public static function composableSchemaExtensionOverridingProvider(): array {
    return [
      // When a single extension is used in a schema, no overriding takes place.
      [
        ['test'],
        'test',
      ],
      // A higher priority extension is executed first and cannot override the
      // default extension.
      [
        ['high_priority_test', 'test'],
        'test',
      ],
      // A lower priority extension is executed last and can override all
      // previous extensions.
      [
        ['high_priority_test', 'test', 'low_priority_test'],
        'low_priority_test',
      ],
      // The order of the extensions as defined in the plugin manager does not
      // influence the order in which they are executed.
      [
        ['low_priority_test', 'test', 'high_priority_test'],
        'low_priority_test',
      ],
    ];
  }

  /**
   * Returns a mocked schema plugin.
   */
  protected function getMockedSchemaPlugin(array $extensions): ComposableSchema {
    $schemaPlugin = $this->getMockBuilder(ComposableSchema::class)
      ->setConstructorArgs([
        [],
        'composable',
        [],
        $this->container->get('cache.graphql.ast'),
        $this->container->get('module_handler'),
        $this->container->get('plugin.manager.graphql.schema_extension'),
        ['development' => FALSE],
      ])
      ->onlyMethods(['getConfiguration', 'getResolverRegistry'])
      ->getMock();

    $schemaPlugin->expects(static::any())
      ->method('getConfiguration')
      ->willReturn([
        'extensions' => $extensions,
        'server_id' => 'test',
      ]);

    $registry = new ResolverRegistry();
    $schemaPlugin->expects($this->any())
      ->method('getResolverRegistry')
      ->willReturn($registry);

    return $schemaPlugin;
  }

  /**
   * Returns the result of a resolver.
   *
   * @param \Drupal\graphql\GraphQL\Resolver\ResolverInterface $resolver
   *   The resolver to execute.
   *
   * @return mixed
   *   The result of the resolver.
   */
  protected function resolve(ResolverInterface $resolver) {
    $resolveContext = $this->getMockBuilder(ResolveContext::class)
      ->disableOriginalConstructor()
      ->getMock();
    $resolveInfo = $this->getMockBuilder(ResolveInfo::class)
      ->disableOriginalConstructor()
      ->getMock();
    $fieldContext = $this->getMockBuilder(FieldContext::class)
      ->disableOriginalConstructor()
      ->getMock();
    return $resolver->resolve(NULL, NULL, $resolveContext, $resolveInfo, $fieldContext);
  }

}
