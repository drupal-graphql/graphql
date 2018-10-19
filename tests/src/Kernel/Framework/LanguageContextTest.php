<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Core\Language\LanguageInterface;
use Drupal\graphql\Language\FixedLanguageNegotiator;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Test contextual language negotiation.
 */
class LanguageContextTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['language'];

  /**
   * @var \Drupal\graphql\Language\LanguageContext
   */
  protected $languageContext;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $gql_schema = <<<GQL
    schema {
      query: Query
    }

    type Query {
      edge(language: String): Node
      language: String
      unaware: String
      leaking: String
    }

    type Node {
      edge(language: String): Node
      language: String
      unaware: String
      leaking: String
    }
GQL;

    $this->setUpSchema($gql_schema, $this->getDefaultSchema());

    $this->languageContext = $this->container->get('graphql.language_context');

    foreach (['Query', 'Node'] as $parent) {
      $this->mockField('edge', [
        'parent' => $parent,
      ], function ($value, $args, ResolveContext $context, ResolveInfo $info) {
        $context->setContext('language', $args['language'], $info);
        return 'foo';
      });

      $this->mockField('language', [
        'parent' => $parent,
      ], function ($value, $args, ResolveContext $context, ResolveInfo $info) {
        return $this->languageContext->executeInLanguageContext(function () {
          return \Drupal::languageManager()->getCurrentLanguage()->getId();
        }, $context->getContext('language', $info));
      });

      $this->mockField('unaware', [
        'parent' => $parent,
      ], function ($value, $args, ResolveContext $context, ResolveInfo $info) {
        return \Drupal::languageManager()->getCurrentLanguage()->getId();
      });

      $this->mockField('leaking', [
        'parent' => $parent,
      ], function ($value, $args, ResolveContext $context, ResolveInfo $info) {
        $context->addCacheContexts(['languages:language_interface']);
        return 'leak';
      });
    }

    $this->container->get('router.builder')->rebuild();
  }

  /**
   * Test if the language negotiator is injected properly.
   */
  public function testNegotiatorInjection() {
    $context = $this->container->get('graphql.language_context');
    $negotiator = $this->container->get('language_negotiator');

    $this->assertInstanceOf(FixedLanguageNegotiator::class, $negotiator);

    // Check if the order of negotiators is correct.
    $getEnabledNegotiators = new \ReflectionMethod(FixedLanguageNegotiator::class, 'getEnabledNegotiators');
    $getEnabledNegotiators->setAccessible(TRUE);
    $negotiators = $getEnabledNegotiators->invokeArgs($negotiator, [LanguageInterface::TYPE_INTERFACE]);
    $this->assertEquals([
      'language-graphql' => -999,
      'language-url' => 0,
    ], $negotiators);

    // Check if the GraphQL language negotiation yields the correct result.
    $language = $context->executeInLanguageContext(function () use ($negotiator) {
      $negotiateLanguage = new \ReflectionMethod(FixedLanguageNegotiator::class, 'negotiateLanguage');
      $negotiateLanguage->setAccessible(TRUE);
      return $negotiateLanguage->invokeArgs($negotiator, [LanguageInterface::TYPE_INTERFACE, 'language-graphql']);
    }, 'fr');

    $this->assertNotNull($language);
    $this->assertEquals('fr', $language->getId());

    // Check if the language type is initialized correctly.
    $result = $context->executeInLanguageContext(function () {
      return \Drupal::service('language_negotiator')->initializeType(LanguageInterface::TYPE_INTERFACE);
    }, 'fr');

    $this->assertEquals('language-graphql', array_keys($result)[0]);
  }

  /**
   * Test the language context service.
   */
  public function testLanguageContext() {
    $context = $this->container->get('graphql.language_context');

    $this->assertEquals('fr', $context->executeInLanguageContext(function () {
      return \Drupal::service('graphql.language_context')->getCurrentLanguage();
    }, 'fr'), 'Unexpected language context result.');
  }

  /**
   * Test the language negotiation within a context.
   */
  public function testLanguageNegotiation() {
    $context = $this->container->get('graphql.language_context');

    $this->assertEquals('fr', $context->executeInLanguageContext(function () {
      return \Drupal::service('language_manager')->getCurrentLanguage()->getId();
    }, 'fr'), 'Unexpected language negotiation result.');
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

  /**
   * Test an language unaware field.
   *
   * If a field doesn't declare language cache contexts, the context is
   * not inactive and the standard language negotiation should kick in.
   */
  public function testUnawareField() {
    $query = <<<GQL
query {
  edge(language: "fr") {
    unaware
  }
}
GQL;

    $this->assertResults($query, [], [
      'edge' => [
        'unaware' => 'en',
      ],
    ], $this->defaultCacheMetaData());
  }

}
