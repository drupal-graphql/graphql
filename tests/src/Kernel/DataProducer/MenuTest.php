<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\system\Entity\Menu;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Data producers Menu test class.
 *
 * @group graphql
 */
#[Group('graphql')]
#[RunTestsInSeparateProcesses]
#[AllowMockObjectsWithoutExpectations]
class MenuTest extends MenuTestBase {

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLinks::resolve
   */
  public function testMenuLinks(): void {
    $result = $this->executeDataProducer('menu_links', [
      'menu' => $this->menu,
    ]);

    $count = 0;
    foreach ($result as $link_tree) {
      $this->assertInstanceOf(MenuLinkTreeElement::class, $link_tree);
      $count += $link_tree->count();
    }

    $this->assertEquals(5, $count);
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuTree\MenuTreeLink::resolve
   */
  public function testMenuTreeLink(): void {
    foreach ($this->linkTree as $link_tree) {
      $result = $this->executeDataProducer('menu_tree_link', [
        'element' => $link_tree,
      ]);

      $this->assertEquals($link_tree->link, $result);
    }
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuTree\MenuTreeSubtree::resolve
   */
  public function testMenuTreeSubtree(): void {
    foreach ($this->linkTree as $link_tree) {
      $result = $this->executeDataProducer('menu_tree_subtree', [
        'element' => $link_tree,
      ]);

      $this->assertEquals($link_tree->subtree, $result);
    }
  }

  /**
   * Test that there are no links returned that are not accessible.
   */
  public function testAccessDeniedLinksRemoved(): void {
    $menu = Menu::create([
      'id' => 'access_test',
      'label' => 'Access test menu',
      'description' => 'Description text',
    ]);

    $menu->save();

    $link_options = [
      'title' => 'Menu link test',
      'provider' => 'graphql',
      'menu_name' => 'access_test',
      'link' => [
        // Only accessible by admins, so must be removed for the anonymous user.
        'uri' => 'internal:/admin/config/graphql',
      ],
      'description' => 'Test description',
    ];
    $link = MenuLinkContent::create($link_options);
    $link->save();

    $result = $this->executeDataProducer('menu_links', [
      'menu' => $menu,
    ]);
    $this->assertEmpty($result);
  }

}
