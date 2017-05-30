<?php

namespace Drupal\Tests\graphql_content\Kernel;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\NodeInterface;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;

/**
 * Test entity query support in GraphQL.
 *
 * @group graphql_content
 */
class EntityByIdTest extends GraphQLFileTestBase {
  use NodeCreationTrait;
  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'field',
    'filter',
    'language',
    'content_translation',
    'text',
    'graphql_content',
  ];

  /**
   * The added French language.
   *
   * @var string
   */
  protected $frenchLangcode = 'fr';

  /**
   * The added Chinese simplified language.
   *
   * @var string
   */
  protected $chineseSimplifiedLangcode = 'zh-hans';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installConfig(['node', 'filter']);
    $this->installSchema('node', 'node_access');
    $this->createContentType(['type' => 'test']);

    Role::load('anonymous')
      ->grantPermission('access content')
      ->save();

    $language_storage = $this->container->get('entity.manager')->getStorage('configurable_language');
    $language = $language_storage->create([
      'id' => $this->frenchLangcode,
    ]);
    $language->save();

    $language = $language_storage->create([
      'id' => $this->chineseSimplifiedLangcode,
    ]);
    $language->save();
  }

  /**
   * Test that the entity query returns all nodes if no args are given.
   */
  public function testEntityByIdWithTranslation() {
    $node = $this->createNode([
      'title' => 'English node',
      'type' => 'test',
    ]);
    $node->save();
    $node->addTranslation($this->frenchLangcode, ['title' => 'French node'])->save();
    $node->addTranslation($this->chineseSimplifiedLangcode, ['title' => 'Chinese simplified node'])->save();

    // Check English node.
    $result = $this->executeQueryFile('entity_by_id.gql', [
      'id' => $node->id(),
      'language' => 'en',
    ]);
    $this->assertEquals(['entityLabel' => 'English node'], $result['data']['nodeById']);

    // Check French translation.
    $result = $this->executeQueryFile('entity_by_id.gql', [
      'id' => $node->id(),
      'language' => 'fr',
    ]);
    $this->assertEquals(['entityLabel' => 'French node'], $result['data']['nodeById']);

    // Check Chinese simplified translation.
    $result = $this->executeQueryFile('entity_by_id.gql', [
      'id' => $node->id(),
      'language' => 'zh_hans',
    ]);
    $this->assertEquals(['entityLabel' => 'Chinese simplified node'], $result['data']['nodeById']);
  }

}
