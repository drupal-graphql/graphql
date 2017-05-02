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
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service using HTTP kernel to extract Drupal block objects.
 *
 * Replaces the controller of requests containing the "graphql_block_reqion"
 * attribute with itself and returns a block response instead that will be
 * use as field value for graphql block fields.
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
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('theme.manager'),
      $container->get('entity_type.manager'),
      $container->get('entity.repository'),
      $container->get('request_stack')
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
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   A symfony request stack.
   */
  public function __construct(
    ThemeManagerInterface $themeManager,
    EntityTypeManagerInterface $entityTypeManager,
    EntityRepositoryInterface $entityRepository,
    RequestStack $requestStack
  ) {
    $this->themeManager = $themeManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityRepository = $entityRepository;
    $this->requestStack = $requestStack;
  }

  /**
   * Extract the blocks for the passed region and return it.
   *
   * @return \Drupal\graphql_block\BlockResponse
   *   A block response instance.
   */
  public function extract() {
    $region = $this->requestStack->getCurrentRequest()->attributes->get('graphql_block_region');
    $response = new BlockResponse();

    $activeTheme = $this->themeManager->getActiveTheme();
    $blockStorage = $this->entityTypeManager->getStorage('block');
    $blocks = $blockStorage->loadByProperties([
      'theme' => $activeTheme->getName(),
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
}