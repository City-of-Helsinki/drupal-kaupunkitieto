<?php

declare(strict_types=1);

namespace Drupal\kaupunkitieto_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Header Search block.
 *
 * @Block(
 *   id = "kaupunkitieto_search_header_search_block",
 *   admin_label = @Translation("Kaupunkitieto Header Search"),
 * )
 */
final class HeaderSearchBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * Creates a HelpBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LanguageManagerInterface $language_manager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('language_manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $search_page_path = match ($langcode) {
      'sv' => '/sv/haku',
      'en' => '/en/haku',
      default => '/fi/haku',
    };

    return [
      '#theme' => 'header_search',
      '#search_page_path' => $search_page_path,
    ];
  }

}
