<?php

namespace Drupal\graphql_twig;

use Youshido\GraphQL\Parser\Ast\Fragment;
use Youshido\GraphQL\Parser\Ast\FragmentReference;
use Youshido\GraphQL\Parser\Parser;

/**
 * Assemble GraphQL queries from a list of fragments.
 */
class QueryAssembler {

  /**
   * The GraphQL parser.
   *
   * @var \Youshido\GraphQL\Parser\Parser
   */
  protected $parser;

  /**
   * The list of available fragments.
   *
   * @var string[]
   */
  protected $fragments;

  /**
   * Fragments with attached suggestions.
   *
   * @var string[]
   */
  protected $processedFragments;

  /**
   * A map of fragment dependencies.
   *
   * @var string[][]
   */
  protected $dependencies;

  /**
   * Add all required fragments to a query.
   *
   * @param string $query
   *   The source query.
   *
   * @return string
   *   The assembled query.
   */
  public function assemble($query) {
    $dependencies = $this->collectFragments($query);
    $fragments = array_map(function ($name) {
      return $this->getFragment($name);
    }, $dependencies);

    return implode("\n", array_merge([$query], array_filter($fragments)));
  }

  /**
   * Recursively collect fragment dependencies.
   *
   * @param string $source
   *   The GraphQL source string.
   *
   * @return string[]
   *   The list of fragment dependencies.
   */
  protected function collectFragments($source) {
    $references = array_map(function (FragmentReference $fragment) {
      return $fragment->getName();
    }, $this->parse($source)['fragmentReferences']);

    $dependencies = $references;

    foreach ($references as $name) {
      if (!array_key_exists($name, $this->dependencies)) {
        $this->dependencies[$name] = [];
        if ($fragmentSource = $this->getFragment($name)) {
          $this->dependencies[$name] = $this->collectFragments($fragmentSource);
        }
      }
      $dependencies = array_merge($dependencies, $this->dependencies[$name]);
    }
    return array_unique($dependencies);
  }

  /**
   * Retrieve a fragment.
   *
   * @param string $name
   *   The fragments name.
   *
   * @return string
   *   The fragment or NULL if it doesn't exist.
   */
  protected function getFragment($name) {
    if (!array_key_exists($name, $this->fragments)) {
      $path = [];
      $suggestions = explode('__', $name);

      $current = implode('__', $suggestions);
      while (!array_key_exists($current, $this->fragments) && $suggestions) {
        array_pop($suggestions);
        $next = implode('__', $suggestions);
        $path[$current] = $next;
        $current = $next;
      }

      $this->fragments[$name] = NULL;

      if ($source = $this->getFragment($current)) {

        $fragments = array_filter($this->parse($source)['fragments'], function (Fragment $fragmentInfo) use ($current) {
          return $fragmentInfo->getName() === $current;
        });

        $models = array_map(function (Fragment $fragmentInfo) {
          return $fragmentInfo->getModel();
        }, $fragments);

        if ($model = array_pop($models)) {
          foreach ($path as $src => $dst) {
            $this->fragments[$src] = "fragment $src on $model { ... $dst }";
          }
        }
      }
    }

    if (!array_key_exists($name, $this->processedFragments)) {
      /** @var Fragment $parsed */
      $parsed = array_filter((new Parser())->parse($this->fragments[$name])['fragments'], function (Fragment $fragment) use ($name) {
        return $fragment->getName() == $name;
      })[0];
      $model = $parsed->getModel();

      $regex = "/fragment\s+$name\s+on\s+$model\s+{/";

      $subs = array_filter(array_keys($this->fragments), function ($key) use ($name) {
        $prefix = $name . '__';
        $length = strlen($prefix);
        return (substr($key, 0, $length) == $prefix) && !strpos(substr($key, $length), '__');
      });

      $subs = implode('', array_map(function ($sub) {
        return "...$sub,";
      }, $subs));

      $replacement = 'fragment ' . $name . ' on ' . $model . ' {' . $subs;
      $this->processedFragments[$name] = preg_replace($regex, $replacement, $this->fragments[$name]);
    }

    return $this->processedFragments[$name];
  }

  /**
   * Add a fragment to the list of available fragments.
   *
   * @param string $name
   *   The fragment name. Corresponding to the template name.
   * @param string $fragment
   *   The fragment string.
   */
  public function addFragment($name, $fragment) {
    $this->fragments[$name] = $fragment;
  }

  /**
   * Parse a single source string.
   */
  public function parse($source) {
    return (new Parser())->parse($source);
  }

  /**
   * QueryAssembler constructor.
   */
  public function __construct() {
    $this->queries = [];
    $this->fragments = [];
    $this->processedFragments = [];
    $this->dependencies = [];
  }

}
