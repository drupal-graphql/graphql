<?php

namespace Drupal\Tests\graphql_core\Kernel;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\graphql_core\Traits\GraphQLFileTestTrait;
use Drupal\Tests\language\Kernel\LanguageTestBase;

/**
 * Test multilingual behavior of `graphql_core` features.
 *
 * @group graphql_core
 */
class LanguageTest extends LanguageTestBase {
  use GraphQLFileTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'graphql',
    'graphql_core',
    'graphql_context_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('configurable_language');
    $this->container->get('router.builder')->rebuild();

    ConfigurableLanguage::create(['id' => 'es'])->save();
    ConfigurableLanguage::create(['id' => 'fr'])->save();

    $config = $this->config('language.negotiation');
    $config->set('url.prefixes', ['en' => 'en', 'es' => 'es', 'fr' => 'fr'])
      ->save();

    $this->container->get('kernel')->rebuildContainer();
  }

  /**
   * Test default language.
   */
  public function testDefaultLanguage() {
    $result = $this->executeQueryFile('languages.gql');

    $english = [
      'id' => 'en',
      'name' => 'English',
      'isDefault' => TRUE,
      'isLocked' => FALSE,
      'direction' => 'ltr',
      'weight' => 0,
    ];

    $french = [
      'id' => 'fr',
      'name' => 'French',
      'isDefault' => FALSE,
      'isLocked' => FALSE,
      'direction' => 'ltr',
      'weight' => 0,
    ];

    $this->assertEquals($english, $result['data']['default']['languageInterfaceContext']);
    $this->assertEquals($english, $result['data']['en']['languageInterfaceContext']);
    $this->assertEquals($french, $result['data']['fr']['languageInterfaceContext']);
  }

}
