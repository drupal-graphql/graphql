<?php

namespace Drupal\graphql_twig;

use Drupal\Core\Template\TwigEnvironment;

class GraphQLTwigEnvironment extends TwigEnvironment {

  public static $GRAPHQL_ANNOTATION_REGEX = '/{#graphql\s+(?<query>.*)\s+#\}/s';

  public function compileSource($source, $name = NULL) {
    if ($source instanceof \Twig_Source) {
      $graphqlFile = str_replace('.html.twig', '.gql', $source->getPath());
      if (file_exists($graphqlFile)) {
        $source = new \Twig_Source(
          '{% graphql %}' . file_get_contents($graphqlFile) . '{% endgraphql %}' . $source->getCode(),
          $source->getName(),
          $source->getPath()
        );
      }
      else {
        $source = new \Twig_Source(
          $this->replaceAnnotation($source->getCode()),
          $source->getName(),
          $source->getPath()
        );
      }

    }
    else {
      $source = $this->replaceAnnotation($source);
    }

    return parent::compileSource($source, $name);
  }

  public function replaceAnnotation($code) {
    return preg_replace(static::$GRAPHQL_ANNOTATION_REGEX, '{% graphql %}$1{% endgraphql %}', $code);
  }

}