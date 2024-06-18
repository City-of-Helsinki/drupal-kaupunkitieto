<?php

declare(strict_types=1);

namespace Drupal\kaupunkitieto_infograph\Commands;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Exception\FieldStorageDefinitionUpdateForbiddenException;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drush\Commands\DrushCommands;

/**
 * A Drush command file.
 */
final class FixKaupunkitietoPlatformUpdate extends DrushCommands {

  use DependencySerializationTrait;
  use StringTranslationTrait;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(
    private readonly ModuleHandlerInterface $moduleHandler,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly Connection $connection,
  ) {
    parent::__construct();
  }

  /**
   * Fixes field type issues for the platform config update.
   *
   * @command kaupunkitieto:fix-field-issues
   */
  public function fixFieldIssues() : int {
    // For some reason a legacy field was added to the accordion paragraph type.
    // Remove the field as it's not needed and replace it with the correct one.
    $table = 'paragraph__field_accordion_heading_level';
    $entity_type = 'paragraph';
    $field_name = 'field_accordion_title_level';

    $field_storage = FieldStorageConfig::loadByName($entity_type, $field_name);

    // Only run if the field exists.
    if (!empty($field_storage)) {
      $rows = NULL;

      // Load the old field table data to restore after the update is completed.
      if ($this->connection->schema()->tableExists($table)) {
        $rows = $this->connection->select($table, 'n')
          ->fields('n')
          ->execute()
          ->fetchAll();
      }

      $new_fields = [];

      // Use existing field config for new field.
      foreach ($field_storage->getBundles() as $bundle => $label) {
        $field = FieldConfig::loadByName($entity_type, $bundle, $field_name);
        $new_field = $field->toArray();
        $new_field['field_type'] = 'list_string';
        $new_field['settings'] = [];
        $new_field['default_value'][] = [
          'value' => '2',
        ];
        $new_fields[] = $new_field;
      }

      // Create new field storage config.
      $new_field_storage = $field_storage->toArray();
      $new_field_storage['type'] = 'list_string';
      $new_field_storage['settings'] = [
        'allowed_values' => [
          '2' => 'h2',
          '3' => 'h3',
          '4' => 'h4',
          '5' => 'h5',
          '6' => 'h6',
        ],
        'allowed_values_function' => '',
      ];

      // Create new field storage if it doesn't exist.
      if (!FieldStorageConfig::loadByName($entity_type, 'field_accordion_title_level')) {
        $new_field_storage = FieldStorageConfig::create($new_field_storage);
        $new_field_storage->save();
      }

      // Create new field config if it doesn't exist.
      foreach ($new_fields as $new_field) {
        if (!$this->connection->schema()->tableExists('paragraph__field_accordion_title_level')) {
          $new_field = FieldConfig::create($new_field);
          $new_field->save();
        }
      }

      // Restore the old field table data to the new field table.
      if (
        !is_null($rows) &&
        $this->connection->schema()->tableExists('paragraph__field_accordion_title_level')
      ) {
        foreach ($rows as $row) {
          $row->field_accordion_title_level_value = $row->field_accordion_heading_level_value;
          unset($row->field_accordion_heading_level_value);
          $this->connection
            ->upsert('paragraph__field_accordion_title_level')
            ->fields((array) $row)
            ->key('entity_id', $row->entity_id)
            ->execute();
        }
      }
    }

    // Run the banner paragraph update again as it's needed
    // for the platform config v3 update.
    if ($this->moduleHandler->moduleExists('helfi_paragraphs_banner')) {
      try {
        $allowed_values = [];
        $allowed_values[] = [
          'value' => 'align-left',
          'label' => 'Aligned to the left',
        ];
        $allowed_values[] = [
          'value' => 'align-left-secondary',
          'label' => 'Aligned to the left, secondary color',
        ];
        $field_banner_design = FieldStorageConfig::loadByName('paragraph', 'field_banner_design');
        $field_banner_design->setSetting('allowed_values', $allowed_values);
        $field_banner_design->save();
      }
      catch (FieldStorageDefinitionUpdateForbiddenException $exception) {
        // We know the database values are different what we are trying
        // to write there, but we are only replacing the
        // values with allowed_values_function.
      }

      // Get all banner paragraphs and update their design.
      $banner_paragraphs = $this->entityTypeManager->getStorage('paragraph')
        ->getQuery('AND')
        ->condition('type', 'banner')
        ->condition('field_banner_design', [
          'align-center',
          'align-center-secondary',
        ], "IN")
        ->execute();

      $paragraphs = Paragraph::loadMultiple($banner_paragraphs);

      // Convert the banner paragraphs to the new allowed designs.
      foreach ($paragraphs as $paragraph) {
        if ($paragraph instanceof ParagraphInterface) {
          $field_update_map = [
            'align-center' => 'align-left',
            'align-center-secondary' => 'align-left-secondary',
          ];

          $paragraph
            ->set('field_banner_design', $field_update_map[$paragraph->field_banner_design->value])
            ->save();
        }
      }
    }

    // Run the hero design paragraph update again as it's needed
    // for the platform config v3 update.
    if ($this->moduleHandler->moduleExists('helfi_paragraphs_hero')) {
      try {
        $field_hero_design = FieldStorageConfig::loadByName('paragraph', 'field_hero_design');
        $field_hero_design->setSetting('allowed_values_function', 'helfi_paragraphs_hero_design_allowed_values');
        $field_hero_design->save();
      }
      catch (FieldStorageDefinitionUpdateForbiddenException $exception) {
        // We know the database values are different what we are trying
        // to write there, but we are only replacing the
        // values with allowed_values_function.
      }
    }

    // Get all hero paragraphs and update their color palettes based on
    // the ancient hero bg color field value.
    $hero_paragraph_query = $this->entityTypeManager
      ->getStorage('paragraph')
      ->getQuery()
      ->condition('type', 'hero');
    $heros = $hero_paragraph_query->execute();

    if (count($heros) > 0) {
      $this->output()->writeln($this->t('Found @amount hero paragraphs, which need to be migrated to use color_palette field. This will be run via batch.', [
        '@amount' => count($heros),
      ]));

      $batch = (new BatchBuilder())
        ->setProgressMessage($this->t('Processed @current out of @total.'))
        ->setErrorMessage($this->t('An error occurred during processing orphaned infograph_row paragraphs'))
        ->addOperation([$this, 'processBatch'], [
          $heros,
        ]);

      batch_set($batch->toArray());
      drush_backend_batch_process();
    }
    return DrushCommands::EXIT_SUCCESS;
  }

  /**
   * Processes a batch operation.
   */
  public function processBatch(
    array $entities,
    &$context,
  ) : void {

    // Check if the sandbox should be initialized.
    if (!isset($context['sandbox']['entities'])) {
      $context['sandbox']['entities'] = $entities;
    }

    $slice = array_slice($context['sandbox']['entities'], 0, 20, TRUE);

    try {
      /** @var \Drupal\paragraphs\ParagraphInterface $hero */
      foreach ($this->entityTypeManager
        ->getStorage('paragraph')
        ->loadMultiple($slice) as $hero) {

        // Remove the entity from the sandbox list.
        $key = array_search($hero->id(), $context['sandbox']['entities']);
        if ($key !== FALSE) {
          unset($context['sandbox']['entities'][$key]);
        }

        if (
          !$hero->hasField('field_hero_bg_color') ||
          $hero->get('field_hero_bg_color')->isEmpty()
        ) {
          continue;
        }

        $color = $hero->get('field_hero_bg_color')->value;
        $parent = $hero->getParentEntity();

        if ($parent->hasField('color_palette')) {
          $parent->set('color_palette', $color);
          $parent->save();
        }
      }

      // Everything has been processed?
      $context['finished'] = count($context['sandbox']['entities']) === 0;
    }
    catch (\Exception $e) {
      $context['message'] = $this->t('An error occurred during processing: @message', ['@message' => $e->getMessage()]);
      $context['finished'] = 1;
    }
  }
}
