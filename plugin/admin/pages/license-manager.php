<?php 
/**
 * License page is a place where a user can check updated and manage the license.
 */
class SocialLocker_LicenseManagerPage extends OnpLicensing000_LicenseManagerPage  {
 
    public $purchasePrice = '$25';
    
    public function configure() {
        
        if ( onp_lang('ru_RU') ) {
            $this->faq = false;
            $this->trial = false;
            $this->premium = false;
            $this->purchasePrice = '590р';
        }
        
        if ( onp_lang('en_US') ) {
            if ( onp_build('ultimate') ) {
                $this->trial = false;
                $this->faq = false;
                $this->premium = false;
                $this->purchasePrice = '$59';
            } else {
                $this->purchasePrice = '$25';
            }
        }
        
        if ( onp_lang('tr_TR') ) {
            $this->trial = false;
            $this->faq = false;
        }

        if ( onp_build('free') ) {
            $this->menuPostType = OPANDA_POST_TYPE;
        } else {
            if ( onp_license('free') ) {
                
                if (onp_lang('ru_RU') ) {
                    $this->menuTitle = __('Social Locker', 'bizpanda');
                } else {
                    $this->menuTitle = __('Social Locker', 'bizpanda');
                }

                $this->menuIcon = SOCIALLOCKER_URL . '/plugin/admin/assets/img/menu-icon.png';
            } else {
                $this->menuPostType = OPANDA_POST_TYPE;
            }
        }
    }
}

FactoryPages000::register($sociallocker, 'SocialLocker_LicenseManagerPage');
 /*@mix:place*/