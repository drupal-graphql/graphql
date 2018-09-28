<?php

namespace Drupal\Tests\graphql_core\Kernel\Languages;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\graphql_core\Kernel\GraphQLCoreTestBase;

/**
 * Test multilingual behavior.
 *
 * @group graphql_core
 */
class LanguageTest extends GraphQLCoreTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'language',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['language']);
    $this->installEntitySchema('configurable_language');
    $this->container->get('router.builder')->rebuild();

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
  public function testLanguageId() {
    $metadata = $this->defaultCacheMetaData();
    // TODO: Should this also contain the language config cache metadata?

    $this->assertResults($this->getQueryFromFile('languages.gql'), [], [
      'languages' => [
        0 => [
          'id' => 'en',
          'name' => 'English',
          'isDefault' => TRUE,
          'isLocked' => FALSE,
          'direction' => 'ltr',
          'weight' => 0,
          'argument' => 'en',
        ],
        1 => [
          'id' => 'fr',
          'name' => 'French',
          'isDefault' => FALSE,
          'isLocked' => FALSE,
          'direction' => 'ltr',
          'weight' => 1,
          'argument' => 'fr',
        ],
        2 => [
          'id' => 'es',
          'name' => 'Spanish',
          'isDefault' => FALSE,
          'isLocked' => FALSE,
          'direction' => 'ltr',
          'weight' => 2,
          'argument' => 'es',
        ],
        3 => [
          'id' => 'pt-br',
          'name' => 'Portuguese, Brazil',
          'isDefault' => FALSE,
          'isLocked' => FALSE,
          'direction' => 'ltr',
          'weight' => 3,
          'argument' => 'pt_br',
        ],
      ],
    ], $metadata);
  }

  /**
   * Test language switch links.
   */
  public function testLanguageSwitchLinks() {
    $metadata = $this->defaultCacheMetaData();
    $metadata->addCacheTags([
      'config:language.entity.en',
      'config:language.entity.es',
      'config:language.entity.fr',
      'config:language.entity.pt-br'
    ]);

    $this->assertResults($this->getQueryFromFile('language_switch_links.gql'), [], [
      'route' => [
        'links' => [
          0 => [
            'language' => [
              'id' => 'en',
            ],
            'url' => [
              'path' => '/en',
            ],
            'title' => 'English',
            'active' => TRUE,
          ],
          1 => [
            'language' => [
              'id' => 'fr',
            ],
            'url' => [
              'path' => '/fr',
            ],
            'title' => NULL,
            'active' => FALSE,
          ],
          2 => [
            'language' => [
              'id' => 'es',
            ],
            'url' => [
              'path' => '/es',
            ],
            'title' => NULL,
            'active' => FALSE,
          ],
          3 => [
            'language' => [
              'id' => 'pt-br',
            ],
            'url' => [
              'path' => '/',
            ],
            'title' => NULL,
            'active' => FALSE,
          ],
        ],
      ],
    ], $metadata);
  }
}
