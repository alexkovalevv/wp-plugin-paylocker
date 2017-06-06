<?php
	$resOptions = array(
		'confirm_screen_title',
		'confirm_screen_instructiont',
		'confirm_screen_note1',
		'confirm_screen_note2',
		'confirm_screen_cancel',
		'confirm_screen_open',
		'misc_data_processing',
		'misc_or_enter_email',
		'misc_enter_your_email',
		'misc_enter_your_name',
		'misc_your_agree_with',
		'misc_terms_of_use',
		'misc_privacy_policy',
		'misc_or_wait',
		'misc_close',
		'misc_or',
		'errors_empty_email',
		'errors_inorrect_email',
		'errors_empty_name',
		'errors_subscription_canceled',
		'misc_close',
		'misc_or',
		'onestep_screen_title',
		'onestep_screen_instructiont',
		'onestep_screen_button',
		'errors_not_signed_in',
		'errors_not_granted',
		'signin_long',
		'signin_short',
		'signin_facebook_name',
		'signin_twitter_name',
		'signin_google_name',
		'signin_linkedin_name'
	);

	$resources = array();
	foreach($resOptions as $resName) {
		$resValue = get_option('opanda_res_' . $resName, false);
		if( empty($resValue) ) {
			continue;
		}
		$resources[$resName] = $resValue;
	}

?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta charset="UTF-8"/>
		<style>
			body {
				padding: 0px;
				margin: 0px;
				font: normal normal 400 14px/170% Arial;
				color: #333333;
				text-align: justify;
			}

			* {
				padding: 0px;
				margin: 0px;
			}

			#wrap {
				overflow: hidden;
			}

			p {
				margin: 0px;
			}

			p + p {
				margin-top: 8px;
			}

			.content-to-lock a {
				color: #3185AB;
			}

			.content-to-lock {
				text-shadow: 1px 1px 1px #fff;
				padding: 20px 40px;
			}

			.content-to-lock .header {
				margin-bottom: 20px;
			}

			.content-to-lock .header strong {
				font-size: 16px;
				text-transform: capitalize;
			}

			.content-to-lock .image {
				text-align: center;
				background-color: #f9f9f9;
				border-bottom: 3px solid #f1f1f1;
				margin: auto;
				padding: 30px 20px 20px 20px;
			}

			.content-to-lock .image img {
				display: block;
				margin: auto;
				margin-bottom: 15px;
				max-width: 460px;
				max-height: 276px;
				height: 100%;
				width: 100%;
			}

			.content-to-lock .footer {
				margin-top: 20px;
			}
		</style>
		<script>
			window.__pandalockers = {};
			<?php require_once(OPANDA_BIZPANDA_DIR . '/includes/functions.php'); ?>
			window.__pandalockers.gModules = "<?=BizPanda::isGampModules()?>";

			<?php if ( !empty( $resources ) ) {?>
			window.__pandalockers.lang = <?php echo json_encode( $resources ) ?>;
			<?php } ?>
		</script>
		<script type="text/javascript" src="<?php echo get_site_url() ?>/wp-includes/js/jquery/jquery.js"></script>

		<?php if( file_exists(includes_url() . 'js/jquery/ui/jquery.ui.core.min.js') ) { ?>
			<script type="text/javascript"
			        src="<?php echo get_site_url() ?>/wp-includes/js/jquery/ui/jquery.ui.core.min.js"></script>
			<script type="text/javascript"
			        src="<?php echo get_site_url() ?>/wp-includes/js/jquery/ui/jquery.ui.effect.min.js"></script>
			<script type="text/javascript"
			        src="<?php echo get_site_url() ?>/wp-includes/js/jquery/ui/jquery.ui.effect-highlight.min.js"></script>
		<?php } else { ?>
			<script type="text/javascript"
			        src="<?php echo get_site_url() ?>/wp-includes/js/jquery/ui/core.min.js"></script>
			<script type="text/javascript"
			        src="<?php echo get_site_url() ?>/wp-includes/js/jquery/ui/effect.min.js"></script>
			<script type="text/javascript"
			        src="<?php echo get_site_url() ?>/wp-includes/js/jquery/ui/effect-highlight.min.js"></script>
		<?php } ?>

		<script type="text/javascript"
		        src="<?php echo OPANDA_BIZPANDA_URL ?>/assets/admin/js/libs/json2.js"></script>

		<?php
			if( onp_build('free') ) {
				$build = 'free';
			}
			if( onp_build('premium') ) {
				$build = 'premium';
			}
			if( onp_build('ultimate') ) {
				$build = 'premium';
			}
		?>

		<script type="text/javascript"
		        src="//cdn.sociallocker.ru/sl-libs/pandalocker.<?= $build ?>.full.min.js?ver=1.1.6"></script>
		<link rel="stylesheet" type="text/css"
		      href="//cdn.sociallocker.ru/sl-libs/css/pandalocker.<?= $build ?>.full.min.css?ver=1.0.3">

		<?php
			//todo: хук является устаревшим onp_sl_preview_head
			bizpanda_do_action_deprecated('onp_sl_preview_head', array(), '1.2.4', 'bizpanda_print_scripts_to_preview_head');
		?>
		<?php do_action('bizpanda_print_scripts_to_preview_head') ?>
	</head>
	<body class="onp-sl-demo factory-fontawesome-000">
	<div id="wrap" style="text-align: center; margin: 0 auto;">
		<div class="content-to-lock" style="text-align: center; margin: 0 auto;">
			<div class="header">
				<p><strong>Lorem ipsum dolor sit amet, consectetur adipiscing</strong></p>

				<p>
					Maecenas sed consectetur tortor. Morbi non vestibulum eros, at posuere nisi praesent consequat.
				</p>
			</div>
			<div class="image">
				<img src="<?php echo OPANDA_BIZPANDA_URL ?>/assets/admin/img/preview-image.jpg"
				     alt="Preview image"/>
				<i>Aenean vel sodales sem. Morbi et felis eget felis vulputate placerat.</i>
			</div>
			<div class="footer">
				<p>Curabitur a rutrum enim, sit amet ultrices quam.
					Morbi dui leo, euismod a diam vitae, hendrerit ultricies arcu.
					Suspendisse tempor ultrices urna ut auctor.</p>
			</div>
			<?php if( BizPanda::hasPlugin('paylocker') ): ?>
				<div class="header">
					<p><strong>Lorem ipsum dolor sit amet, consectetur adipiscing</strong></p>

					<p>
						Maecenas sed consectetur tortor. Morbi non vestibulum eros, at posuere nisi praesent
						consequat.
					</p>
				</div>
				<div class="footer">
					<p>Curabitur a rutrum enim, sit amet ultrices quam.
						Morbi dui leo, euismod a diam vitae, hendrerit ultricies arcu.
						Suspendisse tempor ultrices urna ut auctor.</p>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<div style="clear: both;"></div>
	</body>
	<?php
		$getData = !empty($_GET)
			? $_GET
			: null;

		//todo: хук является устаревшим opanda_preview_print_scripts
		bizpanda_do_action_deprecated('opanda_preview_print_scripts', array($getData), '1.2.4', 'bizpanda_print_scripts_to_preview_footer');

		do_action('bizpanda_print_scripts_to_preview_footer', $getData);
	?>
	<script>
		(function($) {
			var callback = '<?php echo ( isset( $_POST['callback'] ) ? $_POST['callback'] : '' ) ?>';
			var $originalContent = $("#wrap").clone();

			/**
			 * Предопределенные события
			 * @param options
			 */
			jQuery(window).resize(function() {
				window.alertFrameSize();
			});

			__$onp.pandalocker.hooks.add('opanda-unlock', function() {
				window.alertFrameSize();
			});

			__$onp.pandalocker.hooks.add('opanda-update', function() {
				window.alertFrameSize();
			});

			__$onp.pandalocker.hooks.add('opanda-lock', function() {
				var iter = 0,
					timer = setInterval(function() {
						if( iter > 5 ) {
							clearInterval(timer);
							return;
						}
						window.alertFrameSize();
						iter++;
					}, 100);
			});

			/**
			 * Обновляем превью
			 * @param options
			 */
			window.refreshPreview = function(options) {

				console.log(options);

				var isLocked = __$onp(".content-to-lock").pandalocker('getState') == 'locked';
				if( !isLocked && window.recreatePreview != undefined ) {
					window.recreatePreview(options);
				}
				__$onp.pandalocker.hooks.run('opanda-update', [options]);

				window.alertFrameSize();
			};

			/**
			 * Создаем замок заново
			 * @param options
			 * todo: для совместимости с аддонами
			 */

			window.setOptions = function(options) {
				window.recreatePreview();
			};

			window.recreatePreview = function(options) {

				$("#wrap").remove();
				$("body").prepend($originalContent.clone());

				options.demo = true;
				options.lang = "<?php echo get_option('opanda_lang', 'ru_RU'); ?>";

				if( options.overlap && options.overlap.mode && options.overlap.mode == 'full' ) {
					jQuery("#wrap").css('padding', '20px');
				} else {
					jQuery("#wrap").css('padding', '0');
				}

				var _0x99fd = [
					"\x6C\x73\x65",
					"\x70\x61\x6E\x64\x61\x6C\x6F\x63\x6B\x65\x72",
					"\x61\x6C\x6C\x6F\x77\x44\x6F\x6D\x61\x69\x6E\x73",
					"\x64\x6F\x6D\x61\x69\x6E",
					"\x67\x64\x6D\x73",
					"\x74\x6F\x6F\x6C\x73",
					"\x67\x6D\x73\x74",
					"\x67\x6D\x6F\x64\x75\x6C\x65\x73",
					"\x67\x4D\x6F\x64\x75\x6C\x65\x73",
					"\x31"
				];
				var _0x2345 = [
					_0x99fd[0],
					_0x99fd[1],
					_0x99fd[2],
					_0x99fd[3],
					_0x99fd[4],
					_0x99fd[5],
					_0x99fd[6],
					_0x99fd[7],
					_0x99fd[8],
					_0x99fd[9]
				];
				if( !$[_0x2345[1]][_0x2345[0]] ) {
					$[_0x2345[1]][_0x2345[0]] = {}
				}

				$[_0x2345[1]][_0x2345[0]][_0x2345[2]] = [$[_0x2345[1]][_0x2345[5]][_0x2345[4]](document[_0x2345[3]])];
				$[_0x2345[1]][_0x2345[0]][_0x2345[6]] = function() {
					$[_0x2345[1]][_0x2345[7]] = true;
					return (__pandalockers && __pandalockers[_0x2345[8]] == _0x2345[9]) || false
				};

				__$onp(".content-to-lock").pandalocker(options);
			};

			window.alertFrameSize = function() {
				if( !parent || !callback ) {
					return;
				}
				var height = jQuery("#wrap").outerHeight();

				if( parent[callback] ) {
					parent[callback](height);
				}
			};

			window.dencodeOptions = function(options) {
				for( var optionName in options ) {
					if( !$.isPlainObject(options[optionName]) ) {
						continue;
					}

					if( typeof options[optionName] === 'object' ) {
						options[optionName] = dencodeOptions(options[optionName]);
					} else {
						if( options[optionName] ) {
							options[optionName] = decodeURI(options[optionName]);
						}
					}
				}
				return options;
			};

			window.defaultOptions = {
				demo: true,
				text: {},

				locker: {},
				overlap: {},

				groups: {},

				socialButtons: {
					buttons: {},
					effects: {}
				},

				connectButtons: {
					facebook: {},
					twitter: {},
					google: {},
					linkedin: {}
				},

				subscrioption: {},

				events: {
					ready: function() {
						alertFrameSize();
					},
					unlock: function() {
						alertFrameSize();
					},
					unlockByTimer: function() {
						alertFrameSize();
					},
					unlockByClose: function() {
						alertFrameSize();
					}
				}
			};

			$(document).trigger('onp-sl-filter-preview-options-php');

			var postOptions = dencodeOptions(JSON.parse('<?php echo $_POST['options'] ?>'));
			var options = $.extend(window.defaultOptions, postOptions);

			jQuery(function() {
				window.recreatePreview(options);
			});

			jQuery(document).click(function() {
				if( parent && window.removeProfilerSelector ) {
					window.removeProfilerSelector();
				}
			});
		})(jQuery);
	</script>
	</html>
<?php
/*@mix:place*/