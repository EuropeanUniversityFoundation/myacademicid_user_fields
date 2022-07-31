<?php

namespace Drupal\myacademicid_user_hei\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\ewp_institutions_user\Event\UserInstitutionChangeEvent;
use Drupal\myacademicid_user_fields\Event\UserSchacHomeOrganizationChangeEvent;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Drupal\myacademicid_user_hei\MyacademicidUserHei;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * MyAcademicID user institution event subscriber.
 */
class MyacademicidUserHeiSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

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
   * Constructs event subscriber.
   *
   * @param \Drupal\myacademicid_user_hei\MyacademicidUserHei $maid_user_hei
   *   MyAcademicID user institution service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    MyacademicidUserHei $maid_user_hei,
    MessengerInterface $messenger,
    TranslationInterface $string_translation
  ) {
    $this->maidUserHei       = $maid_user_hei;
    $this->messenger         = $messenger;
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
    dpm($event);
  }

  /**
   * Subscribe to the user schac_home_organization change event.
   *
   * @param \Drupal\myacademicid_user_fields\Event\UserSchacHomeOrganizationChangeEvent $event
   *   The event object.
   */
  public function onUserSchacHomeOrganizationChange(UserSchacHomeOrganizationChangeEvent $event) {
    dpm(__METHOD__);
    foreach ($event->sho as $idx => $sho) {
      $exists = $this->maidUserHei->getHeiBySho($sho);

      if ($exists) {
        foreach ($exists as $id => $hei) {
          $renderable = $hei->toLink()->toRenderable();
        }
        $message = $this->t('User %user\'s %claim claim matches @link', [
          '%user' => $event->user->label(),
          '%claim' => MyacademicidUserFields::CLAIM_SHO,
          '@link' => render($renderable),
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
  }

}
