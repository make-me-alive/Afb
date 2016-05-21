<?php
/**
 * @file
 * Contains \Drupal\afb\Controller\DefaultController.
 */

namespace Drupal\afb\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Default controller for the afb module.
 */
class DefaultController extends ControllerBase
{
    public function autocomplete(request $request)
    {
        $matches = array();
        $string = $request->query->get('q');
        if ($string) {
            $matches = array();
            $query = \Drupal::entityQuery('node')
            ->condition('status', 1)
            ->condition('title', '%'.db_like($string).'%', 'LIKE');
            $nids = $query->execute();
            $result = entity_load_multiple('node', $nids);
            foreach ($result as $row) {
                $matches[] = ['value' => $row->nid->value, 'label' => $row->title->value];
            }
        }

        return new JsonResponse($matches);
    }

    public function afb_admin_page()
    {
      $form[] = \Drupal::formBuilder()->getForm('Drupal\afb\Form\TableForm');
      $form[] = \Drupal::formBuilder()->getForm('Drupal\afb\Form\AddEditForm');
    // $form[] = \Drupal::formBuilder()->getForm('Drupal\afb\Form\Untitled');
        return $form;
    }
}
