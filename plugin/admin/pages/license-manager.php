<?php

	/**
	 * License page is a place where a user can check updated and manage the license.
	 */
	class OnpPl_LicenseManagerPage extends OnpLicensing000_LicenseManagerPage {

		public $purchasePrice = '$25';

		public function configure()
		{

			//if( onp_lang('ru_RU') ) {
			$this->faq = false;
			$this->trial = false;
			$this->premium = false;
			$this->purchasePrice = '590р';
			//}

			/*if( onp_lang('en_US') ) {
				if( onp_build('ultimate') ) {
					$this->trial = false;
					$this->faq = false;
					$this->premium = false;
					$this->purchasePrice = '$59';
				} else {
					$this->purchasePrice = '$25';
				}
			}*/

			if( onp_build('free') ) {
				$this->menuPostType = OPANDA_POST_TYPE;
			} else {
				if( onp_license('free') ) {
					$this->menuTitle = __('Платный контент', 'bizpanda');
					$this->menuIcon = PAYLOCKER_URL . '/plugin/admin/assets/img/menu-icon.png';
				} else {
					$this->menuPostType = OPANDA_POST_TYPE;
				}
			}
		}
	}

	global $paylocker;
	FactoryPages000::register($paylocker, 'OnpPl_LicenseManagerPage');
	/*@mix:place*/