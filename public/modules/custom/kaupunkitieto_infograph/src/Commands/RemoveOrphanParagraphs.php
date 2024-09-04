<?php

declare(strict_types=1);

namespace Drupal\kaupunkitieto_infograph\Commands;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drush\Commands\DrushCommands;

/**
 * A Drush command file.
 */
final class RemoveOrphanParagraphs extends DrushCommands {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly Connection $connection,
  ) {
    parent::__construct();
  }

  /**
   * Removes all orphaned infograph_row paragraphs.
   *
   * @option limit
   *   Limit the amount of orphaned paragraphs to remove in a single run.
   * @option batch_size
   *   How many orphaned paragraphs to remove in one batch run.
   *
   * @usage drush kaupunkitieto:remove-orphaned-paragraphs --limit=1000
   *   Deletes 1000 orphaned paragraphs in a single run.
   *
   * @command kaupunkitieto:remove-orphaned-paragraphs
   */
  public function remove(int $limit = 100000, int $batch_size = 100) : int {

    $entity_storage = $this->entityTypeManager->getStorage('paragraph');

    $orphaned_infographs = $entity_storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'infograph_row')
      ->notExists('parent_id')
      ->notExists('parent_type')
      ->notExists('parent_field_name')
      ->count()
      ->execute();

    if ($orphaned_infographs === 0) {
      $this->output()->writeln($this->t('No orphaned paragraphs found.'));
      return DrushCommands::EXIT_SUCCESS;
    }

    if ($orphaned_infographs > 0) {
      // Clean up orphaned paragraphs.
      $entity_ids = $entity_storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', 'infograph_row')
        ->notExists('parent_id')
        ->notExists('parent_type')
        ->notExists('parent_field_name')
        ->range(0, $limit)
        ->execute();

      $this->output()->writeln("Found {$orphaned_infographs} orphaned paragraphs. Starting to delete the next {$limit} orphaned paragraphs...");

      $batch = (new BatchBuilder())
        ->setProgressMessage($this->t('Processed @current out of @total.'))
        ->setErrorMessage($this->t('An error occurred during processing orphaned infograph_row paragraphs'))
        ->addOperation([$this, 'processBatch'], [
          $entity_ids,
          $batch_size,
        ]);

      batch_set($batch->toArray());

      drush_backend_batch_process();

      return DrushCommands::EXIT_SUCCESS;
    }
    return DrushCommands::EXIT_FAILURE;
  }

  /**
   * Processes a batch operation.
   */
  public function processBatch(
    array $entity_ids,
    int $batch_size,
    &$context,
  ) : void {

    // Check if the sandbox should be initialized.
    if (!isset($context['sandbox']['entities'])) {
      $context['sandbox']['entities'] = $entity_ids;
    }

    $slice = array_slice($context['sandbox']['entities'], 0, $batch_size, TRUE);

    try {
      $entities = $this->entityTypeManager
        ->getStorage('paragraph')
        ->loadMultiple($slice);

      foreach ($entities as $entity) {
        $entity->delete();

        // Remove the entity from the sandbox list.
        $key = array_search($entity->id(), $context['sandbox']['entities']);
        if ($key !== FALSE) {
          unset($context['sandbox']['entities'][$key]);
        }
      }

      $remaining = count($context['sandbox']['entities']) - $batch_size >= 0
        ? count($context['sandbox']['entities']) - $batch_size
        : count($context['sandbox']['entities']);

      $context['message'] = $this->t("@total orphaned infograph_row paragraphs remaining", [
        '@total' => $remaining,
      ]);

      // Everything has been processed?
      $context['finished'] = count($context['sandbox']['entities']) === 0;
    }
    catch (\Exception $e) {
      $context['message'] = $this->t('An error occurred during processing: @message', ['@message' => $e->getMessage()]);
      $context['finished'] = 1;
    }
  }

}
