<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Blocks;

use Drupal\block\Entity\Block;
use Drupal\block_content\Plugin\Block\BlockContentBlock;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Condition\ConditionInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Buffers\SubRequestBuffer;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * List all blocks within a theme region.
 *
 * @GraphQLField(
 *   id = "blocks_by_region",
 *   secure = true,
 *   name = "blocksByRegion",
 *   type = "[entity:block]",
 *   parents = {"InternalUrl"},
 *   arguments = {
 *     "region" = "String!"
 *   }
 * )
 */
class BlocksByRegion extends FieldPluginBase implements ContainerFactoryPluginInterface {

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
   * The subrequest buffer service.
   *
   * @var \Drupal\graphql\GraphQL\Buffers\SubRequestBuffer
   */
  protected $subRequestBuffer;

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
      $container->get('graphql.buffer.subrequest'),
      $container->get('theme.manager'),
      $container->get('entity_type.manager'),
      $container->get('entity.repository')
    );
  }

  /**
   * BlocksByRegion constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\graphql\GraphQL\Buffers\SubRequestBuffer $subRequestBuffer
   *   The sub-request buffer service.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   *   The theme manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    SubRequestBuffer $subRequestBuffer,
    ThemeManagerInterface $themeManager,
    EntityTypeManagerInterface $entityTypeManager,
    EntityRepositoryInterface $entityRepository
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->subRequestBuffer = $subRequestBuffer;
    $this->themeManager = $themeManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityRepository = $entityRepository;
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof Url) {
      $activeTheme = $this->themeManager->getActiveTheme();
      $blockStorage = $this->entityTypeManager->getStorage('block');
      $blocks = $blockStorage->loadByProperties([
        'theme' => $activeTheme->getName(),
        'region' => $args['region'],
      ]);

      $resolve = $this->subRequestBuffer->add($value, function () use ($blocks) {
        $blocks = array_filter($blocks, function (Block $block) {
          return array_reduce(iterator_to_array($block->getVisibilityConditions()), function($value, ConditionInterface $condition) {
            return $value && (!$condition->isNegated() == $condition->evaluate());
          }, TRUE);
        });

        uasort($blocks, '\Drupal\Block\Entity\Block::sort');

        return $blocks;
      });

      return function ($value, array $args, ResolveContext $context, ResolveInfo $info) use ($resolve) {
        /** @var \Drupal\graphql\GraphQL\Cache\CacheableValue $response */
        $response = $resolve();
        $blocks = array_map(function (Block $block) {
          $plugin = $block->getPlugin();
          if ($plugin instanceof BlockContentBlock) {
            return $this->entityRepository->loadEntityByUuid('block_content', $plugin->getDerivativeId());
          }
          else {
            return $block;
          }
        }, $response->getValue());

        foreach ($blocks as $block) {
          yield new CacheableValue($block, [$response]);
        }
      };
    }
  }
}
