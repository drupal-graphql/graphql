<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test contextual language negotiation.
 */
class LanguageNegotiationTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['language'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['language']);
    $this->container->get('language_negotiator')
      ->setCurrentUser($this->accountProphecy->reveal());

    $this->installEntitySchema('configurable_language');
    ConfigurableLanguage::create([
      'id' => 'fr',
      'weight' => 1,
    ])->save();


    $this->mockType('node', ['name' => 'Node']);

    $this->mockField('edge', [
      'name' => 'edge',
      'parents' => ['Root', 'Node'],
      'type' => 'Node',
      'arguments' => [
        'language' => 'String',
      ],
      'contextual_arguments' => ['language'],
    ], 'foo');

    $this->mockField('language', [
      'name' => 'language',
      'parents' => ['Root', 'Node'],
      'type' => 'String',
      'response_cache_contexts' => ['languages:language_interface'],
    ], function () {
      yield \Drupal::languageManager()->getCurrentLanguage()->getId();
    });


    $this->container->get('router.builder')->rebuild();
  }

  /**
   * Test root language.
   */
  public function testRootLanguage() {
    $query = <<<GQL
query {
  language
}
GQL;
    $this->assertResults($query, [], [
      'language' => \Drupal::languageManager()->getDefaultLanguage()->getId(),
    ], $this->defaultCacheMetaData());

  }

  /**
   * Test inherited language.
   */
  public function testInheritedLanguage() {
    $query = <<<GQL
query {
  edge(language: "fr") {
    language
  }
}
GQL;

    $this->assertResults($query, [], [
      'edge' => [
        'language' => 'fr',
      ],
    ], $this->defaultCacheMetaData());
  }

  /**
   * Test overridden language.
   */
  public function testOverriddenLanguage() {
    $query = <<<GQL
query {
  edge(language: "fr") {
    language
    edge(language: "en") {
      language
    }
  }
}
GQL;

    $this->assertResults($query, [], [
      'edge' => [
        'language' => 'fr',
        'edge' => [
          'language' => 'en',
        ],
      ],
    ], $this->defaultCacheMetaData());
  }

}
