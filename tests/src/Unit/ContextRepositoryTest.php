<?php

namespace Drupal\Tests\graphql\Unit;

use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\graphql\GraphQL\Context\ContextRepository;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\Tests\UnitTestCase;

class ContextRepositoryTest extends UnitTestCase {

  /**
   * @var ContextRepository
   */
  protected $contextRepository;

  protected $resolveContextA;
  protected $resolveContextB;

  protected function setUp() {
    parent::setUp();
    $this->resolveContextA = $this->prophesize(ResolveContext::class)->reveal();
    $this->resolveContextB = $this->prophesize(ResolveContext::class)->reveal();
    $this->contextRepository = new ContextRepository();
    $contextProviderA = $this->prophesize(ContextProviderInterface::class);
    $contextProviderA->getAvailableContexts()->willReturn([
      'a' => new Context(new ContextDefinition()),
      'b' => new Context(new ContextDefinition()),
    ]);
    $contextProviderA->getRuntimeContexts(['@a.a', '@a.b'])->willReturn([
      '@a.a' => '1',
      '@a.b' => '2',
    ]);
    $this->contextRepository->addContextProvider($contextProviderA->reveal());

    $contextProviderB = $this->prophesize(ContextProviderInterface::class);
    $contextProviderB->getAvailableContexts()->willReturn([
      'c' => new Context(new ContextDefinition()),
      'd' => new Context(new ContextDefinition()),
    ]);
    $contextProviderA->getRuntimeContexts(['@b.c', '@b.d'])->willReturn([
      '@b.c' => '3',
      '@b.d' => '4',
    ]);
    $this->contextRepository->addContextProvider($contextProviderA->reveal());
  }

  function testNoOverridesNoPath() {
    $result = $this->contextRepository->executeInContext($this->resolveContextA, [], function () {
      return $this->contextRepository->getRuntimeContexts(['@a.a', '@a.b']);
    });

    $this->assertEquals([
      '@a.a' => '1',
      '@a.b' => '2',
    ], $result);
  }

  function testNoOverridesPath() {
    $result = $this->contextRepository->executeInContext($this->resolveContextA, ['x'], function () {
      return $this->contextRepository->getRuntimeContexts(['@a.a', '@a.b']);
    });

    $this->assertEquals([
      '@a.a' => '1',
      '@a.b' => '2',
    ], $result);
  }

  function testMatchingPathOverride() {
    $this->contextRepository->overrideContext($this->resolveContextA, ['x'], '@a.a', 'y');
    $result = $this->contextRepository->executeInContext($this->resolveContextA, ['x'], function () {
      return $this->contextRepository->getRuntimeContexts(['@a.a', '@a.b']);
    });

    $this->assertEquals([
      '@a.a' => 'y',
      '@a.b' => '2',
    ], $result);
  }

  function testNonMatchingPathOverride() {
    $this->contextRepository->overrideContext($this->resolveContextA, ['x'], '@a.a', 'y');
    $result = $this->contextRepository->executeInContext($this->resolveContextA, ['z'], function () {
      return $this->contextRepository->getRuntimeContexts(['@a.a', '@a.b']);
    });

    $this->assertEquals([
      '@a.a' => '1',
      '@a.b' => '2',
    ], $result);
  }

  function testInheritPathOverride() {
    $this->contextRepository->overrideContext($this->resolveContextA, ['x'], '@a.a', 'z');
    $result = $this->contextRepository->executeInContext($this->resolveContextA,['x', 'y'], function () {
      return $this->contextRepository->getRuntimeContexts(['@a.a', '@a.b']);
    });

    $this->assertEquals([
      '@a.a' => 'z',
      '@a.b' => '2',
    ], $result);
  }

  function testNestedPathOverride() {
    $this->contextRepository->overrideContext($this->resolveContextA, ['x', 'y'], '@a.a', 'foo');
    $this->contextRepository->overrideContext($this->resolveContextA, ['x'], '@a.b', 'bar');

    $this->contextRepository->executeInContext($this->resolveContextA, ['x'], function () {
      return $this->contextRepository->getRuntimeContexts(['@a.a', '@a.b']);
    });

    $this->contextRepository->executeInContext($this->resolveContextA, ['x', 'y'], function () {
      return $this->contextRepository->getRuntimeContexts(['@a.a', '@a.b']);
    });

    $result = $this->contextRepository->executeInContext($this->resolveContextA, ['x', 'y', 'z'], function () {
      return $this->contextRepository->getRuntimeContexts(['@a.a', '@a.b']);
    });

    $this->assertEquals([
      '@a.a' => 'foo',
      '@a.b' => 'bar',
    ], $result);
  }

  function testMultipleContexts() {
    $this->contextRepository->overrideContext($this->resolveContextA, ['x', 'y'], '@a.a', 'foo');
    $this->contextRepository->overrideContext($this->resolveContextA, ['x'], '@a.b', 'bar');

    $this->contextRepository->executeInContext($this->resolveContextA, ['x'], function () {
      return $this->contextRepository->getRuntimeContexts(['@a.a', '@a.b']);
    });

    $this->contextRepository->executeInContext($this->resolveContextA, ['x', 'y'], function () {
      return $this->contextRepository->getRuntimeContexts(['@a.a', '@a.b']);
    });

    $result = $this->contextRepository->executeInContext($this->resolveContextA, ['x', 'y', 'z'], function () {
      return $this->contextRepository->getRuntimeContexts(['@a.a', '@a.b']);
    });

    $this->assertEquals([
      '@a.a' => 'foo',
      '@a.b' => 'bar',
    ], $result);

    $this->contextRepository->executeInContext($this->resolveContextB, ['x'], function () {
      return $this->contextRepository->getRuntimeContexts(['@a.a', '@a.b']);
    });

    $this->contextRepository->executeInContext($this->resolveContextB, ['x', 'y'], function () {
      return $this->contextRepository->getRuntimeContexts(['@a.a', '@a.b']);
    });

    $result = $this->contextRepository->executeInContext($this->resolveContextB, ['x', 'y', 'z'], function () {
      return $this->contextRepository->getRuntimeContexts(['@a.a', '@a.b']);
    });

    $this->assertEquals([
      '@a.a' => '1',
      '@a.b' => '2',
    ], $result);
  }
}
