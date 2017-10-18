<?php

namespace Drupal\Tests\graphql_core\Kernel\Entity;

use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;
use Drupal\Tests\graphql_core\Traits\RevisionsTestTrait;
use Drupal\user\Entity\Role;

/**
 * Test entity query support in GraphQL.
 *
 * @group graphql_content
 */
class EntityByIdTest extends GraphQLFileTestBase {
  use NodeCreationTrait;
  use ContentTypeCreationTrait;
  use RevisionsTestTrait;

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
    'graphql_test',
    'graphql_core',
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

    $languageStorage = $this->container->get('entity.manager')->getStorage('configurable_language');
    $language = $languageStorage->create([
      'id' => $this->frenchLangcode,
    ]);
    $language->save();

    $language = $languageStorage->create([
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

    // Save a new draft.
    $this
      ->getNewDraft($node)
      ->setPublished(FALSE)
      ->setTitle('English node unpublished')
      ->save();

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
