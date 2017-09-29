<?php

namespace Drupal\graphql_twig;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\RendererInterface;

class GraphQLTwigExtension extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return get_class($this);
  }

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }


  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('embed', [$this, 'embed']),
    ];
  }

  public function embed($component, $data) {
    $arg = ['#theme' => is_array($component) ? implode('__', $component) : $component];
    if ($data) {
      foreach ($data as $key => $value) {
        $arg['#' . $key] = $value instanceof EntityInterface ? $value->id() : (string) $value;
      }
    }
    return $this->renderer->render($arg);
  }

}