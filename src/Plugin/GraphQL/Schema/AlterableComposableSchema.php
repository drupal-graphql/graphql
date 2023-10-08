<?php

namespace Drupal\graphql\Plugin\GraphQL\Schema;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\graphql\Event\AlterSchemaDataEvent;
use Drupal\graphql\Event\AlterSchemaExtensionDataEvent;
use Drupal\graphql\GraphQL\Utility\AST;
use Drupal\graphql\Plugin\SchemaExtensionPluginManager;
use GraphQL\Language\Parser;
use GraphQL\Language\Source;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Allows to alter the graphql files data before parsing.
 *
 * @see \Drupal\graphql\Event\AlterSchemaDataEvent
 * @see \Drupal\graphql\Event\AlterSchemaExtensionDataEvent
 *
 * @Schema(
 *   id = "alterable_composable",
 *   name = "Alterable composable schema"
 * )
 */
class AlterableComposableSchema extends ComposableSchema {

  /**
   * The event dispatcher service.
   *
   * @var \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  protected $dispatcher;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cache.graphql.ast'),
      $container->get('module_handler'),
      $container->get('plugin.manager.graphql.schema_extension'),
      $container->getParameter('graphql.config'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * AlterableComposableSchema constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param array $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\Core\Cache\CacheBackendInterface $astCache
   *   The cache bin for caching the parsed SDL.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\graphql\Plugin\SchemaExtensionPluginManager $extensionManager
   *   The schema extension plugin manager.
   * @param array $config
   *   The service configuration.
   * @param \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher $dispatcher
   *   The event dispatcher.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $pluginId,
    array $pluginDefinition,
    CacheBackendInterface $astCache,
    ModuleHandlerInterface $moduleHandler,
    SchemaExtensionPluginManager $extensionManager,
    array $config,
    ContainerAwareEventDispatcher $dispatcher
  ) {
    parent::__construct(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $astCache,
      $moduleHandler,
      $extensionManager,
      $config
    );
    $this->dispatcher = $dispatcher;
  }

  /**
   * Retrieves the parsed AST of the schema definition.
   *
   * Almost copy of the original method except it
   * provides alter schema event in order to manipulate data.
   *
   * @param array $extensions
   *   The Drupal GraphQl schema plugins data.
   *
   * @return \GraphQL\Language\AST\DocumentNode
   *   The parsed schema document.
   *
   * @throws \GraphQL\Error\SyntaxError
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *
   * @see \Drupal\graphql\Plugin\GraphQL\Schema\ComposableSchema::getSchemaDocument()
   */
  protected function getSchemaDocument(array $extensions = []) {
    $baseDefinition = $this->getSchemaDefinition();
    if (!$baseDefinition instanceof Source) {
      @trigger_error('Returning a ' . get_debug_type($baseDefinition) . ' from `getSchemaDefinition` is deprecated in graphql:4.6 and is disallowed from graphql:5.0.0. Return \GraphQL\Language\Source instead. See https://www.drupal.org/node/', E_USER_DEPRECATED);
      $baseDefinition = new Source($baseDefinition);
    }
    $sources = [$baseDefinition->body];

    foreach ($extensions as $id => $extension) {
      $definition = $extension->getBaseDefinition();
      if (!$definition instanceof Source && $definition !== NULL) {
        @trigger_error('Returning a ' . get_debug_type($definition) . ' from `getBaseDefinition` is deprecated in graphql:4.6 and is disallowed from graphql:5.0.0. Return \GraphQL\Language\Source|NULL instead. See https://www.drupal.org/node/', E_USER_DEPRECATED);
        $definition = new Source($definition);
      }

      if (empty($definition)) {
        continue;
      }

      $sources[$id] = $definition->body;
    }
    // Event in order to alter the schema data.
    $event = new AlterSchemaDataEvent($sources);
    $this->dispatcher->dispatch(
      $event,
      AlterSchemaDataEvent::EVENT_NAME
    );
    $documents = [];
    foreach ($event->getSchemaData() as $schemaDatum) {
      if (empty($schemaDatum)) {
        continue;
      }
      $documents[] = Parser::parse($schemaDatum);
    }

    return AST::concatAST($documents);
  }

  /**
   * Retrieves the parsed AST of the schema extension definitions.
   *
   * Almost copy of the original method except it
   * provides alter schema extension event in order to manipulate data.
   *
   * @param array $extensions
   *   The Drupal GraphQl extensions data.
   *
   * @return \GraphQL\Language\AST\DocumentNode|null
   *   The parsed schema document.
   *
   * @throws \GraphQL\Error\SyntaxError
   *
   * @see \Drupal\graphql\Plugin\GraphQL\Schema\ComposableSchema::getSchemaDocument()
   */
  protected function getExtensionDocument(array $extensions = []) {
    foreach ($extensions as $id => $extension) {
      $definition = $extension->getExtensionDefinition();
      if (!$definition instanceof Source && $definition !== NULL) {
        @trigger_error('Returning a ' . get_debug_type($definition) . ' from `getExtensionDefinition` is deprecated in graphql:4.6 and is disallowed from graphql:5.0.0. Return \GraphQL\Language\Source|NULL instead. See https://www.drupal.org/node/', E_USER_DEPRECATED);
        $definition = new Source($definition);
      }

      if (empty($definition)) {
        continue;
      }

      $sources[$id] = $definition->body;
    }


    // Event in order to alter the schema extension data.
    $event = new AlterSchemaExtensionDataEvent($sources);
    $this->dispatcher->dispatch(
      $event,
      AlterSchemaExtensionDataEvent::EVENT_NAME
    );
    $documents = [];
    foreach ($event->getSchemaExtensionData() as $schemaDatum) {
      $documents[] = Parser::parse($schemaDatum);
    }

    return AST::concatAST($documents);
  }

}
