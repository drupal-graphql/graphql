<?php

namespace Drupal\graphql_example\GraphQL\Field\Page;

use Drupal\Component\Render\MarkupInterface;
use Drupal\graphql_example\GraphQL\Field\SelfAwareField;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\Scalar\StringType;

class PageBodyField extends SelfAwareField implements ContainerAwareInterface {
  use ContainerAwareTrait;

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    if ($value instanceof NodeInterface && $value->bundle() === 'page') {
      /** @var \Drupal\Core\Render\RendererInterface $renderer */
      $renderer = $this->container->get('renderer');
      /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display */
      $display = $this->container->get('entity_type.manager')
        ->getStorage('entity_view_display')
        ->load('node.page.default');

      /** @var \Drupal\Core\Field\FormatterInterface $formatter */
      if (!$formatter = $display->getRenderer('body')) {
        return NULL;
      }

      $items = [$value->id() => $value->get('body')];
      $formatter->prepareView($items);
      $elements = array_map(function ($item) use ($renderer) {
        return $renderer->renderRoot($item);
      }, $formatter->viewElements($items[$value->id()], $value->language()->getId()));

      return implode('', array_map(function (MarkupInterface $element) {
        return (string) $element;
      }, $elements));
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'body';
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return new StringType();
  }
}