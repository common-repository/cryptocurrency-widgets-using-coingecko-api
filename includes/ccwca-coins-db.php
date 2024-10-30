<?php
/**
 * This file is responsible for all database realted functionality.
 */
class ccwca_Coins {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function __construct()
	{

		global $wpdb;

		$this->table_name = $wpdb->base_prefix . 'ccwca_cryptocurrencies';
		$this->primary_key = 'id';
		$this->version = '1.0';

	}

	/**
	 * Get columns and formats
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function get_columns()
	{
		
		return array(
			'coin_id' => '%d',
			'name' => '%s',
			'symbol' => '%s',
			'price' => '%f',
			'rank' => '%d',
			'price_change_percentage_24h' => '%f',
			'image' => '%s',
			'market_cap' => '%f',
			'total_volume' => '%f',
			'high_24h'	=>	'%f',
			'low_24h'	=> '%f',
			'price_change_24h' => '%f',
			'price_change_percentage_24h' => '%f',
			'market_cap_change_24h' => '%f',
			'market_cap_change_percentage_24h' => '%f',
			'circulating_supply' => '%s',
			'total_supply' => '%s'
		);
	}

	function ccwca_insert($coins_data){
		if(is_array($coins_data) && count($coins_data)>1){
		
		return $this->wp_insert_rows($coins_data,$this->table_name,true,'name');
		}
	} 
	/**
	 * Get default column values
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function get_column_defaults()
	{
		return array(
			'id' =>'',
			'coin_id' =>'',
			'name' => '',
			'symbol' => '',
			'price' => '',
			'price_change_percentage_24h ' => '',		
			'last_updated' => date('Y-m-d H:i:s'),
		);
	}

	public function coin_exists_by_id($coin_id)
	{

		global $wpdb;
		$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $this->table_name WHERE coin_id ='%s'", $coin_id));
		if ($count == 1) {
			return true;
		} else {
			return false;
		}

	}	
	/**
	 * Retrieve orders from the database
	 *
	 * @access  public
	 * @since   1.0
	 * @param   array $args
	 * @param   bool  $count  Return only the total number of results found (optional)
	 */
	public function get_coins($args = array(), $count = false)
	{

		global $wpdb;

		$defaults = array(
			'number' => 20,
			'offset' => 0,
			'id' =>'',
			'coin_id'=>'',
			'name'=>'',
			'status' => '',
			'email' => '',
			'orderby' => 'id',
			'order' => 'ASC',
		);

		$args = wp_parse_args($args, $defaults);

		if ($args['number'] < 1) {
			$args['number'] = 999999999999;
		}

		$where = '';

	// specific referrals
		if (!empty($args['id'])) {

			if (is_array($args['id'])) {
				$order_ids = implode(',', $args['id']);
			} else {
				$order_ids = intval($args['id']);
			}

			$where .= "WHERE `id` IN( {$order_ids} ) ";

		}

		if (!empty($args['coin_id'])) {

			if (empty($where)) {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}

			if (is_array($args['coin_id'])) {
				$where .= " `coin_id` IN('" . implode("','", $args['coin_id']) . "') ";
			} else {
				$where .= " `coin_id` = '" . $args['coin_id'] . "' ";
			}

		}


		$args['orderby'] = !array_key_exists($args['orderby'], $this->get_columns()) ? $this->primary_key : $args['orderby'];

		if ('total' === $args['orderby']) {
			$args['orderby'] = 'total+0';
		} else if ('subtotal' === $args['orderby']) {
			$args['orderby'] = 'subtotal+0';
		}

		$cache_key = (true === $count) ? md5('ccwca_coins_count' . serialize($args)) : md5('ccwca_coins_' . serialize($args));

		$results = wp_cache_get($cache_key, 'coins');

		if (false === $results) {

			if (true === $count) {

				$results = absint($wpdb->get_var("SELECT COUNT({$this->primary_key}) FROM {$this->table_name} {$where};"));

			} else {

				$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM {$this->table_name} {$where} ORDER BY {$args['orderby']} {$args['order']} LIMIT %d, %d;",
						absint($args['offset']),
						absint($args['number'])
					)
				);

			}

			wp_cache_set($cache_key, $results, 'coins', 3600);

		}

		return $results;

	}

	
	public function get_coins_listdata($args = array(), $count = false)
	{

		global $wpdb;

		$defaults = array(
			'number' => 20,
			'offset' => 0,
			'coin_id' => '',
			'name'=>'',
			'status' => '',
			'email' => '',
			'orderby' => 'id',
			'order' => 'ASC',
		);

		$args = wp_parse_args($args, $defaults);

		if ($args['number'] < 1) {
			$args['number'] = 999999999999;
		}

		$where = '';

	// specific referrals
		if (!empty($args['id'])) {

			if (is_array($args['id'])) {
				$order_ids = implode(',', $args['id']);
			} else {
				$order_ids = intval($args['id']);
			}

			$where .= "WHERE `id` IN( {$order_ids} ) ";

		}

		if (!empty($args['coin_id'])) {

			if (empty($where)) {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}

			if (is_array($args['coin_id'])) {
				$where .= " `coin_id` IN('" . implode("','", $args['coin_id']) . "') ";
			} else {
				$where .= " `coin_id` = '" . $args['coin_id'] . "' ";
			}

		}


		$args['orderby'] = !array_key_exists($args['orderby'], $this->get_columns()) ? $this->primary_key : $args['orderby'];

		if ('total' === $args['orderby']) {
			$args['orderby'] = 'total+0';
		} else if ('subtotal' === $args['orderby']) {
			$args['orderby'] = 'subtotal+0';
		}

		$cache_key = (true === $count) ? md5('ccwca_coins_list_count' . serialize($args)) : md5('ccwca_coins_list_' . serialize($args));

		$results = wp_cache_get($cache_key, 'coins');

		if (false === $results) {

			if (true === $count) {

				$results = absint($wpdb->get_var("SELECT COUNT({$this->primary_key}) FROM {$this->table_name} {$where};"));

			} else {

				$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT name,price,symbol,coin_id FROM {$this->table_name} {$where} ORDER BY {$args['orderby']} {$args['order']} LIMIT %d, %d;",
						absint($args['offset']),
						absint($args['number'])
					)
				);

			}

			wp_cache_set($cache_key, $results, 'coins', 3600);

		}

		return $results;

	}



	public function get_top_changers_coins($args = array(), $count = false)
	{

		global $wpdb;

		$defaults = array(
			'number' => 20,
			'offset' => 0,
			'coin_id' => '',
			'status' => '',
			'email' => '',
			'orderby' => 'id',
			'order' => 'ASC',
			'volume'=>50000
		);

		$args = wp_parse_args($args, $defaults);

		if ($args['number'] < 1) {
			$args['number'] = 999999999999;
		}

		$where = '';

	// specific referrals
		if (!empty($args['volume'])) {

			$where .= "WHERE `total_volume` >'" . $args['volume'] . "'";

		}

		if (!empty($args['coin_id'])) {

			if (empty($where)) {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}

			if (is_array($args['coin_id'])) {
				$where .= " `coin_id` IN('" . implode("','", $args['coin_id']) . "') ";
			} else {
				$where .= " `coin_id` = '" . $args['coin_id'] . "' ";
			}

		}

		$args['orderby'] = !array_key_exists($args['orderby'], $this->get_columns()) ? $this->primary_key : $args['orderby'];

		if ('total' === $args['orderby']) {
			$args['orderby'] = 'total+0';
		} else if ('subtotal' === $args['orderby']) {
			$args['orderby'] = 'subtotal+0';
		}

		$cache_key = (true === $count) ? md5('ccwca_top_c_coins_count' . serialize($args)) : md5('ccwca_top_c_coins_' . serialize($args));

		$results = wp_cache_get($cache_key, 'coins');

		if (false === $results) {
			$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM {$this->table_name} {$where} ORDER BY {$args['orderby']} {$args['order']} LIMIT %d, %d;",
						absint($args['offset']),
						absint($args['number'])
					)
				);
	
			wp_cache_set($cache_key, $results, 'coins', 3600);
	
		}

		return $results;

	}


	
	/**
	 *  A method for inserting multiple rows into the specified table
	 *  Updated to include the ability to Update existing rows by primary key
	 *  
	 *  Usage Example for insert: 
	 *
	 *  $insert_arrays = array();
	 *  foreach($assets as $asset) {
	 *  $time = current_time( 'mysql' );
	 *  $insert_arrays[] = array(
	 *  'type' => "multiple_row_insert",
	 *  'status' => 1,
	 *  'name'=>$asset,
	 *  'added_date' => $time,
	 *  'last_update' => $time);
	 *
	 *  }
	 *
	 *
	 *  wp_insert_rows($insert_arrays, $wpdb->tablename);
	 *
	 *  Usage Example for update:
	 *
	 *  wp_insert_rows($insert_arrays, $wpdb->tablename, true, "primary_column");
	 *
	 *
	 * @param array $row_arrays
	 * @param string $wp_table_name
	 * @param boolean $update
	 * @param string $primary_key
	 * @return false|int
	 *
	 */
function wp_insert_rows($row_arrays = array(), $wp_table_name, $update = false, $primary_key = null) {
	global $wpdb;
	$wp_table_name = esc_sql($wp_table_name);
	// Setup arrays for Actual Values, and Placeholders
	$values        = array();
	$place_holders = array();
	$query         = "";
	$query_columns = "";
	$floatCols=array('price','price_change_percentage_24h','market_cap','total_volume','high_24h','low_24h','price_change_24h','price_change_percentage_24h',
						'market_cap_change_24h','market_cap_change_percentage_24h','circulating_supply');
	$query .= "INSERT INTO `{$wp_table_name}` (";
	foreach ($row_arrays as $count => $row_array) {
		foreach ($row_array as $key => $value) {
			if ($count == 0) {
				if ($query_columns) {
					$query_columns .= ", " . $key . "";
				} else {
					$query_columns .= "" . $key . "";
				}
			}
			
			$values[] = $value;
			
			$symbol = "%s";
			if (is_numeric($value)) {
						$symbol = "%d";
				}
		
			if(in_array( $key,$floatCols)){
				$symbol = "%f";
			}
			if (isset($place_holders[$count])) {
				$place_holders[$count] .= ", '$symbol'";
			} else {
				$place_holders[$count] = "( '$symbol'";
			}
		}
		// mind closing the GAP
		$place_holders[$count] .= ")";
	}
	
	$query .= " $query_columns ) VALUES ";
	
	$query .= implode(', ', $place_holders);
	
	if ($update) {
		$update = " ON DUPLICATE KEY UPDATE $primary_key=VALUES( $primary_key ),";
		$cnt    = 0;
		foreach ($row_arrays[0] as $key => $value) {
			if ($cnt == 0) {
				$update .= "$key=VALUES($key)";
				$cnt = 1;
			} else {
				$update .= ", $key=VALUES($key)";
			}
		}
		$query .= $update;
	}

	$sql = $wpdb->prepare($query, $values);
	
	if ($wpdb->query($sql)) {
		return true;
	} else {
		return false;
	}
}

	/**
	 * Return the number of results found for a given query
	 *
	 * @param  array  $args
	 * @return int
	 */
	public function count($args = array())
	{
		return $this->get_coins($args, true);
	}

	/**
	 * Create the table
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function create_table()
	{

		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$sql = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		rank int(9),
		coin_id varchar(200) NOT NULL UNIQUE,
		name varchar(250) NOT NULL,
		symbol varchar(200) NOT NULL,
		image varchar(250),
		market_cap decimal(24,2),
		price decimal(24,6),
		total_volume decimal(24,10),
		high_24h decimal(24,10),
		low_24h decimal(24,10),
		price_change_24h decimal(24,10),
		price_change_percentage_24h decimal(24,10),
		market_cap_change_24h decimal(24,6),
		market_cap_change_percentage_24h decimal(24,10),
		circulating_supply varchar(250),
		total_supply varchar(250),
		update_at TIMESTAMP NOT NULL,
		last_updated TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
		PRIMARY KEY (id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta($sql);

		update_option($this->table_name . '_db_version', $this->version);
	}

	/**
	 * Drop database table
	 */
	public function drop_table(){
		global $wpdb;
		$wpdb->query("DROP TABLE IF EXISTS " . $this->table_name);
	}
}