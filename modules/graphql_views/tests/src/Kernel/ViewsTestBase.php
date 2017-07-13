<?php

namespace Drupal\Tests\graphql_views\Kernel;

use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;

/**
 * Base class for test views support in GraphQL.
 *
 * @group graphql_views
 */
abstract class ViewsTestBase extends GraphQLFileTestBase {
  use NodeCreationTrait;
  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'field',
    'filter',
    'text',
    'views',
    'graphql_content',
    'graphql_views',
    'graphql_views_test',
  ];

  /**
   * A List of letters.
   *
   * @var string[]
   */
  protected $letters = ['A', 'B', 'C', 'A', 'B', 'C', 'A', 'B', 'C'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('view');
    $this->installConfig(['node', 'filter', 'views', 'graphql_views_test']);
    $this->installSchema('node', 'node_access');
    $this->createContentType(['type' => 'test']);

    $this->container->get('config.factory')->getEditable('graphql_content.schema')
      ->set('types', [
        'node' => [
          'exposed' => TRUE,
          'bundles' => [
            'test' => [
              'exposed' => TRUE,
            ],
            'test2' => [
              'exposed' => TRUE,
            ],
          ],
        ],
      ])
      ->save();

    Role::load('anonymous')
      ->grantPermission('access content')
      ->save();

    foreach ($this->letters as $index => $letter) {
      $this->createNode([
        'title' => 'Node ' . $letter,
        'type' => 'test',
      ])->save();
    }
  }

}
