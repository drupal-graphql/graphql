<?php

namespace Drupal\graphql_twig;

use Drupal\Core\Template\TwigEnvironment;

/**
 * Enhanced Twig environment for GraphQL.
 *
 * Checks for GraphQL annotations in twig templates or matching `*.gql` and
 * adds them as `{% graphql %}` tags before passing them to the compiler.
 *
 * This is a convenience feature and also ensures that GraphQL-powered templates
 * don't break compatibility with Twig processors that don't have this extension
 * (e.g. patternlab).
 */
class GraphQLTwigEnvironment extends TwigEnvironment {

  /**
   * Regular expression to find a GraphQL annotation in a twig comment.
   *
   * @var string
   */
  public static $GRAPHQL_ANNOTATION_REGEX = '/{#graphql\s+(?<query>.*?)\s+#\}/s';

  public function compileSource($source, $name = NULL) {
    if ($source instanceof \Twig_Source) {
      // Check if there is a `*.gql` file with the same name as the template.
      $graphqlFile = str_replace('.html.twig', '.gql', $source->getPath());
      if (file_exists($graphqlFile)) {
        $source = new \Twig_Source(
          '{% graphql %}' . file_get_contents($graphqlFile) . '{% endgraphql %}' . $source->getCode(),
          $source->getName(),
          $source->getPath()
        );
      }
      else {
        // Else, try to find an annotation.
        $source = new \Twig_Source(
          $this->replaceAnnotation($source->getCode()),
          $source->getName(),
          $source->getPath()
        );
      }

    }
    else {
      // For inline templates, only comment based annotations are supported.
      $source = $this->replaceAnnotation($source);
    }

    // Compile the modified source.
    return parent::compileSource($source, $name);
  }

  /**
   * Replace `{#graphql ... #}` annotations with `{% graphql ... %}` tags.
   * @param $code
   *   The template code.
   *
   * @return string
   *   The template code with all annotations replaced with tags.
   */
  public function replaceAnnotation($code) {
    return preg_replace(static::$GRAPHQL_ANNOTATION_REGEX, '{% graphql %}$1{% endgraphql %}', $code);
  }

}
