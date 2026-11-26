<?php

declare(strict_types=1);

namespace Drupal\Tests\kaupunkitieto_config\Unit\Hook;

use Drupal\kaupunkitieto_config\Hook\TocHooks;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests table of contents hooks.
 */
#[Group('kaupunkitieto_config')]
class TocHooksTest extends UnitTestCase {

  /**
   * Article forms are added to the table of contents forms.
   */
  public function testArticleFormsAreAdded(): void {
    $forms = ['existing_form'];
    (new TocHooks())->helfiTocFormsAlter($forms);

    $this->assertSame([
      'existing_form',
      'node_article_form',
      'node_article_edit_form',
    ], $forms, 'Article forms are appended after existing forms.');
  }

}
