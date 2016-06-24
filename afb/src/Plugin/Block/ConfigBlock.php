<?php

namespace Drupal\afb\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeForm;

/**
 * Provides a 'ConfigBlock' block plugin.
 *
 * @Block(
 *   id = "afb_block",
 *   admin_label = @Translation("Afb block"),
 *   deriver = "Drupal\afb\Plugin\Derivative\ConfigBlock"
 * )
 */
class ConfigBlock extends BlockBase
{
    public function build()
    {
        // drupal_flush_all_caches();
        // $block_id = $this->getDerivativeId();
        // dpm($block_id);
        // $block_id[]['#cache']['max-age'] = 0;
        //
        // return $block_id;
// cache_clear_all();
  // $build = array(
  //   '#key' => $this->getDerivativeId(),
  // '#cache' => array('max-age' => 0),
  // );
      // dpm($block_id);
  return $build;
    }

    public function afb_get_node_form_block_data($delta)
    {
        $block_id = $this->getDerivativeId();
        $delta = $block_id;
        $result = db_select('afb_blocks_data', 'n')
    ->fields('n', array('delta', 'title', 'content_type', 'form_type', 'nid', 'data'))->condition('n.delta', $delta, '=')->execute();
        foreach ($result as $row) {
            $result_obj = $row;
        }

        return $result_obj;
    }

    public function blockForm($form, FormStateInterface $form_state)
    {
        // $form = parent::blockForm($form, $form_state);
          // $form_state->setAlwaysProcess(TRUE);

      $block_info = $this->afb_get_node_form_block_data($delta);

        if ($block_info->form_type === 'Add') {
            return  $form = $this->afb_configure_node_add_block($delta);
        } elseif ($block_info->form_type === 'Edit') {
            return $form = $this->afb_configure_node_edit_block($delta);
        }
    }

    public function afb_configure_node_add_block($delta)
    {
        $form = array();
        $block_info = $this->afb_get_node_form_block_data($delta);
        $type = $block_info->content_type;
        $entity_type = 'node';
        $bundle_type = strtolower($type);
        foreach (\Drupal::entityManager()->getFieldDefinitions($entity_type, $bundle_type) as $field_name => $field_definition) {
            if (!empty($field_definition->getTargetBundle())) {
                if ($field_definition->getLabel() !=  'Promoted to front page') {
                    $field_options[$field_definition->getName()] = $field_definition->getLabel();
                }
            }
        }

        module_load_include('php', 'node', 'NodeForm');
        dpm(module_load_include('php', 'node', 'NodeForm'));
        $vertical_tab_options = array(t('Revision information'),
    t('Menu settings'),
    t('Comment settings'),
    t('URL path settings'),
    t('Authoring information'),
    t('Promotion options'),
  );

        $block_detail_array = $this->afb_get_node_form_block_data($delta);
        $block_detail_data = unserialize($block_detail_array->data);

        $fields = empty($block_detail_data['node_fields']) ? $field_options : $block_detail_data['node_fields'];
        $tabs = empty($block_detail_data['vertical_tabs']) ? $vertical_tab_options : $block_detail_data['vertical_tabs'];

        $form['node_fields'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Select the fields you want to keep in the block'),
    '#options' => array_combine($field_options, $field_options),
    '#default_value' => $fields,

  );

        $form['vertical_tabs'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Select the vertical tab components you want to keep in the block'),
    '#options' => array_combine($vertical_tab_options, $vertical_tab_options),
    '#default_value' => $tabs,
  );

        return $form;
    }

/*
 * Presents the node edit type blocks settings form.
 */

 public function afb_configure_node_edit_block($delta)
 {
     $form = array();

     $block_info = $this->afb_get_node_form_block_data($delta);
     $nid = $block_info->nid;
     $node = \Drupal\node\Entity\Node::load($nid);
     $node->ajax_form = 1;
     $entity_type = 'node';
     $bundle_type = $node->bundle();
     foreach (\Drupal::entityManager()->getFieldDefinitions($entity_type, $bundle_type) as $field_name => $field_definition) {
         if (!empty($field_definition->getTargetBundle())) {
             if ($field_definition->getLabel() !=  'Promoted to front page') {
                 $bundleFields[$entity_type_id][$field_name]['type'] = $field_definition->getType();

                 $field_options[$field_definition->getName()] = $field_definition->getLabel();
             }
         }
     }

     module_load_include('inc', 'node', 'node.pages');
     $vertical_tab_options = array(t('Revision information'),
    t('Menu settings'),
    t('Comment settings'),
    t('URL path settings'),
    t('Authoring information'),
    t('Promotion options'),
  );

     $block_detail_array = $this->afb_get_node_form_block_data($delta);
     $block_detail_data = unserialize($block_detail_array->data);

     $fields = empty($block_detail_data['node_fields']) ? $field_options : $block_detail_data['node_fields'];
     $tabs = empty($block_detail_data['vertical_tabs']) ? $vertical_tab_options : $block_detail_data['vertical_tabs'];

     $vertical_tab_default = \Drupal::state()->get('vertical_tabs_selection_'.$node->id(), '');

     $form['node_fields'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Select the fields you want to keep in the block'),
    '#options' => array_combine($field_options, $field_options),
    '#default_value' => $fields,
  );

     $form['vertical_tabs'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Select the vertical tab components you want to keep in the block'),
    '#options' => array_combine($vertical_tab_options, $vertical_tab_options),
    '#default_value' => $tabs,
  );

     return $form;
 }

/**
 * Implements hook_node_delete().
 */
public function afb_node_delete(\Drupal\Core\Entity\EntityInterface $node)
{
    $result = db_select('afb_blocks_data', 'n')
                  ->fields('n', array('delta', 'nid'))
                  ->condition('n.nid', $node->nid, '=')
                  ->execute();
    $count = $result->rowCount();
    if ($count > 0) {
        foreach ($result as $row) {
            $deltas[] = $row->delta;
        }
        foreach ($deltas as $delta) {
            afb_block_delete($delta);
        }
        $nid = array($node->nid);
        db_delete('afb_blocks_data')
            ->condition('nid', $nid)
            ->execute();
    }
}

    public function blockSubmit($form, FormStateInterface $form_state)
    {

  /**
   * save block configurations on submit
   */

      $block_id = $this->getDerivativeId();
      $delta = $block_id;

        $data = serialize(array(
                           'node_fields' => $form_state->getValue('node_fields'),
                           'vertical_tabs' => $form_state->getValue('vertical_tabs'), ));
        $num_updated = db_update('afb_blocks_data')
                    ->fields(array(
                      'data' => $data,
                    ))
                    ->condition('delta', $delta, '=')
                    ->execute();
    }
}
