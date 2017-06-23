<?php

namespace Drupal\graphql_metatag;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Controller\ControllerBase;
use Drupal\metatag\MetatagManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service using HTTP kernel to extract Drupal metatags.
 */
class MetatagExtractor extends ControllerBase {

  /**
   * The metatag manager.
   *
   * @var \Drupal\metatag\MetatagManagerInterface
   */
  protected $metatagManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('metatag.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(MetatagManagerInterface $metatagManager) {
    $this->metatagManager = $metatagManager;
  }

  /**
   * Extract the metatag information and return it.
   *
   * @return \Drupal\graphql_metatag\MetatagResponse
   *   A metatag response instance.
   */
  public function extract() {
    $response = new MetatagResponse();

    $metatags = metatag_get_tags_from_route();

    $metatags = NestedArray::getValue($metatags, ['#attached', 'html_head']) ?: [];

    $metatags = array_filter($metatags, function ($tag) {
      return is_array($tag) && in_array(NestedArray::getValue($tag, [0, '#tag']), ['meta', 'link']);
    });

    $metatags = array_filter(array_map(function ($tag) {
      return $this->transformTag($tag[0]);
    }, $metatags));

    $response->setMetatags($metatags);
    return $response;
  }

  /**
   * Transform a rendered tag into a GraphQL value object.
   */
  protected function transformTag($tag) {
    if ($tag['#tag'] === 'meta') {
      if (array_key_exists('property', $tag['#attributes'])) {
        return [
          'type' => 'MetaProperty',
          'key' => $tag['#attributes']['property'],
          'value' => $tag['#attributes']['content'],
        ];
      }
      else {
        return [
          'type' => 'MetaValue',
          'key' => $tag['#attributes']['name'],
          'value' => $tag['#attributes']['content'],
        ];
      }
    }
    if ($tag['#tag'] === 'link') {
      return [
        'type' => 'MetaLink',
        'key' => $tag['#attributes']['rel'],
        'value' => $tag['#attributes']['href'],
      ];
    }
  }

}
