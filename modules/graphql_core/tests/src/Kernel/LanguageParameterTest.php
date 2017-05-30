<?php

namespace Drupal\Tests\graphql_core\Kernel;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\graphql\Functional\QueryTestBase;
use Drupal\Tests\graphql_core\Traits\GraphQLFileTestTrait;

/**
 * Test multilingual behavior of `graphql_core` features.
 *
 * @group graphql_core
 */
class LanguageParameterTest extends QueryTestBase {
  use GraphQLFileTestTrait;
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'language',
    'graphql',
    'graphql_core',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $user = $this->drupalCreateUser([
      'administer languages',
      'access administration pages',
      'view the administration theme',
      'execute graphql requests',
    ]);

    $this->drupalLogin($user);

    $this->drupalPostForm('admin/config/regional/language/add', ['predefined_langcode' => 'fr'], $this->t('Add language'));
    $this->drupalPostForm('admin/config/regional/language/detection', ['language_interface[enabled][language-graphql]' => 1], $this->t('Save settings'));
  }

  /**
   * Test if the language parameter is picked up correctly.
   */
  public function testLanguageParameter() {
    $default = json_decode($this->query($this->getQuery('language_parameter.gql')), TRUE);
    $parameterized = json_decode($this->query($this->getQuery('language_parameter.gql'), NULL, NULL, ['graphqlLanguage' => 'fr']), TRUE);

    $this->assertEquals([
      'id' => 'en',
      'name' => 'English',
      'isDefault' => TRUE,
      'isLocked' => FALSE,
      'direction' => 'ltr',
      'weight' => 0,
    ], $default['data']['default']['languageInterfaceContext'], 'Default language is correct.');

    $this->assertEquals([
      'id' => 'fr',
      'name' => 'French',
      'isDefault' => FALSE,
      'isLocked' => FALSE,
      'direction' => 'ltr',
      'weight' => 1,
    ], $parameterized['data']['default']['languageInterfaceContext'], 'Requested language is correct.');
  }

}
