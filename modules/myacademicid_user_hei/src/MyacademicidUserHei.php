<?php

namespace Drupal\myacademicid_user_hei;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\UserInterface;
use Drupal\ewp_institutions_get\InstitutionManager;
use Drupal\ewp_institutions_lookup\InstitutionLookupManager;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;

/**
 * MyAcademicID user institution service.
 */
class MyacademicidUserHei {

  use StringTranslationTrait;

  const KEEP_IN_SYNC = 'sync';
  const SYNC_IF_EMPTY = 'empty';
  const DO_NOT_SYNC = 'nosync';

  /**
   * EWP Institutions manager service.
   *
   * @var \Drupal\ewp_institutions_get\InstitutionManager
   */
  protected $heiManager;

  /**
   * EWP Institutions lookup manager service.
   *
   * @var \Drupal\ewp_institutions_lookup\InstitutionLookupManager
   */
  protected $heiLookup;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The constructor.
   *
   * @param \Drupal\ewp_institutions_get\InstitutionManager $hei_manager
   *   EWP Institutions manager service.
   * @param \Drupal\ewp_institutions_user\InstitutionLookupManager $hei_lookup
   *   EWP Institutions lookup manager service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    InstitutionManager $hei_manager,
    InstitutionLookupManager $hei_lookup,
    LoggerChannelFactoryInterface $logger_factory,
    RendererInterface $renderer,
    TranslationInterface $string_translation
  ) {
    $this->heiManager        = $hei_manager;
    $this->heiLookup         = $hei_lookup;
    $this->logger            = $logger_factory->get('myacademicid_user_hei');
    $this->renderer          = $renderer;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Get Institution by schac_home_organization.
   *
   * @param string $sho
   *   The schac_home_organization value.
   * @param boolean $import
   *   Whether to lookup and import an Institution.
   *
   * @return array
   *   An array of [id => Drupal\ewp_institutions\Entity\InstitutionEntity]
   */
  public function getHeiBySho(string $sho, $import = FALSE): array {
    dpm(__METHOD__);
    $hei = $this->heiManager->getInstitution($sho);

    if (empty($hei) && $import) {
      $lookup = $this->heiLookup->lookup($sho);

      if (\array_key_exists($sho, $lookup)) {
        $hei = $this->heiManager
          ->getInstitution($sho, $create_from = $lookup[$sho]);
      }
    }

    return $hei;
  }

  /**
   * Log schac_home_organization claims without a matching Institution.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   * @param string $sho
   *   The schac_home_organization claim.
   * @param bool $import
   *   Whether there was an attempt to lookup and import an Institution.
   */
  public function logUnmatched(UserInterface $user, string $sho, bool $import): void {
    dpm(__METHOD__);
    $link = $user->toLink();
    $renderable = $link->toRenderable();

    $message = $this->t('User @link has an unmatched %claim claim of %sho.', [
      '@link' => $this->renderer->render($renderable),
      '%claim' => MyacademicidUserFields::CLAIM_SHO,
      '%sho' => $sho,
    ]);

    if ($import) {
      $this->logger->error($message);
    } else {
      $this->logger->warning($message);
    }
  }

}
