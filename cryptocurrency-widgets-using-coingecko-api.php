<?php
/**
 * Plugin Name:Cryptocurrency Widgets Using CoinGecko API
 * Description:2000+ crypto coins widgets, price ticker, labels using CoinGecko API – Best cryptocurrency widgets pack plugin for WordPress.
 
 * Author:Cool Plugins
 * Author URI:https://coolplugins.net/
 * Plugin URI:https://cryptowidget.coolplugins.net/
 * Version: 1.2
 * License: GPL2
 * Text Domain:ccwca
 * Domain Path: languages
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'CCWCA_VERSION' ) ) {
	return;
}

/*
	Defined constent for later use
*/
define( 'CCWCA_VERSION', '1.2' );
define( 'CCWCA_FILE', __FILE__ );
define( 'CCWCA_PATH', plugin_dir_path( CCWCA_FILE ) );
define( 'CCWCA_URL', plugin_dir_url( CCWCA_FILE ) );


/**
 * Class CCWCA_Widget
 */
final class CryptoCurrency_Widget {

	/**
	 * Plugin instance.
	 *
	 * @var CryptoCurrency_Widget
	 * @access private
	 */
	private static $instance = null;

	/**
	 * Get plugin instance.
	 *
	 * @return CryptoCurrency_Widget
	 * @static
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @access private
	 */
	private function __construct() {
		$this->ccwca_includes();
		$this->ccwca_installation_date();
	
		register_activation_hook( CCWCA_FILE, array( $this, 'ccwca_activate' ) );
		register_deactivation_hook( CCWCA_FILE, array( $this, 'ccwca_deactivate' ) );
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		//main plugin shortcode for list widget
		add_shortcode( 'ccwca', array( $this, 'ccwca_shortcode' ));
		
		add_action( 'save_post', array( $this,'ccwca_save_shortcode'),10, 3 );

		// ajax call for datatable server processing
		add_action('wp_ajax_ccwca_get_coins_list', array($this, 'ccwca_get_coins_list'));
		add_action('wp_ajax_nopriv_ccwca_get_coins_list', array($this, 'ccwca_get_coins_list'));

		//creating posttype for plugin settings panel
		add_action( 'init','ccwca_post_type');
		// integrating cmb2 metaboxes in post type
		add_action( 'cmb2_admin_init','cmb2_ccwca_metaboxes');
		add_action( 'add_meta_boxes','ccwca_register_meta_box');
		
		// check coin market cap plugin is activated.
		add_action('admin_init', array($this, 'ccwca_check_cmc_activated'));

		add_action( 'wp_footer', array($this,'ccwca_ticker_in_footer') );
		add_action( 'wp_footer', array($this,'ccwca_enable_ticker') );

		if(is_admin()){
		add_action( 'admin_init',array($this,'ccwca_check_installation_time'));
			add_action( 'admin_init',array($this,'ccwca_spare_me'), 5 );
			add_action('admin_enqueue_scripts', array($this,'ccwca_load_scripts'),100);
			add_action('admin_head-edit.php', array($this, 'ccwca_custom_btn'));
			add_action( 'add_meta_boxes_ ccwca','ccwca_add_meta_boxes');

			add_filter( 'manage_ccwca_posts_columns',array($this,'ccwca_set_custom_edit_columns'));
			add_action( 'manage_ccwca_posts_custom_column' ,array($this,'ccwca_custom_column'), 10, 2 );
		}
		
		//add_filter('cron_schedules', array($this, 'ccwca_cron_schedules')); 
		// add_action('ccwca_coins_autoupdate', array($this, 'ccwca_cron_coins_autoupdater'));
		add_action('init', array($this, 'ccwca_cron_coins_autoupdater'));

	}

	/**
	 * Cron status schedule(s)
	 */
	function ccwca_cron_schedules($schedules)
	{
		// 5 minute schedule for grabing all coins 
		if (!isset($schedules["5min"])) {
			$schedules["5min"] = array(
				'interval' => 5 * 60,
				'display' => __('Once every 5 minutes')
			);
		}
		return $schedules;
	}

	/**
	 * initialize cron : MUST USE ON PLUGIN ACTIVATION
	 */
	public function ccwca_cron_job_init(){
		if (!wp_next_scheduled('ccwca_coins_autoupdate')) {
			wp_schedule_event(time(), '5min', 'ccwca_coins_autoupdate');
		}
	}

	public function ccwca_cron_coins_autoupdater(){
		//update coins data;
		$response = ccwca_save_coins_data();
	}

	/**
	 * server side processing ajax callback
	 */
	function ccwca_get_coins_list(){
		require(CCWCA_PATH.'includes/ccwca-serverside-processing.php');
		ccwca_get_ajax_data();
		wp_die();
	}

	/**
	 * Load plugin function files here.
	 */
	public function ccwca_includes() {

		/**
		 * Get the bootstrap!
		 */
		if ($this->ccwca_get_post_type_page() == "ccwca") {
			require_once __DIR__ . '/cmb2/init.php';
			require_once __DIR__ . '/cmb2/cmb2-conditionals.php';
			require_once __DIR__ . '/cmb2/cmb-field-select2/cmb-field-select2.php';
		}

		//loading required functions
		require_once __DIR__ . '/includes/ccwca-coins-db.php';
		require_once __DIR__ . '/includes/ccwca-helpers.php';
		require_once __DIR__ . '/includes/ccwca-functions.php';
		require_once __DIR__ . '/includes/ccwca-widget.php';
	}

// loading required assets according to the type of widget
function ccwca_enqueue_assets($type, $post_id){
		
	wp_enqueue_style('ccwca-bootstrap', CCWCA_URL.'assets/css/bootstrap.min.css');
	wp_enqueue_style('ccwca-custom-icons', CCWCA_URL.'assets/css/ccwca-icons.css');
	// ccwca main styles file
	wp_enqueue_style('ccwca-styles', CCWCA_URL. 'assets/css/ccwca-styles.css', array(), null, null, 'all');

	// loading Scripts for ticker widget
	if($type=="ticker"){
	$ticker_id = "ccwca-ticker-widget-" . $post_id;
	wp_enqueue_script('ccwca_marque_js', CCWCA_URL.'assets/js/jquery.marquee.min.js', array('jquery'), null, true);
	wp_add_inline_script('ccwca_marque_js', 'jQuery(document).ready(function($){
		$(".ccwca-ticker-cont #'.$ticker_id.'").each(function(index){
			var tickerCon=$(this);
			var ispeed=Number(tickerCon.data("tickerspeed"));
			$(this).marquee({
				allowCss3Support: true,
				speed: ispeed-ispeed * 60/100,
				pauseOnHover: true,
				gap: 0,
				delayBeforeStart: 0,
				direction: "left",
				duplicated: true,
			startVisible: true,
			});
		});

	});' );

	} else if($type=="multi-currency-tab"){
		wp_enqueue_script('ccwca_script', CCWCA_URL. 'assets/js/ccwca-script.js',array('jquery'));
	}else if( $type == "table-widget"){
		wp_enqueue_script('ccwca-datatable', CCWCA_URL. 'assets/js/jquery.dataTables.min.js',array('jquery'));
		wp_enqueue_script('ccwca-headFixer', CCWCA_URL. 'assets/js/tableHeadFixer.js');
		wp_enqueue_style('ccwca-custom-datatable-style', CCWCA_URL. 'assets/css/ccwca-custom-datatable.css');
		wp_enqueue_script('ccwca-table-script', CCWCA_URL. 'assets/js/ccwca-table-widget.js',array('jquery'));
		wp_localize_script(
			'ccwca-table-script',
			'ccwca_js_objects',
			array('ajax_url' => admin_url('admin-ajax.php'))
		);
		wp_enqueue_script('ccwca-numeral', CCWCA_URL. 'assets/js/numeral.min.js',array('jquery'));
		wp_enqueue_script('ccwca-table-sort', CCWCA_URL. 'assets/js/tablesort.min.js',array('jquery'));
	}
	
		
}
	
	/**
	 * Crypto Widget Main Shortcode
	 */

	function ccwca_shortcode( $atts, $content = null ) {
	$atts = shortcode_atts( array(
		'id'  => '',
		'class' => ''
	), $atts, 'ccwca' );

	$post_id=$atts['id'];

	/*
	 *	Return if post status is anything other than 'publish'
	 */
	if( get_post_status( $post_id ) != "publish" ){
		return;
	}

	$cron_available = get_transient('ccwca-coin-updates');

	if( $cron_available === false ){
		ccwca_save_coins_data();
	}
	// Grab the metadata from the database
	$type = get_post_meta($post_id,'type', true );
	$currency = get_post_meta($post_id, 'currency', true);
	$show_credit = get_post_meta($post_id, 'ccwc_coingecko_credits', true);
	$credit_html	=	'<div class="ccwca-credits"><a href="https://www.coingecko.com" target="_blank">Powered by CoinGecko API</a></div>';
	$fiat_currency= $currency ? $currency :"USD";
	$ticker_position = get_post_meta($post_id,'ticker_position', true );
    $header_ticker_position = get_post_meta($post_id,'header_ticker_position', true );
	$ticker_speed = get_post_meta($post_id,'ticker_speed', true ) ;
	$t_speed=$ticker_speed ?$ticker_speed:15;
	$display_currencies = get_post_meta($post_id,'display_currencies', true );
	if($display_currencies==false){
		$display_currencies=array();
	}
	$datatable_currencies	= get_post_meta($post_id,'display_currencies_for_table', true);
	$datatable_pagination	= get_post_meta($post_id,'pagination_for_table',true);
	$display_24h_high		= get_post_meta($post_id,'display_24h_high',true);
	$display_24h_low		= get_post_meta($post_id,'display_24h_low',true);
	
	$output='';$cls='';$crypto_html='';
	$display_changes = get_post_meta($post_id,'display_changes', true );
    $back_color = get_post_meta($post_id,'back_color', true );
	$font_color = get_post_meta($post_id,'font_color', true );
	$custom_css = get_post_meta($post_id,'custom_css', true );
	$id = "ccwca-ticker" . $post_id . rand(1, 20);
	$is_cmc_enabled = get_option('cmc-dynamic-links');
	// Initialize Titan for cmc links
		if (class_exists('TitanFramework')) {
			$cmc_titan = TitanFramework::getInstance('cmc_single_settings');

			$cmc_slug = $cmc_titan->getOption('single-page-slug');

			if (empty($cmc_slug)) {
				$cmc_slug = 'currencies';
			}
		} else {
			$cmc_slug = 'currencies';
		}

	$this->ccwca_enqueue_assets($type, $post_id);
	
	/* dynamic styles */
	$dynamic_styles="";
	$styles='';
	$dynamic_styles_list="";
	$dynamic_styles_multicurrency="";
	$bg_color=!empty($back_color)? "background-color:".$back_color.";":"background-color:#fff;";
	$bg_coloronly=!empty($back_color)? ":".$back_color."d9;":":#ddd;";
	$fnt_color=!empty($font_color)? "color:".$font_color.";":"color:#000;";
	$fnt_coloronly=!empty($font_color)? ":".$font_color."57;":":#666;";
	$fnt_colorlight=!empty($font_color)? ":".$font_color."1F;":":#eee;";
	$ticker_top=!empty($header_ticker_position)? "top:".$header_ticker_position."px !important;":"top:0px !important;";

	if ($type == "ticker") {
		$id = "ccwca-ticker-widget-" . $post_id;	
		$dynamic_styles.=".tickercontainer #".$id."{".$bg_color."}
		.tickercontainer #".$id." span.name,
		.tickercontainer #".$id." .ccwca-credits a {".$fnt_color."}	
		.tickercontainer #".$id." span.coin_symbol {".$fnt_color."}			
		.tickercontainer #".$id." span.price {".$fnt_color."} .tickercontainer .price-value{".$fnt_color."}
		.ccwca-header-ticker-fixedbar{".$ticker_top."}";
	
	}
	else if ($type == "price-label") {
		$id = "ccwca-label-widget-" . $post_id;	
		$dynamic_styles .= "#".$id.".ccwca-price-label li a , #".$id.".ccwca-price-label li{" . $fnt_color . "}
		";

	}
	else if($type == "list-widget"){
			$id = "ccwca-list-widget-" . $post_id;	
			$dynamic_styles .="#".$id.".ccwca-widget{".$bg_color."}
			#".$id.".ccwca-widget .ccwca_table tr{".$bg_color.$fnt_color."}
			#".$id.".ccwca-widget .ccwca_table tr th, #".$id.".ccwca-widget .ccwca_table tr td,
			#".$id.".ccwca-widget .ccwca_table tr td a{".$fnt_color."}
			";
		
	}
	else if ($type == "multi-currency-tab") {
			$id = "ccwca-multicurrency-widget-" . $post_id;	
			$dynamic_styles .=".currency_tabs#".$id.",.currency_tabs#".$id." ul.multi-currency-tab li.active-tab{".$bg_color."}
			.currency_tabs#".$id." .mtab-content, .currency_tabs#".$id." ul.multi-currency-tab li, .currency_tabs#".$id." .mtab-content a{".$fnt_color."}";
	}

	 if($type=="multi-currency-tab"){
		  $usd_conversions=(array)ccwca_usd_conversions('all');
		}else{
		  $usd_conversions=array();
		}
	
		if( $type!='table-widget' ){
			$all_coin_data = ccwca_get_all_coins_details($display_currencies);
					
			if (is_array($all_coin_data) && count($all_coin_data)>0 ) {
				
				foreach ($all_coin_data as $currency) {
					// gather data from database
					if( $currency != false ){
					$coin = $currency;
					require(__DIR__ . '/includes/ccwca-generate-html.php');
					$crypto_html .= $coin_html;
					}
				}
			} else {
				$error = _e('You have not selected any currencies to display', 'ccwca');
				return $error.'<!-- Cryptocurrency Widget ID: '.$post_id.' !-->';
			}	
		}
		if ($type=="ticker"){
				$id = "ccwca-ticker-widget-" . $post_id;	
			 	if($ticker_position=="footer"||$ticker_position=="header"){
			 		$cls='ccwca-sticky-ticker';
			 		if($ticker_position=="footer"){
			 			$container_cls='ccwca-footer-ticker-fixedbar';
			 		}else{
			 			$container_cls='ccwca-header-ticker-fixedbar';
			 		}
			 		
			 	}else{
			 		$cls='ccwca-ticker-cont';
			 		$container_cls='';
			 	}

			$output .= '<div style="display:none" class="ccwca-container ccwca-ticker-cont '.$container_cls.'">';
			$output .= '<div  class="tickercontainer" style="height: auto; overflow: hidden;">
			';
			$output .= '<ul   data-tickerspeed="'.$t_speed.'" id="'.$id.'">';
			$output .= $crypto_html;
			if( $show_credit ){
				$output .= '<li ="ccwca-ticker-credit">'.$credit_html.'</li>';
			}
			$output	.=	'</ul></div></div>';

	}else if($type == "price-label"){
			$id = "ccwca-label-widget-".$post_id;	
			$output .='<div id="'.$id.'" class="ccwca-container ccwca-price-label"><ul class="lbl-wrapper">';
			$output .= $crypto_html;
			$output .= '</ul></div>';
			if( $show_credit ){
				$output .= $credit_html;
			}
	 
		}else if($type=="list-widget"){
			$cls='ccwca-widget';
			$id="ccwca-list-widget-".$post_id;	
			$output .= '<div id="'.$id.'" class="'.$cls.'"><table class="ccwca_table" style="border:none!important;"><thead>
			<th>'.__('Name','ccwca').'</th>
			<th>'.__('Price','ccwca').'</th>';
			if($display_changes){
			$output .='<th>'.__('24H (%)','ccwca').'</th>';
				}
			$output .='</thead><tbody>';
			$output .= $crypto_html;
			$output .= '</tbody></table></div>';
			
			if( $show_credit ){
				$output .= $credit_html;
			}
		
      }else if($type=="multi-currency-tab"){
				$id = "ccwca-multicurrency-widget-" . $post_id;	
				$output .= '<div class="currency_tabs" id="'.$id.'">';
      			$output .= '<ul class="multi-currency-tab">
      			<li data-currency="usd" class="active-tab">'.__("USD","ccwca").'</li>
      			<li data-currency="eur">'.__("EUR","ccwca").'</li>
      			<li data-currency="gbp">'.__("GPB","ccwca").'</li>
      			<li data-currency="aud">'.__("AUD","ccwca").'</li>
      			<li data-currency="jpy">'.__("JPY","ccwca").'</li>
      			</ul>';
				$output .= '<div><ul class="multi-currency-tab-content">';
      			$output .= $crypto_html;
      			$output .= '</ul></div></div>';
				if( $show_credit ){
					$output .= $credit_html;
				}
	}else if( $type == "table-widget"){
		$cls='ccwca-coinslist_wrapper';
		$preloader_url = CCWCA_URL . 'assets/chart-loading.svg';
		$ccwca_prev_coins= __('Previous','cmc');
		$ccwca_next_coins= __('Next','cmc');
		$coin_loading_lbl= __('Loading...','cmc');
		$ccwca_no_data= __('No Coin Found','cmc');

			$id="ccwca-coinslist_wrapper";	
			$output .= '<div id="'.$id.'" class="'.$cls.'"><table id="ccwca-datatable-'.$post_id.'" class="display ccwca_table_widget table-striped table-bordered no-footer" data-currency-type="'.$fiat_currency.'" data-next-coins="'.$ccwca_next_coins.'" data-loadinglbl="'.$coin_loading_lbl.'" data-prev-coins="'.$ccwca_prev_coins.'" data-dynamic-link="'.$is_cmc_enabled.'" data-currency-slug="'.$cmc_slug.'" data-required-currencies="'.$datatable_currencies.'" data-zero-records="'.$ccwca_no_data.'" data-pagination="'.$datatable_pagination.'" data-currency-symbol="'.ccwca_currency_symbol($fiat_currency).'" data-currency-rate="'.ccwca_usd_conversions($fiat_currency).'" style="border:none!important;">
			<thead data-preloader="'.$preloader_url.'">
			<th data-classes="desktop ccwca_coin_rank" data-index="rank">'.__('#','ccwca').'</th>
			<th data-classes="desktop ccwca_name" data-index="name">'.__('Name','ccwca').'</th>
			<th data-classes="desktop ccwca_coin_price" data-index="price">'.__('Price','ccwca').'</th>
			<th data-classes="desktop ccwca_coin_change24h" data-index="change_percentage_24h">'.__('Changes 24h','ccwca').'</th>
			<th data-classes="desktop ccwca_coin_market_cap" data-index="market_cap">'.__('Market CAP','ccwca').'</th>';

			if( $display_24h_high ){
				$output .='<th data-classes="ccwca_coin_high24h" data-index="high_24h">'.__('24H High','ccwca').'</th>';
			}

			if( $display_24h_low ){
				$output .='<th  data-classes="ccwca_coin_low24h" data-index="low_24h">'.__('24H Low','ccwca').'</th>';
			}

			$output .='<th data-classes="ccwca_coin_total_volume" data-index="total_volume">'.__('Volume','ccwca').'</th>
			<th data-classes="ccwca_coin_supply" data-index="supply">'.__('Supply','ccwca').'</th>';
			
			$output .='</tr></thead><tbody>';
			$output .= '</tbody><tfoot>
					</tfoot></table>';

			if( $show_credit ){
				$output .= $credit_html;
			}

			$output .= '</div>';

	}
				$ccwcacss= $dynamic_styles . $custom_css;
			
		wp_add_inline_style('ccwca-styles', $ccwcacss);

		$ccwcav='<!-- Cryptocurrency Widgets Using CoinGecko API - Version:- '.CCWCA_VERSION.' By Cool Plugins (CoolPlugins.net) -->';	
			return  $ccwcav.$output;	
		 
	/*	}else{
			 return _e('There is something wrong with the server','ccwca');
		} */
	}
	
		/**
		 * Code you want to run when all other plugins loaded.
		 */
		public function init() {
			load_plugin_textdomain( 'ccwca', false, CCWCA_PATH . 'languages' );
		}

	/**
	 * Run when activate plugin.
	 */
	public  function ccwca_activate() {
		$coins_db = new ccwca_Coins;
		$coins_db->create_table();
		//$this->ccwca_cron_job_init();
		
		ccwca_save_coins_data();
	}

	/**
	 * Run when deactivate plugin.
	 */
	public static function ccwca_deactivate() {

		//remove cache
		delete_transient('ccwca-coin-updates');

		
		// remove table from database
		$coins_db = new ccwca_Coins;
		$coins_db->drop_table();
		
		//clear wp cron scheduling
		wp_clear_scheduled_hook('ccwca_coins_autoupdate');
	}

	/**
	 * Save shortcode when a post is saved.
	 *
	 * @param int $post_id The post ID.
	 * @param post $post The post object.
	 * @param bool $update Whether this is an existing post being updated or not.
	 */
	function ccwca_save_shortcode( $post_id, $post, $update ) {
		// Autosave, do nothing
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		        return;
		// AJAX? Not used here
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) 
		        return;
		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) )
		        return;
		// Return if it's a post revision
		if ( false !== wp_is_post_revision( $post_id ) )
		        return;
 
    	$post_type = get_post_type($post_id);

		// If this isn't a 'ccwca' post, don't update it.
		if ( "ccwca" != $post_type ) return;
			// - Update the post's metadata.
			if(isset($_POST['ticker_position'])&& in_array($_POST['ticker_position'],array('header','footer'))){
				update_option('ccwca-p-id',$post_id);
				update_option('ccwca-shortcode',"[ccwca id=".$post_id."]");
				}

			delete_transient( 'ccwca-coins' ); // Site Transient
	}

	/*
		Added ticker shortcode in footer hook for footer ticker
	*/

	function ccwca_ticker_in_footer(){
		 $id=get_option('ccwca-p-id');
		if($id){
				$ticker_position = get_post_meta($id,'ticker_position', true );
    			$type = get_post_meta($id,'type', true );
  		
    			if($type=="ticker"){
    			if($ticker_position=="header" || $ticker_position=="footer" ){
					 $shortcode=get_option('ccwca-shortcode');
					echo do_shortcode($shortcode);
				 }
				}
			}	
	}

	// Re-enable ticker after dom load
	function ccwca_enable_ticker(){
		wp_add_inline_script('ccwca_marque_js',
		'jQuery(document).ready(function($){
			$(".ccwca-ticker-cont").fadeIn();     
		});'
		,'before');
		
	}

	/*
	For ask for reviews code
	*/

	function ccwca_installation_date(){
		 $get_installation_time = strtotime("now");
   	 	  add_option('ccwca_activation_time', $get_installation_time ); 
	}	

	//check if review notice should be shown or not

 	function ccwca_check_installation_time() {
		$spare_me = get_option('ccwca_spare_me');
  		if(get_option('ccwca_spare_me')==false){
		  $install_date = get_option( 'ccwca_activation_time' );
	        $past_date = strtotime( '-1 days' );
	      if ( $past_date >= $install_date ) {
	     	 add_action( 'admin_notices', array($this,'ccwca_display_admin_notice'));
	     		}
	    }
	}

	/**
	* Display Admin Notice, asking for a review
	**/
	function ccwca_display_admin_notice() {
	    // wordpress global variable 
	    global $pagenow;
	//    if( $pagenow == 'index.php' ){
	        $dont_disturb = esc_url( get_admin_url() . '?ccwca_spare_me=1' );
	        $plugin_info = get_plugin_data( __FILE__ , true, true );
	        $reviewurl = esc_url( 'https://wordpress.org/support/plugin/cryptocurrency-widgets-using-coingecko-api/reviews/#new-post' );
			echo $html='<div class="ccwca-review wrap"><img style="width:90px;height:auto;" src="'.plugin_dir_url(__FILE__).'assets/crypto-widget.png" />
			<p>You have been using <b> '.$plugin_info['Name']. '</b> for a while. We hope you liked it ! Please give us a quick rating, it works as a boost for us to keep working on the plugin !<br/>
			<br/><a href="'.$reviewurl.'" class="button button-primary" target=
				"_blank">Rate Now! ★★★★★</a>
				<a href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-price-ticker-widget-pro-wordpress-plugin%2F21269050" class="button button-secondary" style="margin-left: 10px !important;" target="_blank"> Try Crypto Widgets Pro !</a>
				<a href="'.$dont_disturb.'" class="ccwca-review-done"> Already Done ☓</a></p></div>';
	       
	   // }
	}

 	 function ccwca_set_custom_edit_columns($columns) {
	   $columns['type'] = __( 'Widget Type', 'ccwca' );
	    $columns['shortcode'] = __( 'Shortcode', 'ccwca' );
	   return $columns;
	}

	function ccwca_custom_column( $column, $post_id ) {
	    switch ( $column ) {
            case 'type' :
              $type=get_post_meta( $post_id , 'type' , true );
            switch ($type){
                case "ticker":
                    _e('Ticker','ccpw');
                break;
                case "price-label":
                        _e('Price Label', 'ccpw');
                break;
                case "multi-currency-tab":
                        _e('Multi Currency Tabs', 'ccpw');
                break;
                case "table-widget":
                    _e('Table Widget','ccpw');
                break;
                default:
                    _e('List Widget','ccpw');
            }
               break;
           	 case 'shortcode' :
                echo '<code>[ccwca id="'.$post_id.'"]</code>';
                break;
        }
	}

	/*
	 check admin side post type page
	*/
	function ccwca_get_post_type_page() {
    global $post, $typenow, $current_screen;
 
	 if ( $post && $post->post_type ){
	        return $post->post_type;
	 }elseif( $typenow ){
	        return $typenow;
	  }elseif( $current_screen && $current_screen->post_type ){
	        return $current_screen->post_type;
	 }
	 elseif( isset( $_REQUEST['post_type'] ) ){
	        return sanitize_key( $_REQUEST['post_type'] );
	 }
	 elseif ( isset( $_REQUEST['post'] ) ) {
	   return get_post_type( $_REQUEST['post'] );
	 }
	  return null;
	}

	// remove the notice for the user if review already done or if the user does not want to
	function ccwca_spare_me(){    
	    if( isset( $_GET['ccwca_spare_me'] ) && !empty( $_GET['ccwca_spare_me'] ) ){
			$spare_me = $_GET['ccwca_spare_me'];
		
	        if( $spare_me == 1 ){
				update_option('ccwca_spare_me',true);
	        }
	    }
	}

	//check coin market cap plugin is activated. then enable links
	function ccwca_check_cmc_activated()
	{
		if (is_plugin_active('coin-market-cap/coin-market-cap.php') || class_exists('CoinMarketCap')) {
			update_option('cmc-dynamic-links', true);
		} else {
			update_option('cmc-dynamic-links', false);
		}
	}
	
	public function ccwca_custom_btn()
	{
		global $current_screen;

    // Not our post type, exit earlier
 		if ('ccwca' != $current_screen->post_type) {
			return;
		}

		?>
        <script type="text/javascript">
            jQuery(document).ready( function($)
            {
				$(".wrap").find('a.page-title-action').after("<a  id='ccwcaadd_premium' href='https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-price-ticker-widget-pro-wordpress-plugin%2F21269050' target='_blank' class='add-new-h2'>Add Premium Widgets</a>");
                
            });
        </script>
    <?php

	}

	function ccwca_load_scripts($hook) {
	//if( $hook != 'edit.php' && $hook != 'post.php' && $hook != 'post-new.php' ) {
	//	return;
		$posttype = get_current_screen()->post_type;
		if( isset($posttype) ){	// check if 'post' is available				
				wp_enqueue_style( 'ccwca-custom-styles', CCWCA_URL.'assets/css/ccwca-admin-styles.css');
				if( $posttype=='ccwca'){
					wp_dequeue_script('wp-color-picker-alpha');
				}
		}
	}
}	// end of class CryptoCurrency_Widget

function CryptoCurrency_Widget() {
	return CryptoCurrency_Widget::get_instance();
}

$GLOBALS['CryptoCurrency_Widget'] = CryptoCurrency_Widget();
