<?php

declare(strict_types=1);

namespace Drupal\graphql\Drush\Generators;

use Drupal\graphql\Plugin\SchemaPluginManager;
use DrupalCodeGenerator\Asset\AssetCollection;
use DrupalCodeGenerator\Attribute\Generator;
use DrupalCodeGenerator\Command\BaseGenerator;
use DrupalCodeGenerator\GeneratorType;
use Drush\Commands\AutowireTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Generator for GraphQL schema extension plugins.
 */
#[Generator(
  name: 'plugin:graphql-schema-extension',
  description: 'Generates a GraphQL schema extension plugin.',
  aliases: ['graphql-schema-extension'],
  templatePath: __DIR__,
  type: GeneratorType::MODULE_COMPONENT,
)]
final class SchemaExtensionGenerator extends BaseGenerator {

  use AutowireTrait;

  public function __construct(
    #[Autowire(service: 'plugin.manager.graphql.schema')]
    protected SchemaPluginManager $schemaPluginManager,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function generate(array &$vars, AssetCollection $assets): void {
    $ir = $this->createInterviewer($vars);

    $vars['machine_name'] = $ir->askMachineName();

    $vars['plugin_label'] = $ir->askPluginLabel();
    $vars['plugin_id'] = $ir->askPluginId();
    $vars['plugin_description'] = $ir->ask('Description');
    $vars['class'] = $ir->askPluginClass(suffix: 'Extension');
    $vars['services'] = $ir->askServices(FALSE);

    $schemas = array_map(fn ($definition) => $definition['name'] ?? $definition['id'], $this->schemaPluginManager->getDefinitions());
    $vars['schema'] = $ir->choice('Schema', $schemas);

    $assets->addFile('src/Plugin/GraphQL/SchemaExtension/{class}.php', 'schema-extension.twig');
  }

}
