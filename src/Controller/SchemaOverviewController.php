<?php

namespace Drupal\graphql\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql\GraphQL\Schema\SchemaLoader;
use Drupal\graphql\Plugin\GraphQL\SchemaPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SchemaOverviewController implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The schema plugin manager service.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\SchemaPluginManager
   */
  protected $schemaManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a SchemaOverviewController object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler srevice.
   * @param \Drupal\graphql\Plugin\GraphQL\SchemaPluginManager $schemaManager
   *   The schema plugin manager service.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler, SchemaPluginManager $schemaManager) {
    $this->schemaManager = $schemaManager;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('plugin.manager.graphql.schema')
    );
  }

  /**
   * Renders a table of available schemas.
   *
   * @return array
   *   The renderable array for the overview table.
   */
  public function listSchemas() {
    $table = [
      '#type' => 'table',
      '#header' => [
        $this->t('Schema'),
        $this->t('Provider'),
        $this->t('Operations'),
      ],
      '#attributes' => [
        'id' => 'graphql-schemas',
      ],
    ];

    foreach ($this->schemaManager->getDefinitions() as $key => $definition) {
      $table["schema-$key"]['name'] = [
        '#plain_text' => $definition['name'],
      ];

      $table["schema-$key"]['provider'] = [
        '#plain_text' => $this->moduleHandler->getName($definition['provider']),
      ];

      $table["schema-$key"]['operations'] = [
        '#plain_text' => 'Operations',
      ];
    }

    return $table;
  }
}
