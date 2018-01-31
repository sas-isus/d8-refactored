<?php

namespace Drupal\gathercontent_ui\Form;

use Cheppers\GatherContent\GatherContentClientInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\gathercontent\DrupalGatherContentClient;
use Drupal\gathercontent\Entity\Mapping;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MappingImportForm.
 *
 * @package Drupal\gathercontent\Form
 */
class MappingImportForm extends EntityForm {

  /**
   * GatherContent client.
   *
   * @var \Drupal\gathercontent\DrupalGatherContentClient
   */
  protected $client;

  /**
   * MappingImportForm constructor.
   *
   * @param \Cheppers\GatherContent\GatherContentClientInterface $client
   *   GatherContent client.
   */
  public function __construct(GatherContentClientInterface $client) {
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('gathercontent.client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $account_id = DrupalGatherContentClient::getAccountId();
    /** @var \Cheppers\GatherContent\DataTypes\Project[] $projects */
    $projects = [];
    if ($account_id) {
      $projects = $this->client->getActiveProjects($account_id);
    }

    $form['description'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => t("Please select the GatherContent Templates you'd like to map. Only Templates you've not selected will be listed."),
      '#attributes' => [
        'class' => ['description'],
      ],
    ];

    $form['projects'] = [
      '#type' => 'vertical_tabs',
    ];

    $form['template_counter'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'gather-content-counter-message',
        ],
      ],
      '#attached' => [
        'library' => [
          'gathercontent_ui/template_counter',
        ],
      ],
    ];

    $created_mapping_ids = Mapping::loadMultiple();
    $local_templates = [];

    foreach ($created_mapping_ids as $mapping) {
      /** @var \Drupal\gathercontent\Entity\Mapping $mapping */
      $local_templates[$mapping->getGathercontentTemplateId()] = $mapping->getGathercontentTemplate();
    }

    foreach ($projects as $project_id => $project) {
      $remote_templates = $this->client->getTemplatesOptionArray($project_id);
      $templates = array_diff_assoc($remote_templates, $local_templates);

      if (empty($templates)) {
        continue;
      }

      $form['p' . $project_id] = [
        '#type' => 'details',
        '#title' => $project->name,
        '#group' => 'projects',
        '#tree' => TRUE,
      ];
      $form['p' . $project_id]['templates'] = [
        '#type' => 'checkboxes',
        '#title' => $project->name,
        '#options' => $templates,
        '#attributes' => [
          'class' => [
            'gather-content-counted',
          ],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#id'] == 'edit-submit') {
      // Load all projects.
      $account_id = DrupalGatherContentClient::getAccountId();

      if (!$account_id) {
        drupal_set_message($this->t('No available accounts.'), 'error');
        $form_state->setRedirect('entity.gathercontent_mapping.collection');
        return;
      }

      /** @var \Cheppers\GatherContent\DataTypes\Project[] $projects */
      $projects = $this->client->getActiveProjects($account_id);

      $values = $form_state->getValues();

      foreach ($values as $k => $tree) {
        if (!is_array($tree)) {
          continue;
        }
        $templates = array_filter($values[$k]['templates']);
        foreach ($templates as $template_id => $selected) {
          $template = $this->client->templateGet($template_id);
          $templateBody = $this->client->getBody(TRUE);

          $mapping_values = [
            'id' => $template_id,
            'gathercontent_project_id' => $template->projectId,
            'gathercontent_project' => $projects[$template->projectId]->name,
            'gathercontent_template_id' => $template_id,
            'gathercontent_template' => $template->name,
            'template' => serialize($templateBody),
          ];

          $mapping = \Drupal::entityManager()
            ->getStorage('gathercontent_mapping')
            ->create($mapping_values);
          if (is_object($mapping)) {
            $mapping->save();
          }
        }
      }
    }

    $form_state->setRedirect('entity.gathercontent_mapping.collection');
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Select');
    $actions['close'] = [
      '#type' => 'submit',
      '#value' => t('Close'),
    ];
    return $actions;
  }

}
