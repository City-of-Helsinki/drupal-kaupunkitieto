<?php

namespace Drupal\kaupunkitieto_infograph\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class KaupunkiTietoSettingsForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'kaupunkitieto_infograph_form';
  }

  /**
   * Load our only configuration as default param.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('kaupunkitieto_infograph.settings');

    // Source text field.
    $form['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Url:'),
      '#default_value' => $config->get('url'),
      '#description' => $this->t('Baseurl for Inforgraphs. Example: https://infographic-api.herokuapp.com/infographic/'),
    ];

    $form['actions']['#type'] = 'actions';
      $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
    * {@inheritdoc}
    */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::getContainer()->get('config.factory')->getEditable('kaupunkitieto_infograph.settings');
    $config->set('url', $form_state->getValue('url'));
    $config->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'kaupunkitieto_infograph.settings',
    ];
  }
}
