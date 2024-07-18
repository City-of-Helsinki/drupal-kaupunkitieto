<?php

declare(strict_types=1);

namespace Drupal\kaupunkitieto_infograph\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings form for kaupunkitieto infograph.
 */
final class KaupunkiTietoSettingsForm extends FormBase {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a database object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
    );
  }

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
    $config = $this->configFactory->getEditable('kaupunkitieto_infograph.settings');
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
