<?php

/**
 * Options got new names.
 * 
 * @since 4.0.0
 */
class SocialLockerUpdate040000 extends Factory000_Update {

    public function install() {
        
        $activator = new OPanda_Activation( $this->plugin );
        $activator->activate();
    }
}