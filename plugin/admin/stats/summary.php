<?php

	class OPanda_Paylocker_Summary_StatsTable extends OPanda_StatsTable {

		public function __construct($screen, $data)
		{
			parent::__construct($screen, $data);
			$this->updateData();
		}

		public function updateData()
		{
			global $wpdb;

			$posts = array();

			if( isset($this->data['data']) ) {
				foreach($this->data['data'] as $key => $value) {
					$posts[$value['id']] = &$this->data['data'][$key];
				}
			}

			if( empty($posts) ) {
				return;
			}

			$sql = "SELECT COUNT(*) as counts, post_id, table_payment_type as payment
                FROM {$wpdb->prefix}opanda_pl_transactions ";

			$sql .= "WHERE ";
			$sql .= "post_id IN (" . implode(',', array_keys($posts)) . ") and ";
			$sql .= "transaction_status='finish' GROUP BY post_id, table_payment_type ORDER BY counts DESC";

			$results = $wpdb->get_results($sql);

			if( empty($results) ) {
				return;
			}

			foreach($results as $result) {
				if( $result->payment == 'purchase' ) {
					$posts[$result->post_id]['purchase'] = $result->counts;
				} else {
					$posts[$result->post_id]['subscribe'] = $result->counts;
				}
			}

			function cmp($a, $b)
			{
				if( $a['purchase'] == $b['purchase'] ) {
					return 0;
				}

				return ($a['purchase'] > $b['purchase'])
					? -1
					: 1;
			}

			usort($this->data['data'], "cmp");
		}

		public function getColumns()
		{

			return array(
				'index' => array(
					'title' => ''
				),
				'title' => array(
					'title' => __('Страницы', 'plugin-paylocker')
				),
				'impress' => array(
					'title' => __('Просмотров', 'plugin-paylocker'),
					'cssClass' => 'opanda-col-number'
				),
				'purchase' => array(
					'title' => __('Покупок', 'plugin-paylocker'),
					'hint' => __('Число покупок записей.', 'plugin-paylocker'),
					'highlight' => true,
					'cssClass' => 'opanda-col-number'
				),
				'subscribe' => array(
					'title' => __('Платных подписок', 'plugin-paylocker'),
					'hint' => __('Число платных подписок.', 'plugin-paylocker'),
					'cssClass' => 'opanda-col-number'
				)
			);
		}
	}

	class OPanda_Paylocker_Summary_StatsChart extends OPanda_StatsChart {

		public $type = 'line';

		public function __construct($screen, $data, $options)
		{
			parent::__construct($screen, $data, $options);
			$this->updateData();
		}

		public function updateData()
		{
			global $wpdb;

			// building and executeing a sql query

			$extraWhere = '';
			if( isset($this->options['postId']) && !empty($this->options['postId']) ) {
				$extraWhere .= ' AND tr.post_id=' . (int)$this->options['postId'];
			}
			if( isset($this->options['itemId']) && !empty($this->options['itemId']) ) {
				$extraWhere .= ' AND tr.locker_id=' . (int)$this->options['itemId'];
			}

			$rangeStart = $this->options['rangeStart'];
			$rangeEnd = $this->options['rangeEnd'];

			$sql = "SELECT
						DATE_FORMAT(FROM_UNIXTIME(tr.transaction_begin), '%Y-%m-%d') AS aggregate_date,
                    	tr.table_payment_type AS metric_name,
                    	COUNT(tr.table_payment_type) AS metric_value
                	FROM {$wpdb->prefix}opanda_pl_transactions tr ";

			$sql .= "WHERE transaction_status='finish' AND (tr.transaction_begin BETWEEN '" . $rangeStart . "' AND '" . $rangeEnd . "') ";

			$sql .= $extraWhere;

			$sql .= " GROUP BY aggregate_date, metric_name";

			$rawData = $wpdb->get_results($sql, ARRAY_A);

			// extracting metric names stored in the database &
			// grouping the same metrics data per day

			$metricNames = array();
			$data = array();

			foreach($rawData as $row) {
				$metricName = $row['metric_name'];
				$metricValue = $row['metric_value'];

				if( !in_array($metricName, $metricNames) ) {
					$metricNames[] = $metricName;
				}

				$timestamp = strtotime($row['aggregate_date']);
				$data[$timestamp][$metricName] = $metricValue;
			}

			// normalizing data by adding zero value for skipped dates

			$resultData = array();

			$currentDate = $rangeStart;
			while( $currentDate <= $rangeEnd ) {

				$phpdate = getdate($currentDate);
				$resultData[$currentDate] = array();

				$resultData[$currentDate]['day'] = $phpdate['mday'];
				$resultData[$currentDate]['mon'] = $phpdate['mon'] - 1;
				$resultData[$currentDate]['year'] = $phpdate['year'];
				$resultData[$currentDate]['timestamp'] = $currentDate;

				foreach($metricNames as $metricName) {

					if( !isset($data[$currentDate][$metricName]) ) {
						$resultData[$currentDate][$metricName] = 0;
					} else {
						$resultData[$currentDate][$metricName] = $data[$currentDate][$metricName];
					}
				}

				$currentDate = strtotime("+1 days", $currentDate);
			}

			/*@mix:place*/

			foreach($this->data as $key => $dataValue) {
				if( isset($resultData[$key]) ) {
					$this->data[$key] = array_merge($dataValue, $resultData[$key]);
				}
			}
		}


		public function getSelectors()
		{
			return null;
		}

		public function getFields()
		{

			return array(
				'aggregate_date' => array(
					'title' => __('Дата', 'plugin-paylocker')
				),
				'purchase' => array(
					'title' => __('Покупок записей', 'plugin-paylocker'),
					'color' => '#ffc107'
				),
				'subscribe' => array(
					'title' => __('Платных подписок', 'plugin-paylocker'),
					'color' => '#d6bef7'
				)
			);
		}
	}