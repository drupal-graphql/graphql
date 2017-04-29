<?php

namespace Drupal\graphql_block;

use Drupal\block\Entity\Block;
use Drupal\block_content\Plugin\Block\BlockContentBlock;
use Drupal\Core\Condition\ConditionInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Service using HTTP kernel to extract Drupal context objects.
 *
 * Replaces the controller of requests containing the "graphql_context"
 * attribute with itself and returns a context response instead that will be
 * use as field value for graphql context fields.
 */
class BlockExtractor extends ControllerBase {

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('theme.manager'),
      $container->get('entity_type.manager'),
      $container->get('entity.repository')
    );
  }

  /**
   * BlockExtractor constructor.
   *
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   *   A theme manager instance.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   An entity type manager instance.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   An entity repository instance.
   */
  public function __construct(ThemeManagerInterface $themeManager, EntityTypeManagerInterface $entityTypeManager, EntityRepositoryInterface $entityRepository) {
    $this->themeManager = $themeManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityRepository = $entityRepository;
  }

  /**
   * Handle kernel request events.
   *
   * If there is a `graphql_context` attribute on the current request, pass the
   * request to a context extraction.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The kernel event object.
   */
  public function onKernelRequest(GetResponseEvent $event) {
    $request = $event->getRequest();
    if ($request->attributes->has('graphql_block_region')) {
      $request->attributes->set('_controller', '\Drupal\graphql_block\BlockExtractor:extract');
    }
  }

  /**
   * Extract the required context and return it.
   *
   * @return \Drupal\graphql_block\BlockResponse
   *   A context response instance.
   */
  public function extract() {
    $region = \Drupal::request()->attributes->get('graphql_block_region');
    $response = new BlockResponse();

    $active_theme = $this->themeManager->getActiveTheme();
    $block_storage = $this->entityTypeManager->getStorage('block');
    $blocks = $block_storage->loadByProperties([
      'theme' => $active_theme->getName(),
      'region' => $region,
    ]);

    $blocks = array_filter($blocks, function (Block $block) {
      return array_reduce(iterator_to_array($block->getVisibilityConditions()), function ($value, ConditionInterface $condition) {
        return $value && (!$condition->isNegated() == $condition->evaluate());
      }, TRUE);
    });

    uasort($blocks, '\Drupal\Block\Entity\Block::sort');


    $response->setBlocks(array_map(function (Block $block) {
      $plugin = $block->getPlugin();
      if ($plugin instanceof BlockContentBlock) {
        return $this->entityRepository->loadEntityByUuid('block_content', $plugin->getDerivativeId());
      }
      else {
        return $block;
      }
    }, $blocks));

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::REQUEST => 'onKernelRequest'];
  }
}