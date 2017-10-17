<?php

namespace Drupal\Tests\graphql_core\Kernel\Blocks;

use Drupal\block_content\Entity\BlockContent;
use Drupal\simpletest\BlockCreationTrait;
use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;

/**
 * Test block retrieval via GraphQL.
 *
 * @group graphql_block
 */
class BlockTest extends GraphQLFileTestBase {
  use BlockCreationTrait;
  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'block',
    'block_content',
    'text',
    'field',
    'filter',
    'editor',
    'ckeditor',
    'graphql_core',
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
    $result = $this->executeQueryFile('Blocks/blocks.gql');
    $this->assertEquals(1, count($result['data']['content']), 'Blocks can be retrieved on root level.');
    $this->assertEquals(1, count($result['data']['route']['content']), 'Block listing respects visibility settings.');
  }

  /**
   * Test placement of a content block.
   */
  public function testContentBlock() {
    $result = $this->executeQueryFile('Blocks/blocks.gql');
    $this->assertEquals(1, count($result['data']['route']['sidebar']), 'One content block in sidebar region.');
    $this->assertEquals('<p>This is a test block content.</p>', $result['data']['route']['sidebar'][0]['body']['value'], 'Content block body contains expected text.');
  }

}
