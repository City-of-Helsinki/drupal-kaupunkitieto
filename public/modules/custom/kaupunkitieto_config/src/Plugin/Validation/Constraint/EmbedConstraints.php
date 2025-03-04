<?php

declare(strict_types=1);

namespace Drupal\kaupunkitieto_config\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the fields are valid in embed paragraph.
 *
 * @Constraint(
 *   id = "EmbedConstraints",
 *   label = @Translation("Fields are not valid.", context = "Validation"),
 * )
 */
class EmbedConstraints extends Constraint {

  /**
   * Embed fields and corresponding error message keys.
   *
   * @var string[]
   */
  public const EMBED_FIELDS = [
    'field_embed_code' => 'embedCodeNotValid',
    'field_embed_code_id' => 'embedCodeIdNotValid',
    'field_embed_link' => 'embedLinkNotValid',
  ];

  /**
   * Embed fields and corresponding error message keys.
   *
   * @var string[]
   */
  public const VALID_VALUES = [
    'field_embed_code' => 'https://e.infogram.com/js/dist/embed.js?abc123',
    'field_embed_code_id' => 'infogram_0_xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    'field_embed_link' => 'https://infogram.com',
  ];

  /**
   * Validation patterns for each embed field.
   *
   * @var string[]
   */
  public const VALIDATION_PATTERNS = [
    'field_embed_code' => '#^https://e\.infogram\.com/js/dist/embed\.js\?.+#',
    'field_embed_code_id' => '#^infogram_#',
    'field_embed_link' => '#^https://infogram\.com/#',
  ];

  /**
   * Message for the embed code.
   *
   * @var string
   */
  public string $embedCodeNotValid = 'The embed code must be as follows: %value';

  /**
   * Message for the embed code.
   *
   * @var string
   */
  public string $embedCodeIdNotValid = 'The embed code ID must be as follows: %value';

  /**
   * Message for the embed code.
   *
   * @var string
   */
  public string $embedLinkNotValid = 'The embed link must start with %value. For example: %value/abc123';

}
