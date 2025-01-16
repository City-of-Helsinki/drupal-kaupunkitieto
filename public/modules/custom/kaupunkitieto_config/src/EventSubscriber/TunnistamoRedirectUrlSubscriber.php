<?php

declare(strict_types=1);

namespace Drupal\kaupunkitieto_config\EventSubscriber;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\helfi_tunnistamo\Event\RedirectUrlEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Tunnistamo return url subscriber.
 */
final class TunnistamoRedirectUrlSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   */
  public function __construct(
    private readonly LanguageManagerInterface $languageManager,
  ) {
  }

  /**
   * Responds to Tunnistamo redirect url event.
   *
   * @param \Drupal\helfi_tunnistamo\Event\RedirectUrlEvent $event
   *   Response event.
   */
  public function onRedirectUrlEvent(RedirectUrlEvent $event) : void {
    $returnUrl = sprintf('/fi/openid-connect/%s', $event->getClient()->getParentEntityId());

    $uriOptions['language'] = $this->languageManager->getLanguage('fi');

    try {
      // Overwrite return url so that it includes fi language prefix. There is
      // no technical reason for this, we asked for return urls with /fi/
      // prefix by accident. This can be removed in the future, if accepted
      // return urls are reconfigured by Helsinki profile.
      $event->setRedirectUrl(Url::fromUserInput($returnUrl, $uriOptions)->setAbsolute());
    }
    catch (\InvalidArgumentException $e) {
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() : array {
    return [
      RedirectUrlEvent::class => ['onRedirectUrlEvent'],
    ];
  }

}
