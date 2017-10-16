<?php

namespace Drupal\Tests\graphql_core\Kernel\Languages;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\graphql\Traits\ByPassAccessTrait;
use Drupal\Tests\graphql\Traits\GraphQLFileTestTrait;
use Drupal\Tests\language\Kernel\LanguageTestBase;

/**
 * Test multilingual behavior of `graphql_core` features.
 *
 * @group graphql_core
 */
class LanguageTest extends LanguageTestBase {
  use GraphQLFileTestTrait;
  use ByPassAccessTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'graphql',
    'graphql_core',
    'graphql_test',
    'graphql_context_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('configurable_language');
    $this->container->get('router.builder')->rebuild();

    ConfigurableLanguage::create([
      'id' => 'fr',
      'weight' => 1,
    ])->save();

    ConfigurableLanguage::create([
      'id' => 'es',
      'weight' => 2,
    ])->save();

    ConfigurableLanguage::create([
      'id' => 'pt-br',
      'weight' => 3,
    ])->save();

    $config = $this->config('language.negotiation');
    $config->set('url.prefixes', ['en' => 'en', 'es' => 'es', 'fr' => 'fr'])
      ->save();

    $this->container->get('kernel')->rebuildContainer();
  }

  /**
   * Test listing of available languages.
   */
  public function testAvailableLanguages() {
    $result = $this->executeQueryFile('languages.gql');

    $english = [
      'id' => 'en',
      'name' => 'English',
      'isDefault' => TRUE,
      'isLocked' => FALSE,
      'direction' => 'ltr',
      'weight' => 0,
      'argument' => 'en',
    ];

    $french = [
      'id' => 'fr',
      'name' => 'French',
      'isDefault' => FALSE,
      'isLocked' => FALSE,
      'direction' => 'ltr',
      'weight' => 1,
      'argument' => 'fr',
    ];

    $spanish = [
      'id' => 'es',
      'name' => 'Spanish',
      'isDefault' => FALSE,
      'isLocked' => FALSE,
      'direction' => 'ltr',
      'weight' => 2,
      'argument' => 'es',
    ];

    $brazil = [
      'id' => 'pt-br',
      'name' => 'Portuguese, Brazil',
      'isDefault' => FALSE,
      'isLocked' => FALSE,
      'direction' => 'ltr',
      'weight' => 3,
      'argument' => 'pt_br',
    ];

    $this->assertEquals([$english, $french, $spanish, $brazil], $result['data']['languages']);
  }

}
