<?php

namespace Drupal\Tests\graphql_block;

use Drupal\block_content\Entity\BlockContent;
use Drupal\simpletest\BlockCreationTrait;
use Drupal\Tests\graphql_core\GraphQLFileTest;

/**
 * Test block retrieval via GraphQL.
 */
class BlockTest extends GraphQLFileTest {
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
    'graphql_content',
    'graphql_block',
    'graphql_block_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    /** @var \Drupal\Core\Extension\ThemeInstallerInterface $theme_installer */
    $theme_installer = $this->container->get('theme_installer');
    $theme_installer->install(['stark']);

    $this->installEntitySchema('block_content');
    $this->installConfig('block_content');
    $this->installConfig('graphql_block_test');

    $custom_block = BlockContent::create([
      'type' => 'basic',
      'info' => 'Custom block test',
      'body' => [
        'value' => '<p>This is a test block content.</p>',
        'format' => 'basic_html',
      ],
    ]);

    $custom_block->save();

    $this->placeBlock('block_content:' . $custom_block->uuid(), [
      'region' => 'sidebar_first',
    ]);
  }

  /**
   * Test if two static blocks are in the content area.
   */
  public function testStaticBlocks() {
    $result = $this->executeQueryFile('blocks.gql');
    $this->assertEquals(1, count($result['data']['route']['content']), 'Block listing respects visibility settings.');
  }

  /**
   * Test placement of a content block.
   */
  public function testContentBlock() {
    $result = $this->executeQueryFile('blocks.gql');
    $this->assertEquals(1, count($result['data']['route']['sidebar']), 'One content block in sidebar region.');
    $this->assertEquals('<p>This is a test block content.</p>', $result['data']['route']['sidebar'][0]['body'], 'Content block body contains expected text.');
  }

}
