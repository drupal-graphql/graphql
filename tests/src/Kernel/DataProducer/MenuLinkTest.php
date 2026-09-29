<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Menu link data producer tests.
 *
 * @group graphql
 */
#[Group('graphql')]
#[RunTestsInSeparateProcesses]
#[AllowMockObjectsWithoutExpectations]
class MenuLinkTest extends MenuTestBase {

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink\MenuLinkAttribute::resolve
   */
  public function testMenuLinkAttribute(): void {
    $attribute = 'target';
    $assert_happened = FALSE;
    foreach ($this->linkTree as $link_tree) {
      $options = $link_tree->link->getOptions();
      if (!empty($options['attributes'][$attribute])) {
        $result = $this->executeDataProducer('menu_link_attribute', [
          'link' => $link_tree->link,
          'attribute' => 'target',
        ]);

        $this->assertEquals($options['attributes'][$attribute], $result);
        $assert_happened = TRUE;
      }
    }
    $this->assertTrue($assert_happened, 'At least one menu attribute was tested');
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink\MenuLinkDescription::resolve
   */
  public function testMenuLinkDescription(): void {
    foreach ($this->linkTree as $link_tree) {
      $result = $this->executeDataProducer('menu_link_description', [
        'link' => $link_tree->link,
      ]);

      $this->assertEquals($link_tree->link->getDescription(), $result);

      foreach ($link_tree->subtree as $link) {
        $result = $this->executeDataProducer('menu_link_description', [
          'link' => $link->link,
        ]);
        $this->assertEquals($link->link->getDescription(), $result);
      }
    }
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink\MenuLinkExpanded::resolve
   */
  public function testMenuLinkExpanded(): void {
    foreach ($this->linkTree as $link_tree) {
      $result = $this->executeDataProducer('menu_link_expanded', [
        'link' => $link_tree->link,
      ]);

      $this->assertEquals($link_tree->link->isExpanded(), $result);
    }
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink\MenuLinkLabel::resolve
   */
  public function testMenuLinkLabel(): void {
    foreach ($this->linkTree as $link_tree) {
      $result = $this->executeDataProducer('menu_link_label', [
        'link' => $link_tree->link,
      ]);

      $this->assertEquals($link_tree->link->getTitle(), $result);
    }
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink\MenuLinkUrl::resolve
   */
  public function testMenuLinkUrl(): void {
    foreach ($this->linkTree as $link_tree) {
      $result = $this->executeDataProducer('menu_link_url', [
        'link' => $link_tree->link,
      ]);

      $this->assertEquals($link_tree->link->getUrlObject(), $result);
    }
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink\MenuLinkUrl::resolve
   */
  public function testMenuLinkUrlTranslated(): void {
    foreach ($this->linkTree as $link_tree) {
      $result = $this->executeDataProducer('menu_link_url', [
        'link' => $link_tree->link,
      ], 'en');

      $expected_url = $link_tree
        ->link
        ->getUrlObject()
        ->setOption('language', $this->container->get('language_manager')->getLanguage('en'));
      $this->assertEquals($expected_url, $result);
    }
  }

}
