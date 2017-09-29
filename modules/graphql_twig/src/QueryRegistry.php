<?php

namespace Drupal\graphql_twig;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Theme\ActiveTheme;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\graphql\QueryProcessor;

class QueryRegistry {

  public static $GRAPHQL_TWIG_FILE_REGEX = '/.*\.html\.twig/';

  public static $GRAPHQL_TWIG_QUERY_REGEX = '/\{#graphql-query\n(?<query>.*)\n#\}\n/s';

  public static $GRAPHQL_TWIG_FRAGMENT_REGEX = '/\{#graphql-fragment\n(?<fragment>.*)\n#\}\n/s';

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The GraphQL query processor.
   *
   * @var \Drupal\graphql\QueryProcessor
   */
  protected $processor;

  /**
   * The query registry.
   *
   * @var array
   */
  protected $registry;

  /**
   * QueryRegistry constructor.
   *
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   */
  public function __construct(ThemeManagerInterface $themeManager, QueryProcessor $processor) {
    $this->themeManager = $themeManager;
    $this->processor = $processor;
    // TODO: load registry from cache.
  }

  /**
   * Process a templates GraphQL query.
   *
   * @param $filename
   *   The template filename.
   * @param $variables
   *   The variables provided to this template.
   *
   * @return array
   *   A set of new template variables or NULL if the template has no query.
   */
  public function processTemplate($filename, $variables) {
    $reg = $this->find($filename);

    if ($reg === NULL) {
      return NULL;
    }

    $vars = array_filter($variables, function ($key) use ($reg) {
      return in_array($key, $reg['variables']);
    }, ARRAY_FILTER_USE_KEY);

    $vars = array_map(function ($var) {
      return $var instanceof EntityInterface ? $var->id() : $var;
    }, $vars);

    // TODO: execute in render context.
    return $this->processor->processQuery($reg['query'], $vars)->getData();
  }

  /**
   * Find the registry entry for a given file.
   *
   * @param $filename
   *   The template filename.
   *
   * @return array
   *   An array with information or NULL if the template has no query.
   */
  protected function find($filename) {
    $theme = $this->themeManager->getActiveTheme();
    $themeName = $theme->getName();

    if (!isset($this->registry[$themeName])) {
      $this->registry[$themeName] = $this->build($theme);
    }

    if (!isset($this->registry[$themeName][$filename])) {
      return NULL;
    }

    return $this->registry[$themeName][$filename];
  }

  /**
   * Build the registry for a specific theme.
   *
   * @param \Drupal\Core\Theme\ActiveTheme $activeTheme
   *   The ActiveTheme object.
   *
   * @return array
   *   The themes query registry.
   */
  protected function build(ActiveTheme $activeTheme) {
    $registry = [];
    $fragments = [];

    $assembler = new QueryAssembler();

    $paths = [$activeTheme->getName() => $activeTheme->getPath()];

    $paths += array_map(function (ActiveTheme $theme) {
      return $theme->getPath();
    }, $activeTheme->getBaseThemes());

    foreach (array_reverse($paths) as $path) {
      foreach (file_scan_directory($path, static::$GRAPHQL_TWIG_FILE_REGEX) as $file) {
        $content = file_get_contents($file->uri);
        preg_match(static::$GRAPHQL_TWIG_QUERY_REGEX, $content, $matches);
        if (array_key_exists('query', $matches)) {
          $source = $assembler->parse($matches['query']);
          $registry[$file->uri]['query'] = $matches['query'];
          $registry[$file->uri]['variables'] = [];

          foreach ($source['variables'] as $variable) {
            /** @var \Youshido\GraphQL\Parser\Ast\ArgumentValue\Variable $variable */
            $registry[$file->uri]['variables'][] = $variable->getName();
          }
        }

        preg_match(static::$GRAPHQL_TWIG_FRAGMENT_REGEX, $content, $matches);
        if (array_key_exists('fragment', $matches)) {
          $assembler->addFragment($file, $matches['fragment']);
        }
      }
    }

    foreach ($registry as $file => $query) {
      $registry[$file]['query'] = $assembler->assemble($query['query']);
    }

    return $registry;
  }

}
