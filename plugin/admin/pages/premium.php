<?php
/**
 * The file contains a short help info.
 * 
 * @author Paul Kashtanoff <paul@byonepress.com>
 * @copyright (c) 2014, OnePress Ltd
 * 
 * @package core 
 * @since 1.0.0
 */

/**
 * Common Settings
 */
class OnpSL_PremiumPage extends FactoryPages000_AdminPage  {
 
    public $menuPostType = OPANDA_POST_TYPE;
    public $id = "premium";
    
    public function __construct(Factory000_Plugin $plugin) {   
        parent::__construct($plugin);
        add_filter( 'factory_menu_title_premium-' . $plugin->pluginName, array( $this, 'fixMenuTitle') ) ;
    }
    
    public function fixMenuTitle() {
        if ( BizPanda::isSinglePlugin() ) return __('Go Premium', 'bizpanda');
        return '<span class="factory-fontawesome-000"><i class="fa fa-star-o" style="margin-right: 5px;"></i>' . __('Social Locker', 'bizpanda') . '</span>';
    }
    
    public function assets($scripts, $styles) {
        $this->scripts->request('jquery');
        $this->styles->add(OPANDA_BIZPANDA_URL . '/assets/admin/css/premium.030100.css');   
        $this->styles->request('bootstrap.core', 'bootstrap');
    }

    /**
     * Shows 'Get more features!'
     * 
     * @sinve 1.0.0
     * @return void
     * 
     */
    public function indexAction() {
        global $sociallocker;
        
        $alreadyActivated = get_option('onp_trial_activated_' . $sociallocker->pluginName, false);
        
        $skipTrial = get_option('onp_sl_skip_trial', false);
        if ( $skipTrial ) {
            wp_redirect( onp_sl_get_premium_url('go-premium') );
            exit;
        }
        
        ?>
        <div class="wrap factory-bootstrap-000 factory-fontawesome-000">
            <div class="onp-page-content">
                <div class="onp-inner-wrap">
                    
        <div class="onp-page-section">
            
            <?php if ( !$alreadyActivated ) { ?>
                <h1><?php _e('Try Premium Version For 7 Days For Free!', 'bizpanda'); ?></h1>
            <?php } else { ?>
                <h1><?php _e('Upgrade Social Locker To Premium!', 'bizpanda'); ?></h1>
            <?php } ?>

            <?php if ( !$alreadyActivated ) { ?>  
            <p>
                <?php printf( __('The plugin you are using is a free version of the popular <a target="_blank" href="%s"> Social Locker</a> plugin. 
                We offer you to try the premium version for 7 days absolutely for free. We sure you will love it.', 'bizpanda'), onp_sl_get_premium_url('go-premium') ) ?>
            </p>
            <p>
                <?php _e('Check out the table below to know about the premium features.', 'bizpanda'); ?>
            </p>
            <?php } else { ?>
            <p>
                <?php _e('The plugin you are using is a free version of the popular <a target="_blank" href="%s"> Social Locker plugin</a> sold on CodeCanyon.', 'bizpanda') ?>
                <?php _e('Check out the table below to know about all the premium features.', 'bizpanda'); ?>
            </p>   
            <?php } ?>

        </div>

        <div class="onp-page-section">
            <h2><i class="fa fa-star-o"></i> <?php _e('Comparison of Free & Premium Versions', 'bizpanda'); ?></h2>
            <p><?php _e('Click on the dotted title to learn more about a given feature.', 'bizpanda'); ?></p>
            <table class="table table-bordered onp-how-comparation">
                <tbody>
                    
                    <tr class="onp-how-group">
                        <td class="onp-how-group-title"><i class="fa fa-cogs"></i> <?php _e('Common Features', 'bizpanda'); ?></td>
                        <td class="onp-how-yes"><?php _e('Free', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><?php _e('Premium', 'bizpanda'); ?></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title"><?php _e('Unlimited Lockers', 'bizpanda'); ?></td>
                        <td class="onp-how-yes"><?php _e('yes', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><?php _e('yes', 'bizpanda'); ?></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title"><?php _e('Manual Lock (via shortcodes)', 'bizpanda'); ?></td>
                        <td class="onp-how-yes"><?php _e('yes', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><?php _e('yes', 'bizpanda'); ?></td>
                    </tr>  
                    <tr>
                        <td class="onp-how-title"><?php _e('Batch Lock (3 modes)', 'bizpanda'); ?></td>
                        <td class="onp-how-yes"><?php _e('yes', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><?php _e('yes', 'bizpanda'); ?></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title"><a href="#extra-options"><?php _e('Visibility Options', 'bizpanda'); ?></a></td>
                        <td class="onp-how-no">-</td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('yes', 'bizpanda'); ?></strong></td>
                    </tr>    
                        <td class="onp-how-title"><a href="#extra-options"><?php _e('Advanced Options', 'bizpanda'); ?></a></td>
                        <td class="onp-how-no">-</td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('yes', 'bizpanda'); ?></strong></td>
                    </tr>   
                    
                    <tr class="onp-how-group-separator">
                      <td colspan="3"></td>   
                    </tr>
                    <tr class="onp-how-group">
                        <td class="onp-how-group-title"><i class="fa fa-bullhorn"></i> <?php _e('Social Locker', 'bizpanda'); ?></td>
                        <td class="onp-how-yes"><?php _e('Free', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><?php _e('Premium', 'bizpanda'); ?></td>
                    </tr>

                    <tr>
                        <td class="onp-how-title"><?php _e('Facebook Like', 'bizpanda'); ?></td>
                        <td class="onp-how-yes"><?php _e('yes', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><?php _e('yes', 'bizpanda'); ?></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title"><?php _e('Twitter Tweet', 'bizpanda'); ?></td>
                        <td class="onp-how-yes"><?php _e('yes', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><?php _e('yes', 'bizpanda'); ?></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title"><?php _e('Google +1', 'bizpanda'); ?></td>
                        <td class="onp-how-yes"><?php _e('yes', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><?php _e('yes', 'bizpanda'); ?></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title"><?php _e('Facebook Share', 'bizpanda'); ?></a></td>
                        <td class="onp-how-no">-</td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('yes', 'bizpanda'); ?></strong></td>
                    </tr>    
                    <tr>
                        <td class="onp-how-title"><?php _e('Twitter Follow', 'bizpanda'); ?></td>
                        <td class="onp-how-no">-</td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('yes', 'bizpanda'); ?></strong></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title"><?php _e('LinkedIn Share', 'bizpanda'); ?></td>
                        <td class="onp-how-no">-</td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('yes', 'bizpanda'); ?></strong></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title"><?php _e('Google Share', 'bizpanda'); ?></td>
                        <td class="onp-how-no">-</td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('yes', 'bizpanda'); ?></strong></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title"><?php _e('YouTube Subscribe', 'bizpanda'); ?></td>
                        <td class="onp-how-no">-</td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('yes', 'bizpanda'); ?></strong></td>
                    </tr>
                    
                    <tr class="onp-how-group-separator">
                      <td colspan="3"></td>   
                    </tr>
                    <tr class="onp-how-group">
                        <td class="onp-how-group-title"><i class="fa fa-user"></i> <?php _e('Sign-In Locker', 'bizpanda'); ?></td>
                        <td class="onp-how-yes"><?php _e('Free', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><?php _e('Premium', 'bizpanda'); ?></td>
                    </tr>

                    <tr>
                        <td class="onp-how-title"><?php _e('Facebook Sign-In', 'bizpanda'); ?></td>
                        <td class="onp-how-yes"><?php _e('yes', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><?php _e('yes', 'bizpanda'); ?></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title"><?php _e('Twitter Sign-In', 'bizpanda'); ?></td>
                        <td class="onp-how-yes"><?php _e('yes', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><?php _e('yes', 'bizpanda'); ?></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title"><?php _e('Google Sign-In', 'bizpanda'); ?></td>
                        <td class="onp-how-yes"><?php _e('yes', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><?php _e('yes', 'bizpanda'); ?></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title"><?php _e('LinkedIn Sign-In', 'bizpanda'); ?></a></td>
                        <td class="onp-how-no">-</td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('yes', 'bizpanda'); ?></strong></td>
                    </tr>    
                    <tr>
                        <td class="onp-how-title"><?php _e('Sign-In via Email', 'bizpanda'); ?></td>
                        <td class="onp-how-no">-</td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('yes', 'bizpanda'); ?></strong></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title"><?php _e('Action "Twitter Follow"', 'bizpanda'); ?></td>
                        <td class="onp-how-no">-</td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('yes', 'bizpanda'); ?></strong></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title"><?php _e('Action "LinkedIn Follow"', 'bizpanda'); ?></td>
                        <td class="onp-how-no">-</td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('yes', 'bizpanda'); ?></strong></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title"><?php _e('Action "Subscribe to Youtube"', 'bizpanda'); ?></td>
                        <td class="onp-how-no">-</td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('yes', 'bizpanda'); ?></strong></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title"><strong><?php _e('Export Leads In CSV', 'bizpanda'); ?></strong></td>
                        <td class="onp-how-no">-</td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('yes', 'bizpanda'); ?></strong></td>
                    </tr>
                    
                    <tr class="onp-how-group-separator">
                      <td colspan="3"></td>   
                    </tr>
                    <tr class="onp-how-group">
                        <td class="onp-how-group-title"><i class="fa fa-adjust"></i> <?php _e('Overlap Modes', 'bizpanda'); ?></td>
                        <td class="onp-how-yes"><?php _e('Free', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><?php _e('Premium', 'bizpanda'); ?></td>
                    </tr>

                    <tr>
                        <td class="onp-how-title"><?php _e('Full', 'bizpanda'); ?></td>
                        <td class="onp-how-yes"><?php _e('yes', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><?php _e('yes', 'bizpanda'); ?></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title"><?php _e('Transparency', 'bizpanda'); ?></td>
                        <td class="onp-how-yes"><?php _e('yes', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><?php _e('yes', 'bizpanda'); ?></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title"><a href="#blurring"><?php _e('Blurring (new!)', 'bizpanda'); ?></a></td>
                        <td class="onp-how-no">-</td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('yes', 'bizpanda'); ?></strong></td>
                    </tr>   
                    
                    <tr class="onp-how-group-separator">
                      <td colspan="3"></td>   
                    </tr>
                    <tr class="onp-how-group">
                        <td class="onp-how-group-title"><i class="fa fa-picture-o"></i> <?php _e('Themes', 'bizpanda'); ?></td>
                        <td class="onp-how-yes"><?php _e('Free', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><?php _e('Premium', 'bizpanda'); ?></td>
                    </tr>

                    <tr>
                        <td class="onp-how-title onp-how-group-in-group"><?php _e('The "Secrets" Theme', 'bizpanda'); ?></td>
                        <td class="onp-how-yes"><?php _e('yes', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><?php _e('yes', 'bizpanda'); ?></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title onp-how-group-in-group"><a href="#extra-themes"><?php _e('Theme "Flat" (new!)', 'bizpanda'); ?></a></td>
                        <td class="onp-how-no">-</td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('yes', 'bizpanda'); ?></strong></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title onp-how-group-in-group"><a href="#extra-themes"><?php _e('Theme "Dandyish"', 'bizpanda'); ?> </a></td>
                        <td class="onp-how-no">-</td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('yes', 'bizpanda'); ?></strong></td>
                    </tr>          
                    <tr>
                        <td class="onp-how-title onp-how-group-in-group"><a href="#extra-themes"><?php _e('Theme "Glass"', 'bizpanda'); ?></a></td>
                        <td class="onp-how-no">-</td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('yes', 'bizpanda'); ?></strong></td>
                    </tr>
                    <tr>
                        <td class="onp-how-title onp-how-group-in-group"><a href="#extra-themes"><?php _e('Theme "Friendly Giant"', 'bizpanda'); ?></a></td>
                        <td class="onp-how-no">-</td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('yes', 'bizpanda'); ?></strong></td>
                    </tr>          
                    <tr>
                        <td class="onp-how-title onp-how-group-in-group"><a href="#extra-themes"><?php _e('Theme "Dark Force"', 'bizpanda'); ?></a></td>
                        <td class="onp-how-no">-</td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('yes', 'bizpanda'); ?></strong></td>
                    </tr>
                    
                    <tr class="onp-how-group-separator">
                      <td colspan="3"></td>   
                    </tr>
                    <tr class="onp-how-group">
                        <td class="onp-how-group-title"><i class="fa fa-picture-o"></i> <?php _e('Services', 'bizpanda'); ?></td>
                        <td class="onp-how-yes"><?php _e('Free', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><?php _e('Premium', 'bizpanda'); ?></td>
                    </tr>

                    <tr>
                        <td class="onp-how-title onp-how-group-in-group"><a href="#updates"><?php _e('Updates', 'bizpanda'); ?></a></td>
                        <td class="onp-how-no"><?php _e('not guaranteed', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('primary updates', 'bizpanda'); ?></strong></td>
                    </tr>      
                    <tr>
                        <td class="onp-how-title"><a href="#support"><?php _e('Support', 'bizpanda'); ?></a></td>
                        <td class="onp-how-no"><?php _e('not guaranteed', 'bizpanda'); ?></td>
                        <td class="onp-how-yes onp-how-premium"><strong><?php _e('dedicated support', 'bizpanda'); ?></strong></td>
                    </tr>  
                </tbody>
            </table>
            
            <?php if ( !$alreadyActivated ) { ?>
            
            <div>
                <a class="button button-primary" id="activate-trial-btn" href="<?php echo onp_licensing_000_manager_link($this->plugin->pluginName, 'activateTrial', false ) ?>">
                    <i class="fa fa-star-o"></i>
                    <?php _e('Click Here To Activate Your Free Trial For 7 Days', 'bizpanda'); ?>
                    <i class="fa fa-star-o"></i>
                    <br />
                    <small><?php _e('(instant activation by one click)', 'bizpanda'); ?></small>
                </a>
            </div>
            
            <?php } else { ?>
            
            <div class='factory-bootstrap-000'>
                <a class="btn btn-gold" id="onp-sl-purchase-btn" href="<?php echo onp_sl_get_premium_url( 'go-premium' ) ?>">
                    <i class="fa fa-star"></i>
                    <?php _e('Purchase Social Locker Premium For $25 Only', 'bizpanda'); ?>
                    <i class="fa fa-star"></i>
                </a>
            </div>
            
            <?php } ?>

            <?php if ( !$alreadyActivated ) { ?>

            <p style="text-align: center; margin-top: 20px;">
                <a href="<?php echo onp_sl_get_premium_url( 'go-premium' ) ?>" style="color: #111;"><strong><?php _e('Or Buy The Social Locker Right Now For $25 Only', 'bizpanda'); ?></strong></a>
            </p>

            <?php } ?>
            
        </div>
                    
        <div class="onp-page-section" id="social-options">
            <h1>
                <i class="fa fa-star-o"></i> <?php _e('Drive More Traffic & Build Quality Followers', 'bizpanda'); ?>
            </h1>
            <p><?php _e('The premium version of the plugin provides 8 social buttons for all major social networks: Facebook, Twitter, Google, LinkedIn, YouTube, including the Twitter Follow button. You can use them together or separately for customized results.', 'bizpanda') ?></p>
            <p class='onp-img'>
                <img src='http://cconp.s3.amazonaws.com/bizpanda/social-options-a.png' />
            </p>
        </div> 

        <div class="onp-page-section" id="extra-options">
            <h1>
                <i class="fa fa-star-o"></i> <?php _e('Set How, When and For Whom Your Lockers Appear', 'bizpanda'); ?>
            </h1>
            
            <p><?php _e('Each website has its own unique audience. We know that a good business is an agile business. The premium version of Social Locker provides 8 additional options that allow you to configure the lockers flexibly to meet your needs.', 'bizpanda'); ?></p>

            <p class='onp-img'>
                <img src='http://cconp.s3.amazonaws.com/bizpanda/advanced-options.png' />
            </p>
            <div class="clearfix"></div>
        </div> 

        <div class="onp-page-section" id='blurring'>
            <h1>
                <i class="fa fa-star-o"></i> <?php _e('Create Highly Shareable Content Via The Blur Effect', 'bizpanda'); ?>
            </h1>
            <p><?php _e('The previous versions of the plugin allowed only to hide the locked content totally. But recently we have added the long-awaited option to overlap content and make it transparent or blurred.', 'bizpanda'); ?></p>
            <p class='onp-img'>
                <img src='http://cconp.s3.amazonaws.com/bizpanda/blur-effect.png' />
            </p>
            <p><?php _e('When we tested this feature on sites of some our customers, we were blown away how this feature attracts attention of the huge number of visitors. If people see and understand that they will get after unlocking, the plugin works more effectively.', 'bizpanda'); ?></p>
        </div> 

        <div class="onp-page-section" id='extra-themes'>
            <h1>
                <i class="fa fa-star-o"></i> <?php _e('5 Extra Stunning Themes For Your Lockers', 'bizpanda'); ?>
            </h1>
            <p>
                <p><?php _e('The premium version of Social Locker comes with 5 extra impressive, polished styles which create interest and attract attention (3 for the classic Social Locker and 2 for the Sign-In Locker). They are nicely animated and don\'t look obtrusive:', 'bizpanda'); ?></p>
            </p>
            <p class='onp-img'>
                <img src='http://cconp.s3.amazonaws.com/bizpanda/new-themes.png' />
            </p>
        </div> 

        <div class="onp-page-section" id='updates'>
            <h1>
                <i class="fa fa-star-o"></i> <?php _e('Get New Features & Updates Almost Every Week', 'bizpanda'); ?>
            </h1>
            <p><?php _e('We release about 3-4 updates each month, adding new features and fixing bugs. The Free version does not guarantee that you will get all the major updates. But if you upgrade to the Premium version, your copy of the plugin will be always up-to-date.', 'bizpanda'); ?></p>
        </div> 

        <div class="onp-page-section" id='support'>
            <h1>
                <i class="fa fa-star-o"></i> <?php _e('Guaranteed Support Within 24h', 'bizpanda'); ?>
            </h1>
            <p>
                <?php _e('All of our plugins come with free support. We care about your plugin after purchase just as much as you do. We want to make your life easier and make you happy about choosing our plugins.', 'bizpanda'); ?>
            </p>
            <p>
                <?php _e('Unfortunately we receive plenty of support requests every day and we cannot answer to all the users quickly. But for the users of the premium version (and the trial version), we guarantee to respond to every inquiry within 1 business day (typical response time is 3 hours).', 'bizpanda'); ?>
            </p>
        </div> 

        <?php if ( !$alreadyActivated ) { ?>

        <div class="onp-page-section">
            <div>
                <a class="button button-primary" id="activate-trial-btn" href="<?php echo onp_licensing_000_manager_link($this->plugin->pluginName, 'activateTrial', false ) ?>">
                    <i class="fa fa-star-o"></i>
                    <?php _e('Click Here To Activate Your Free Trial For 7 Days', 'bizpanda'); ?>
                    <i class="fa fa-star-o"></i>
                    <br />
                    <small><?php _e('(instant activation by one click)', 'bizpanda'); ?></small>
                </a>
            </div>
        </div> 

        <div class="onp-page-section">
            <p style="text-align: center;">
                <a href="<?php echo onp_sl_get_premium_url( 'go-premium' ) ?>" style="color: #111;"><strong><?php _e('Or Buy The Social Locker Right Now For $25 Only', 'bizpanda'); ?></strong></a>
            </p>
            <div class="onp-remark">
                <div class="onp-inner-wrap">
                    <p><?php _e('You can purchase the premium version at any time within your trial period or right now. After purchasing you will get a license key to unlock all the plugin features.', 'bizpanda'); ?></p>
                    <p><?php printf(__('<strong>To purchase the Social Locker</strong>, <a target="_blank" href="%s">click here</a> to visit the plugin page on CodeCanyon. Then click the "Purchase" button on the right sidebar.', 'bizpanda'), onp_sl_get_premium_url( 'go-premium' )); ?></p>
                </div>
            </div>
        </div> 

        <?php } else { ?>
        <div class="onp-page-section">
            <div class='factory-bootstrap-000'>
                <a class="btn btn-gold" id="onp-sl-purchase-btn" href="<?php echo onp_sl_get_premium_url( 'go-premium' ) ?>">
                    <i class="fa fa-star"></i>
		            <?php _e('Purchase Social Locker Premium For $25 Only', 'bizpanda'); ?>
                    <i class="fa fa-star"></i>
                </a>
            </div>
        </div> 
        <?php } ?>
                    
                </div>
            </div>    
        </div> 
        <?php
    }
}

FactoryPages000::register($sociallocker, 'OnpSL_PremiumPage');
/*@mix:place*/