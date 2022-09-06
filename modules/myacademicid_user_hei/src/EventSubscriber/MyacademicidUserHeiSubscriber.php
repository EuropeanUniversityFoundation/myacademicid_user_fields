<?php

namespace Drupal\myacademicid_user_hei\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\ewp_institutions_user\Event\SetUserInstitutionEvent;
use Drupal\ewp_institutions_user\Event\UserInstitutionChangeEvent;
use Drupal\myacademicid_user_fields\Event\SetUserSchacHomeOrganizationEvent;
use Drupal\myacademicid_user_fields\Event\UserSchacHomeOrganizationChangeEvent;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Drupal\myacademicid_user_hei\MyacademicidUserHei;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * MyAcademicID user institution event subscriber.
 */
class MyacademicidUserHeiSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * MyAcademicID user institution service.
   *
   * @var \Drupal\myacademicid_user_hei\MyacademicidUserHei
   */
  protected $maidUserHei;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   * @param \Drupal\myacademicid_user_hei\MyacademicidUserHei $maid_user_hei
   *   MyAcademicID user institution service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EventDispatcherInterface $event_dispatcher,
    MyacademicidUserHei $maid_user_hei,
    MessengerInterface $messenger,
    RendererInterface $renderer,
    TranslationInterface $string_translation
  ) {
    $this->configFactory     = $config_factory;
    $this->eventDispatcher   = $event_dispatcher;
    $this->maidUserHei       = $maid_user_hei;
    $this->messenger         = $messenger;
    $this->renderer          = $renderer;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      UserInstitutionChangeEvent::EVENT_NAME => [
        'onUserInstitutionChange'
      ],
      UserSchacHomeOrganizationChangeEvent::EVENT_NAME => [
        'onUserSchacHomeOrganizationChange'
      ],
    ];
  }

  /**
   * Subscribe to the user institution change event.
   *
   * @param \Drupal\ewp_institutions_user\Event\UserInstitutionChangeEvent $event
   *   The event object.
   */
  public function onUserInstitutionChange(UserInstitutionChangeEvent $event) {
    dpm(__METHOD__);
    $mode = $this->configFactory
      ->get('myacademicid_user_fields.settings')
      ->get('mode');

    // Default case: Server sets different HEI; rewrite SHO.
    if ($mode === MyacademicidUserFields::SERVER_MODE) {
      // Instantiate our event.
      $new_event = new SetUserSchacHomeOrganizationEvent(
        $event->user,
        $event->hei_id,
        FALSE
      );
      // Dispatch the event.
      $this->eventDispatcher->dispatch(
        $new_event,
        SetUserSchacHomeOrganizationEvent::EVENT_NAME
      );
    }
  }

  /**
   * Subscribe to the user schac_home_organization change event.
   *
   * @param \Drupal\myacademicid_user_fields\Event\UserSchacHomeOrganizationChangeEvent $event
   *   The event object.
   */
  public function onUserSchacHomeOrganizationChange(UserSchacHomeOrganizationChangeEvent $event) {
    dpm(__METHOD__);
    $mode = $this->configFactory
      ->get('myacademicid_user_fields.settings')
      ->get('mode');

    // Default case: Client sets different SHO; reassign HEI.
    if ($mode === MyacademicidUserFields::CLIENT_MODE) {
      $hei_list = [];
      $import = FALSE;

      foreach ($event->sho as $idx => $sho) {
        $exists = $this->maidUserHei->getHeiBySho($sho, $import);

        if ($exists) {
          foreach ($exists as $id => $hei) {
            $hei_list[] = $hei;
            $renderable = $hei->toLink()->toRenderable();
          }
          $message = $this->t('User %user\'s %claim claim matches @link', [
            '%user' => $event->user->label(),
            '%claim' => MyacademicidUserFields::CLAIM_SHO,
            '@link' => $this->renderer->render($renderable),
          ]);
          $this->messenger->addMessage($message);
        }
        else {
          $message = $this->t('No match found for %claim claim %sho.', [
            '%claim' => MyacademicidUserFields::CLAIM_SHO,
            '%sho' => $sho,
          ]);
          $this->messenger->addWarning($message);
        }
      }

      // Instantiate our new event.
      $new_event = new SetUserInstitutionEvent(
        $event->user,
        $hei_list,
        FALSE
      );
      // Dispatch the new event.
      $this->eventDispatcher->dispatch(
        $new_event,
        SetUserInstitutionEvent::EVENT_NAME
      );
    }
  }

}
