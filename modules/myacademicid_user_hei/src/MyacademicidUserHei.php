<?php

namespace Drupal\myacademicid_user_hei;

use Drupal\ewp_institutions_get\InstitutionManager;
use Drupal\ewp_institutions_lookup\InstitutionLookupManager;

/**
 * MyAcademicID user institution service.
 */
class MyacademicidUserHei {

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
   * The constructor.
   *
   * @param \Drupal\ewp_institutions_get\InstitutionManager $hei_manager
   *   EWP Institutions manager service.
   * @param \Drupal\ewp_institutions_user\InstitutionLookupManager $hei_lookup
   *   EWP Institutions lookup manager service.
   */
  public function __construct(
    InstitutionManager $hei_manager,
    InstitutionLookupManager $hei_lookup
  ) {
    $this->heiManager = $hei_manager;
    $this->heiLookup = $hei_lookup;
  }

  /**
   * Get Institution by schac_home_organization.
   *
   * @param string $sho
   *   The event object.
   *
   * @return array
   *   An array of [id => Drupal\ewp_institutions\Entity\InstitutionEntity]
   */
  public function getHeiBySho(string $sho, $import = FALSE): array {
    dpm(__METHOD__);
    $hei = $this->heiManager->getInstitution($sho);

    if (empty($hei) && $import) {
      $lookup = $this->heiLookup->lookup($sho);

      $hei = $this->heiManager
        ->getInstitution($sho, $create_from = $lookup[$sho]);
    }

    return $hei;
  }

}
