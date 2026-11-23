<?php

declare(strict_types=1);

namespace Drupal\Tests\kaupunkitieto_config\Unit\Hook;

use Drupal\kaupunkitieto_config\Hook\FormHooks;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests form hooks.
 */
#[Group('kaupunkitieto_config')]
class FormHooksTest extends UnitTestCase {

  /**
   * Node forms get the content liftup library.
   */
  public function testNodeFormGetsContentLiftupLibrary(): void {
    $form = ['#attached' => ['library' => ['core/drupal']]];
    (new FormHooks())->formNodeFormAlter($form);

    $this->assertSame([
      'core/drupal',
      'kaupunkitieto_config/admin-content-liftup',
    ], $form['#attached']['library'], 'Content liftup library is appended after existing libraries.');
  }

}
