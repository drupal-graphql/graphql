<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Blocks;

use Drupal\block\Entity\Block;
use Drupal\block_content\Plugin\Block\BlockContentBlock;
use Drupal\Core\Condition\ConditionInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\graphql\GraphQL\Batching\BatchedFieldResolver;
use Drupal\graphql\Plugin\GraphQL\Fields\SubrequestFieldBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * List all blocks within a theme region.
 *
 * TODO: Move this to `InternalUrl` (breaking change).
 *
 * @GraphQLField(
 *   id = "blocks_by_region",
 *   secure = true,
 *   name = "blocksByRegion",
 *   type = "Entity",
 *   parents = {"Url", "Root"},
 *   multi = true,
 *   arguments = {
 *     "region" = "String"
 *   }
 * )
 */
class BlocksByRegion extends SubrequestFieldBase {
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
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $pluginId,
    $pluginDefinition
  ) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('http_kernel'),
      $container->get('request_stack'),
      $container->get('graphql.batched_resolver'),
      $container->get('theme.manager'),
      $container->get('entity_type.manager'),
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    HttpKernelInterface $httpKernel,
    RequestStack $requestStack,
    BatchedFieldResolver $batchedFieldResolver,
    ThemeManagerInterface $themeManager,
    EntityTypeManager $entityTypeManager,
    EntityRepositoryInterface $entityRepository
  ) {
    $this->themeManager = $themeManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityRepository = $entityRepository;
    parent::__construct(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $httpKernel,
      $requestStack,
      $batchedFieldResolver
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveSubrequest($value, array $args, ResolveInfo $info) {
    $region = $args['region'];

    $activeTheme = $this->themeManager->getActiveTheme();
    $blockStorage = $this->entityTypeManager->getStorage('block');
    $blocks = $blockStorage->loadByProperties([
      'theme' => $activeTheme->getName(),
      'region' => $region,
    ]);

    $blocks = array_filter($blocks, function(Block $block) {
      return array_reduce(iterator_to_array($block->getVisibilityConditions()), function($value, ConditionInterface $condition) {
        return $value && (!$condition->isNegated() == $condition->evaluate());
      }, TRUE);
    });

    uasort($blocks, '\Drupal\Block\Entity\Block::sort');

    $result = array_map(function(Block $block) {
      $plugin = $block->getPlugin();
      if ($plugin instanceof BlockContentBlock) {
        return $this->entityRepository->loadEntityByUuid('block_content', $plugin->getDerivativeId());
      }
      else {
        return $block;
      }
    }, $blocks);

    return $result;

  }

}
