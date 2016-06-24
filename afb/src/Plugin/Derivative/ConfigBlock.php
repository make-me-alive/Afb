<?php
/**
 * @file
 * Contains \Drupal\afb\Plugin\Derivative\ConfigBlock.
 */

namespace Drupal\afb\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;

/**
 * Provides block plugin definitions for custom add/edit type blocks.
 *
 * @see \Drupal\afb\Plugin\Block\ConfigBlock
 */
class ConfigBlock extends DeriverBase
{
    /**
     * {@inheritdoc}
     */
    public function getDerivativeDefinitions($base_plugin_definition)
    {
        $results = \Drupal::database()->select('afb_blocks_data', 'n')
    ->fields('n', array('delta', 'title', 'content_type', 'form_type', 'nid'))
    ->execute();
        foreach ($results as $result) {
            $this->derivatives[$result->delta ] = $base_plugin_definition;
            $this->derivatives[$result->delta ]['admin_label'] = t('@name   (@type @ctype)nid: @id', array('@name' => $result->title, '@ctype' => $result->content_type, '@type' => $result->form_type, '@id' => $result->nid));
        }

  //      return array('cache' => array(
  //      'max_age' => 0,
  //    ),
  //  );
  // $build = [ '#cache' => [ 'max_age' => 0, ], ];
        return $this->derivatives;
    }
}
