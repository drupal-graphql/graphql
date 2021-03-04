<?php

namespace Drupal\Tests\graphql_core\Kernel\Entity;

use Drupal\Tests\graphql_core\Kernel\GraphQLContentTestBase;

/**
 * Test entity query support in GraphQL.
 *
 * @group graphql_core
 */
class EntityByIdTest extends GraphQLContentTestBase {

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

    /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $languageStorage */
    $languageStorage = $this->container->get('entity_type.manager')->getStorage('configurable_language');

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

    // TODO: Check chache metadata.
    $metadata = $this->defaultCacheMetaData();
    $metadata->addCacheTags([
      'node:1',
    ]);

    // Check English node.
    $this->assertResults($this->getQueryFromFile('entity_by_id.gql'), [
      'id' => $node->id(),
      'language' => 'EN',
    ], [
      'nodeById' => [
        'entityLabel' => 'English node',
      ],
    ], $metadata);

    // Check French translation.
    $this->assertResults($this->getQueryFromFile('entity_by_id.gql'), [
      'id' => $node->id(),
      'language' => 'FR',
    ], [
      'nodeById' => [
        'entityLabel' => 'French node',
      ],
    ], $metadata);

    // Check Chinese simplified translation.
    $this->assertResults($this->getQueryFromFile('entity_by_id.gql'), [
      'id' => $node->id(),
      'language' => 'ZH_HANS',
    ], [
      'nodeById' => [
        'entityLabel' => 'Chinese simplified node',
      ],
    ], $metadata);
  }

}
