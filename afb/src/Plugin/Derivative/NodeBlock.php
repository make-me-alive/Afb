<?php

/**
 * @file
 * Contains \Drupal\afb\Plugin\Derivative\NodeBlock.
 */

namespace Drupal\afb\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides block plugin definitions for nodes.
 *
 * @see \Drupal\demo\Plugin\Block\NodeBlock
 */
class NodeBlock extends DeriverBase implements ContainerDeriverInterface
{
    /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Constructs new NodeBlock.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $node_storage
   *   The node storage.
   */
  public function __construct(EntityStorageInterface $node_storage)
  {
      $this->nodeStorage = $node_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id)
  {
      return new static(
      $container->get('entity.manager')->getStorage('node')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition)
  {
      $nodes = $this->nodeStorage->loadByProperties();

      foreach ($nodes as $node) {
          $this->derivatives[$node->id()] = $base_plugin_definition;
          $this->derivatives[$node->id()]['admin_label'] = t('Afb Node block: ').$node->label();
      }
              //
      return $this->derivatives;
  }
}
