<?php

namespace Drupal\graphql_twig;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Template\TwigExtension;

class GraphQLTwigExtension extends TwigExtension {

  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('embed', [$this, 'embed']),
    ];
  }

  public function embed($component, $data) {
    $arg = ['#theme' => is_array($component) ? implode('__', $component) : $component];
    foreach ($data as $key => $value) {
      $arg['#' . $key] = $value instanceof EntityInterface ? $value->id() : (string) $value;
    }
    return $this->renderVar($arg);
  }

}