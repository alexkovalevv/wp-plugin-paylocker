<?php

	/**
	 * Класс для работы с пользовательскими уведомлениями
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 13.06.2017
	 * @version 1.0
	 */
	class OnpPl_Notifications {

		/**
		 * Задание для крона. Проверяет каким пользователям нужно отравить уведомление,
		 * а также отправляет уведомление, если это необходимо.
		 */
		public function runShedule()
		{
			global $wpdb;

			if( !get_option('opanda_notify_subscribe_expire', false) ) {
				return;
			}

			$start = (int)trim(get_option('opanda_subscribe_expire_start'));
			$possibleCount = (int)trim(get_option('opanda_subscribe_expire_count'));
			$notifyInterval = (int)trim(get_option('opanda_subscribe_expire_interval'));

			if( empty($start) || empty($possibleCount) || empty($notifyInterval) ) {
				return;
			}

			$subscribes = $wpdb->get_results("
					SELECT sb.user_id,sb.locker_id, nt.notifications, nt.last_notification_time
					FROM {$wpdb->prefix}opanda_pl_subsribers sb
					LEFT JOIN {$wpdb->prefix}opanda_pl_notifications nt ON sb.user_id = nt.user_id and sb.locker_id = nt.locker_id
					WHERE sb.expired_end > UNIX_TIMESTAMP() and ((sb.expired_end - UNIX_TIMESTAMP())/86400) <= $start
			");

			foreach($subscribes as $subscribe) {

				$userInfo = get_userdata($subscribe->user_id);
				$receiver = $userInfo->user_email;

				if( is_null($subscribe->notifications) ) {
					$result = $wpdb->insert($wpdb->prefix . 'opanda_pl_notifications', array(
						'user_id' => $subscribe->user_id,
						'locker_id' => $subscribe->locker_id,
						'notifications' => 1,
						'last_notification_time' => time()
					), array('%d', '%d', '%d', '%d'));

					if( $result ) {
						$this->sendSubscribeExpire($receiver, $subscribe->user_id, $subscribe->locker_id);
					}
				} else if( (int)$subscribe->notifications < $possibleCount ) {

					if( $subscribe->last_notification_time < strtotime("-{$notifyInterval} days") ) {
						$notifyResult = $this->sendSubscribeExpire($receiver, $subscribe->user_id, $subscribe->locker_id);

						if( $notifyResult ) {
							$updateResult = $wpdb->update("{$wpdb->prefix}opanda_pl_notifications", array(
								'notifications' => (int)$subscribe->notifications + 1,
								'last_notification_time' => time()
							), array(
								'user_id' => $subscribe->user_id,
								'locker_id' => $subscribe->locker_id,
							), array('%d', '%d'), array('%d', '%d'));
						}
					}
				}
			}
		}

		protected function send($receiver, $from, $subject, $message, $replacements)
		{
			foreach($replacements as $name => $replacement) {
				$subject = str_replace('{' . $name . '}', $replacement, $subject);
				$message = str_replace('{' . $name . '}', $replacement, $message);
			}

			add_action('wp_mail_failed', 'OnpPl_Notifications::mailerLog', 10, 1);

			$headers = array();

			$headers[] = 'from: ' . get_bloginfo('site_name') . ' <' . $from . '>';
			$headers[] = 'content-type: text/html';

			$resultSend = wp_mail($receiver, $subject, $message, $headers);

			remove_action('wp_mail_failed', 'OnpPl_Notifications::mailerLog');

			return $resultSend;
		}

		protected function sendSubscribeExpire($receiver, $user_id, $locker_id)
		{
			$from = trim(get_option('opanda_subscribe_expire_email_from'));
			$subject = trim(get_option('opanda_subscribe_expire_email_subject'));
			$message = trim(get_option('opanda_subscribe_expire_email_body'));

			if( empty($locker_id) || empty($subject) || empty($message) ) {
				onp_pl_logging('send-mail-errors', __('[Ошибка]: Уведомление не может быть отправлено, так как не установлен один или несколько атрибутов locker_id, subject, message', 'plugin-paylocker'));

				return;
			}

			$replacements = array(
				'sitename' => get_bloginfo('name'),
				'siteurl' => get_bloginfo('url'),
				'subscribe_url' => admin_url('admin.php?page=begin_subscribe-paylocker&locker_id=' . $locker_id)
			);

			return $this->send($receiver, $from, $subject, $message, $replacements);
		}

		protected function mailerLog($mailer)
		{
			if( empty($mailer) || empty($mailer->errors) ) {
				onp_pl_logging('send-mail-errors', __('[Ошибка]: Неизвестная ошибка', 'plugin-paylocker'));

				return;
			}

			onp_pl_logging('send-mail-errors', $mailer->errors);
		}
	}