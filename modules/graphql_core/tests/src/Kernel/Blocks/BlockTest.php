<?php

namespace Drupal\Tests\graphql_core\Kernel\Blocks;

use Drupal\block_content\Entity\BlockContent;
use Drupal\simpletest\BlockCreationTrait;
use Drupal\Tests\graphql_core\Kernel\GraphQLCoreTestBase;

/**
 * Test block retrieval via GraphQL.
 *
 * @group graphql_core
 */
class BlockTest extends GraphQLCoreTestBase {
  use BlockCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'block',
    'block_content',
    'text',
    'field',
    'filter',
    'editor',
    'ckeditor',
    'graphql_block_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    /** @var \Drupal\Core\Extension\ThemeInstallerInterface $themeInstaller */
    $themeInstaller = $this->container->get('theme_installer');
    $themeInstaller->install(['stark']);

    $this->installEntitySchema('block_content');
    $this->installConfig('block_content');
    $this->installConfig('graphql_block_test');

    $this->prophesize(BlockContent::class);

    $customBlock = BlockContent::create([
      'type' => 'basic',
      'info' => 'Custom block test',
      'body' => [
        'value' => '<p>This is a test block content.</p>',
        'format' => 'basic_html',
      ],
    ]);

    $customBlock->save();

    $this->placeBlock('block_content:' . $customBlock->uuid(), [
      'region' => 'sidebar_first',
    ]);
  }

  /**
   * Test if two static blocks are in the content area.
   */
  public function testStaticBlocks() {
    $query = $this->getQueryFromFile('Blocks/blocks.gql');
    $metadata = $this->defaultCacheMetaData();

    // TODO: Check cache metadata.
    $metadata->addCacheTags([
      'config:block_list',
      'block_content:1',
      'config:block.block.stark_powered',
      'config:field.storage.block_content.body',
      'entity_bundles',
      'entity_field_info',
      'entity_types',
    ]);

    $this->assertResults($query, [], [
      'route' => [
        'content' => [
          0 => [
            '__typename' => 'UnexposedEntity',
          ],
        ],
        'sidebar' => [
          0 => [
            '__typename' => 'BlockContentBasic',
            'body' => [
              'value' => '<p>This is a test block content.</p>',
            ],
          ],
        ],
      ],
    ], $metadata);
  }
}
