<?php

namespace Drupal\Tests\graphql_core\Kernel\Views;

use Drupal\field\Tests\EntityReference\EntityReferenceTestTrait;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\user\Entity\Role;

/**
 * Base class for test views support in GraphQL.
 *
 * @group graphql_views
 */
abstract class ViewsTestBase extends ViewsTestBaseDeprecationFix {
  use NodeCreationTrait;
  use ContentTypeCreationTrait;
  use EntityReferenceTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'field',
    'filter',
    'text',
    'views',
    'taxonomy',
    'graphql_core',
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
    $this->installEntitySchema('taxonomy_term');
    $this->installConfig(['node', 'filter', 'views', 'graphql_views_test']);
    $this->installSchema('node', 'node_access');
    $this->createContentType(['type' => 'test']);
    $this->createEntityReferenceField('node', 'test', 'field_tags', 'Tags', 'taxonomy_term');

    Vocabulary::create([
      'name' => 'Tags',
      'vid' => 'tags',
    ])->save();

    Role::load('anonymous')
      ->grantPermission('access content')
      ->save();

    $terms = [];

    $terms['A'] = Term::create([
      'name' => 'Term A',
      'vid' => 'tags',
    ]);
    $terms['A']->save();

    $terms['B'] = Term::create([
      'name' => 'Term B',
      'vid' => 'tags',
    ]);
    $terms['B']->save();

    $terms['C'] = Term::create([
      'name' => 'Term C',
      'vid' => 'tags',
    ]);
    $terms['C']->save();

    foreach ($this->letters as $index => $letter) {
      $this->createNode([
        'title' => 'Node ' . $letter,
        'type' => 'test',
        'field_tags' => $terms[$letter],
      ])->save();
    }
  }

}
