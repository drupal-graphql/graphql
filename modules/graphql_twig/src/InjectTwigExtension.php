<?php

namespace Drupal\graphql_twig;

use Drupal\Core\Template\TwigExtension;

class InjectTwigExtension extends TwigExtension {

  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('inject', [$this, 'inject']),
    ];
  }

  public function inject($component, $data) {
    $arg = ['#theme' => $component];
    foreach ($data as $key => $value) {
      $arg['#' . $key] = $value;
    }
    return $this->renderVar($arg);
  }

}