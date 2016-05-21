<?php
/**
 * @file
 *  shows the table and add edit functionalities for afb module
 */
namespace Drupal\afb\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

class AddEditForm extends FormBase
{
    public function getFormId()
    {
        return 'afb_edit_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form = array();

        $form['title'] = array(
    '#type' => 'textfield',
    '#title' => t('Title of the Block'),
    '#description' => t('Enter the Desired title'),
    '#size' => 60,
    '#required' => true,
     '#default' => ($form_state->isValueEmpty('title')) ? null : $form_state->getValue('title'),
  );

        $form['block_type'] = array(
    '#type' => 'select',
    '#title' => t('Type of the Block'),
    '#description' => t('Node Add or Node edit type block'),
    '#options' => array_combine(array(t('Add'), t('Edit')), array(t('Add'), t('Edit'))),
    '#default' => ($form_state->isValueEmpty('block_type')) ? 'Add' : $form_state->getValue('block_type'),
    '#required' => true,

  );

        $node_type_options = \Drupal::entityTypeManager()->getStorage('node_type')->loadMultiple();
        $options = array();
        foreach ($node_type_options as $node_type) {
            $options[$node_type->id()] = $node_type->label();
        }
        $form['content_type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Content type'),
      '#description' => t('Node Add or Node edit Content type'),
      '#options' => array_combine($options, $options),
      '#default' => ($form_state->isValueEmpty('content_type')) ? null : $form_state->getValue('content_type'),
      '#states' => array(
          'visible' => array(

            ':input[name="block_type"]' => array('value' => t('Add')),
          ),
          'required' => array(
                  ':input[name="block_type"]' => array('value' => t('Add')),
              ),
        ),

      );

        $form['nid'] = array(
    '#type' => 'textfield',
    '#title' => t('Name of the referenced node'),
    '#options' => array_combine($matches, $matches),
    '#autocomplete_route_name' => 'afb.autocomplete',
    '#description' => t('Node Add or Node edit type block'),
    '#default' => ($form_state->isValueEmpty('nid')) ? null : ($form_state->getValue('nid')),
    '#states' => array(
    'visible' => array(

    ':input[name="block_type"]' => array('value' => t('Edit')),
          ),
    'required' => array(
                  ':input[name="block_type"]' => array('value' => t('Edit')),
              ),
        ),
  );

        $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Create'),
  );

        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $data = serialize(array());
        $v = $form_state->getValues();
        $nids = (!$form_state->isValueEmpty('nid')) ? $v['nid'] : 0;

        if ($form_state->isValueEmpty('content_type')) {
            $db = \Drupal::database();
            $type = $db->select('node_field_data', 'n')->fields('n', array('type'))->condition('nid', $nids, '=')->execute()
            ->fetchField();
              } else {
            $type = $v['content_type'];
             }

          $insert = db_insert('afb_blocks_data')
         ->fields(array('title', 'content_type', 'form_type', 'nid', 'data'))
         ->values(array(
          'title' => $v['title'],
         'content_type' => $type,
         'form_type' => $v['block_type'],
          'nid' => $nids,
          'data' => $data,
             )
          )
          ->execute();

        if (isset($nid)) {
            drupal_set_message(t('The Block has been succesfully created'));
        } else {
            drupal_set_message(t('Error creating the block'));
        }
    }

}
