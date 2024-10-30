<?php

/*
|--------------------------------------------------------------------------
| getting all coin ids from database
|--------------------------------------------------------------------------
*/
function ccwca_get_all_coinids(){
  $DB = new ccwca_Coins;
  $coin_data = $DB->get_coins(array('number' => -1));
  if (is_array($coin_data) && isset($coin_data)) {
      $coin_data =  ccwca_objectToArray($coin_data);
      $coins = array();
      foreach ($coin_data as $coin) {
          $coins[$coin['coin_id']] = $coin['name'];
      }
      return $coins;
  } else {
      return false;
  }
  
}

/*
|--------------------------------------------------------------------------
| USD conversion helper function
|--------------------------------------------------------------------------
 */	
  function ccwca_usd_conversions($currency){
    $conversions= get_transient('ccwca_usd_conversions');
		if( empty($conversions) || $conversions==="" ) {
		   	 	$request = wp_remote_get('https://api-beta.coinexchangeprice.com/v1/exchange-rates');
		  	if( is_wp_error( $request ) ) {
					return false;
				}
				$currency_ids = array("USD","AUD","BRL","CAD","CZK","DKK", "EUR","HKD","HUF","ILS","INR" ,"JPY" ,"MYR","MXN", "NOK","NZD","PHP" ,"PLN","GBP" ,"SEK","CHF","TWD","THB" ,"TRY","CNY","KRW","RUB", "SGD","CLP", "IDR","PKR", "ZAR" );
				$body = wp_remote_retrieve_body( $request );
				$conversion_data= json_decode( $body );
				if(isset($conversion_data->rates)){
				$conversion_data=(array)$conversion_data->rates;
				}else{
					$conversion_data=array();
				}
				if(is_array($conversion_data) && count($conversion_data)>0) {
					foreach($conversion_data as $key=> $currency_price){
							if(in_array($key,$currency_ids)){
								$conversions[$key]=$currency_price;
							}
					}	
				uksort($conversions, function($key1, $key2) use ($currency_ids) {
				    return (array_search($key1, $currency_ids) > array_search($key2, $currency_ids));
				});
			
				set_transient('ccwca_usd_conversions',$conversions, 3 * HOUR_IN_SECONDS);
				}
			}

			if($currency=="all"){
				
				return $conversions;

			}else{
				if(isset($conversions[$currency])){
					return $conversions[$currency];
				}
			}
  }


  /**
   * Check if provided $value is empty or not.
   * Return $default if $value is empty
   */
  function ccwca_set_default_if_empty($value,$default='N/A'){
      return $value?$value:$default;
  }

  function ccwca_format_number($n){
      $formatted = $n;
      if($n <= -1){
        $formatted = number_format($n, 2, '.', ',');
      }else if($n < 0.50){
        $formatted = number_format($n, 6, '.', ',');
      }else{
        $formatted = number_format($n, 2, '.', ',');
      }
      return $formatted;
  }

  // object to array conversion 
  function ccwca_objectToArray($d)
  {
    if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
      $d = get_object_vars($d);
    }

    if (is_array($d)) {
            /*
       * Return array converted to object
       * Using __FUNCTION__ (Magic constant)
       * for recursive call
       */
      return array_map(__FUNCTION__, $d);
    } else {
            // Return array
      return $d;
    }
  }
	/*
	Added meta boxes for shortcode
	*/

	function ccwca_register_meta_box()
	{
	    add_meta_box( 'ccwca-shortcode', 'Shortcode','ccwca_p_shortcode_meta', 'ccwca', 'side', 'high' );
	}

  /*
    Plugin Shortcode meta section 
  */
	function ccwca_p_shortcode_meta()
		{ 
	    $id = get_the_ID();
	    $dynamic_attr='';
	    _e(' <p>Paste this shortcode in anywhere (page/post)</p>','ccwca'); 
  
	   $element_type = get_post_meta( $id, 'pp_type', true );
	   $dynamic_attr.="[ccwca id=\"{$id}\"";
	   $dynamic_attr.=']';
	    ?>
	    <input type="text" class="regular-small" name="my_meta_box_text" id="my_meta_box_text" value="<?php echo htmlentities($dynamic_attr) ;?>" readonly/>
      
      <?php 

	}


	function ccwca_add_meta_boxes( $post){
		 add_meta_box(
                'ccwca-feedback-section',
                __( 'Hopefully you are happy with our COOL crypto widget plugin','ccwca'),
                'ccwca_right_section',
                'ccwca',
                'side',
                'low'
            );
	}

  /*
    Admin notice for plugin feedback
  */
	function ccwca_right_section($post, $callback){
        global $post;
        $pro_add=''; 
        $pro_add .=
        __('May I ask you to give it a 5-star rating on WordPress.org. This will help to spread its popularity and to make this plugin a better one.  ','ccwca').
        '<br/><br/><a href="https://wordpress.org/support/plugin/cryptocurrency-price-ticker-widget/reviews/#new-post" class="button button-primary" target="_blank">Submit Review ★★★★★</a>
        <hr>
         <div>
        <h3>Crypto Widgets Pro Features:-</h3>
      <ol style="list-style:disc;"><li> You can display real time live price changes. - <a href="http://cryptowidgetpro.coolplugins.net/list-widget/#live-changes-demo" target="_blank">DEMO</a></li> 
		<li>  Create widgets for 1500+ crypto coins in pro version while free version only supports top 50 crypto coins.</li> 
		<li>  Create historical price charts & tradingview candlestick charts. - <a href="http://cryptowidgetpro.coolplugins.net/coin-price-chart/" target="_blank">DEMO</a></li> 
		<li>  You can create beautiful price label and crypto price card designs.</li> 
    <li>  Display latest crypto news feed from popular websites. - <a href="http://cryptowidgetpro.coolplugins.net/news-feed/" target="_blank">DEMO</a></li> 
		<li>  Display market cap and volume of virtual crypto coins.</li> 
		<li>  32+ fiat currencies support - USD, GBP, EUR, INR, JPY, CNY, ILS, KRW, RUB, DKK, PLN, AUD, BRL, MXN, SEK, CAD, HKD, MYR, SGD, CHF, HUF, NOK, THB, CLP, IDR, NZD, TRY, PHP, TWD, CZK, PKR, ZAR.</li> 
		<li><a target="_blank" href="http://cryptowidgetpro.coolplugins.net/">'.__('VIEW ALL DEMOS','ccwca').'</a></li>

		</ol>
		<a class="button button-secondary" target="_blank" href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-price-ticker-widget-pro-wordpress-plugin%2F21269050s">'.__('Buy Now','ccwca').' ($29)</a>
		</div>';
        echo $pro_add ;

    }


  // currencies symbol
  function ccwca_currency_symbol($name)
  {
    $cc = strtoupper($name);
    $currency = array(
      "USD" => "&#36;", //U.S. Dollar
	  "CLP" => "&#36;", //CLP Dollar
	  "SGD" => "S&#36;", //Singapur dollar
      "AUD" => "&#36;", //Australian Dollar
      "BRL" => "R&#36;", //Brazilian Real
      "CAD" => "C&#36;", //Canadian Dollar
      "CZK" => "K&#269;", //Czech Koruna
      "DKK" => "kr", //Danish Krone
      "EUR" => "&euro;", //Euro
      "HKD" => "&#36", //Hong Kong Dollar
      "HUF" => "Ft", //Hungarian Forint
      "ILS" => "&#x20aa;", //Israeli New Sheqel
      "INR" => "&#8377;", //Indian Rupee
	  "IDR" => "Rp", //Indian Rupee
	  "KRW" => "&#8361;", //WON
	  "CNY" => "&#165;", //CNY
      "JPY" => "&yen;", //Japanese Yen 
      "MYR" => "RM", //Malaysian Ringgit 
      "MXN" => "&#36;", //Mexican Peso
      "NOK" => "kr", //Norwegian Krone
      "NZD" => "&#36;", //New Zealand Dollar
      "PHP" => "&#x20b1;", //Philippine Peso
      "PLN" => "&#122;&#322;",//Polish Zloty
      "GBP" => "&pound;", //Pound Sterling
      "SEK" => "kr", //Swedish Krona
      "CHF" => "Fr", //Swiss Franc
      "TWD" => "NT&#36;", //Taiwan New Dollar 
	  "PKR" => "Rs", //Rs 
      "THB" => "&#3647;", //Thai Baht
      "TRY" => "&#8378;", //Turkish Lira
	  "ZAR" => "R", //zar
	  "RUB" => "&#8381;" //rub
    );

    if (array_key_exists($cc, $currency)) {
      return $currency[$cc];
    }
  }


  	// Register Custom Post Type of Crypto Widget
  function ccwca_post_type()
  {

    $labels = array(
      'name' => _x('Cryptocurrency Widgets - CoinGecko API', 'Post Type General Name', 'ccwca'),
      'singular_name' => _x('Cryptocurrency Widgets - CoinGecko API', 'Post Type Singular Name', 'ccwca'),
      'menu_name' => __('Crypto Widgets', 'ccwca'),
      'name_admin_bar' => __('Post Type', 'ccwca'),
      'archives' => __('Item Archives', 'ccwca'),
      'attributes' => __('Item Attributes', 'ccwca'),
      'parent_item_colon' => __('Parent Item:', 'ccwca'),
      'all_items' => __('All Shortcodes', 'ccwca'),
      'add_new_item' => __('Add New Shortcode', 'ccwca'),
      'add_new' => __('Add New', 'ccwca'),
      'new_item' => __('New Item', 'ccwca'),
      'edit_item' => __('Edit Item', 'ccwca'),
      'update_item' => __('Update Item', 'ccwca'),
      'view_item' => __('View Item', 'ccwca'),
      'view_items' => __('View Items', 'ccwca'),
      'search_items' => __('Search Item', 'ccwca'),
      'not_found' => __('Not found', 'ccwca'),
      'not_found_in_trash' => __('Not found in Trash', 'ccwca'),
      'featured_image' => __('Featured Image', 'ccwca'),
      'set_featured_image' => __('Set featured image', 'ccwca'),
      'remove_featured_image' => __('Remove featured image', 'ccwca'),
      'use_featured_image' => __('Use as featured image', 'ccwca'),
      'insert_into_item' => __('Insert into item', 'ccwca'),
      'uploaded_to_this_item' => __('Uploaded to this item', 'ccwca'),
      'items_list' => __('Items list', 'ccwca'),
      'items_list_navigation' => __('Items list navigation', 'ccwca'),
      'filter_items_list' => __('Filter items list', 'ccwca'),
    );
    $args = array(
      'label' => __('CryptoCurrency Price Widget', 'ccwca'),
      'description' => __('Post Type Description', 'ccwca'),
      'labels' => $labels,
      'supports' => array('title'),
      'taxonomies' => array(''),
      'hierarchical' => false,
      'public' => false,  // it's not public, it shouldn't have it's own permalink, and so on
      'show_ui' => true,
      'show_in_nav_menus' => false,  // you shouldn't be able to add it to menus
      'menu_position' => 5,
      'show_in_admin_bar' => true,
      'show_in_nav_menus' => true,
      'can_export' => true,
      'has_archive' => false,  // it shouldn't have archive page
      'rewrite' => false,  // it shouldn't have rewrite rules
      'exclude_from_search' => true,
      'publicly_queryable' => true,
      'menu_icon' => CCWCA_URL.'/assets/ccwca-icon.png',
      'capability_type' => 'page',
    );
    register_post_type('ccwca', $args);

  }

  /**
   * Define the metabox and field configurations.
   */
  function cmb2_ccwca_metaboxes()
  {

    // Start with an underscore to hide fields from custom fields list
    $prefix = 'ccwca_';
    $currencies_arr = array(
      'USD' => 'USD',
      'GBP' => 'GBP',
      'EUR' => 'EUR',
      'INR' => 'INR',
      'JPY' => 'JPY',
      'CNY' => 'CNY',
      'ILS' => 'ILS',
      'KRW' => 'KRW',
      'RUB' => 'RUB',
      'DKK' => 'DKK',
      'PLN' => 'PLN',
      'AUD' => 'AUD',
      'BRL' => 'BRL',
      'MXN' => 'MXN',
      'SEK' => 'SEK',
      'CAD' => 'CAD',
      'HKD' => 'HKD',
      'MYR' => 'MYR',
      'SGD' => 'SGD',
      'CHF' => 'CHF',
      'HUF' => 'HUF',
      'NOK' => 'NOK',
      'THB' => 'THB',
      'CLP' => 'CLP',
      'IDR' => 'IDR',
      'NZD' => 'NZD',
      'TRY' => 'TRY',
      'PHP' => 'PHP',
      'TWD' => 'TWD',
      'CZK' => 'CZK',
      'PKR' => 'PKR',
      'ZAR' => 'ZAR',
    );
    /**
     * Initiate the metabox
     */
    $cmb = new_cmb2_box(array(
      'id' => 'generate_shortcode_gicko',
      'title' => __('Settings', 'cmb2'),
      'object_types' => array('ccwca'), // Post type
      'context' => 'normal',
      'priority' => 'high',
      'show_names' => true, // Show field names on the left
        // 'cmb_styles' => false, // false to disable the CMB stylesheet
        // 'closed'     => true, // Keep the metabox closed by default
    ));
    $cmb->add_field(array(
      'name' => 'Type<span style="color:red;">*</span>',
      'id' => 'type',
      'type' => 'select',
      'default' => 'table-widget',
      'options' => array(
        'table-widget' => __('Advanced Table', 'cmb2'),
        'list-widget' => __('Simple List', 'cmb2'),
        'ticker' => __('Ticker / Marquee', 'cmb2'),
        'multi-currency-tab' => __('Multi Currency Tabs', 'cmb2'),
        'price-label' => __('Price Label', 'cmb2'),
      ),
    ));

    $cmb->add_field(
      array(
        'name'    =>  'Display CryptoCurrencies<span style="color:red;">*</span>',
        'id'      =>  'display_currencies_for_table',
        'type'    =>  'select',
        'options' =>  array(
          'top-10'    =>'Top 10',
          'top-50'    =>'Top 50',
          'top-100'   =>'Top 100',
          'all'       =>'All'
        )
      ,
      'attributes' => array(
        'data-conditional-id' => 'type',
        'data-conditional-value' => json_encode(array('table-widget')),
      )
    ));

    $cmb->add_field(
      array(
        'name'    =>  'Records Per Page',
        'id'      =>  'pagination_for_table',
        'type'    =>  'select',
        'options' =>  array(
              '10'   =>'10',
              '25'   =>'25',
              '50'   =>'50',
              '100'  =>'100'
        )
      ,
      'attributes' => array(
        'data-conditional-id' => 'type',
        'data-conditional-value' => json_encode(array('table-widget')),
      )
    ));

    $cmb->add_field(array(
      'name' => 'Display 24 Hour High? (Optional)',
      'desc' => 'Select if you want to display 24 hour high',
      'id' => 'display_24h_high',
      'type' => 'checkbox',
      'default' => ccwca_set_checkbox_default_for_new_post( true ),
      'attributes' => array(
        'data-conditional-id' => 'type',
        'data-conditional-value' => json_encode(array('table-widget')),
      )
    ));

    $cmb->add_field(array(
      'name' => 'Display 24 Hour low? (Optional)',
      'desc' => 'Select if you want to display 24 hour low',
      'id' => 'display_24h_low',
      'type' => 'checkbox',
      'default' => ccwca_set_checkbox_default_for_new_post( true ),
      'attributes' => array(
        'data-conditional-id' => 'type',
        'data-conditional-value' => json_encode(array('table-widget')),
      )
    ));

    $cmb->add_field(array(
      'name' => 'Display CryptoCurrencies<span style="color:red;">*</span>',
      'id' => 'display_currencies',
      'desc' => 'Select CryptoCurrencies (Press CTRL key to select multiple)',
      'type' => 'pw_multiselect',
      'options' => 
        ccwca_get_all_coinids()
      ,
      'attributes' => array(
          'required' => true,
          'data-conditional-id'=>'type',
          'data-conditional-value' => json_encode(array('price-label', 'list-widget','multi-currency-tab', 'ticker')),
      )
    ));

   //select currency
    $cmb->add_field(array(
      'name' => 'Select Currency',
      'desc' => '',
      'id' => 'currency',
      'type' => 'select',
      'show_option_none' => false,
      'default' => 'custom',
      'options' => $currencies_arr,
      'default' => 'USD',
      'attributes' => array(
        'data-conditional-id' => 'type',
        'data-conditional-value' => json_encode(array('price-label', 'list-widget', 'ticker','table-widget')),
      )
    ));

    $cmb->add_field(array(
      'name' => 'Display 24 Hours changes? (Optional)',
      'desc' => 'Select if you want to display Currency changes in price',
      'default' => ccwca_set_checkbox_default_for_new_post( true ),
      'id' => 'display_changes',
      'type' => 'checkbox',
      'attributes' => array(
        // 'required' => true,        
       'data-conditional-id' => 'type',
       'data-conditional-value' =>  json_encode(array('price-label', 'list-widget','multi-currency-tab', 'ticker'))
      )
  ));

    $cmb->add_field(array(
      'name' => 'Where Do You Want to Display Ticker? (Optional)',
      'desc' => '<br>Select the option where you want to display ticker.<span class="warning">Important: Do not add shortcode in a page if Header/Footer position is selected.</span>',
      'id' => 'ticker_position',
      'type' => 'radio_inline',
      'options' => array(
        'header' => __('Header', 'cmb2'),
        'footer' => __('Footer', 'cmb2'),
        'shortcode' => __('Anywhere', 'cmb2'),
      ),
      'default' => 'shortcode',

      'attributes' => array(
         // 'required' => true,        
        'data-conditional-id' => 'type',
        'data-conditional-value' => 'ticker',
      )

    ));

    $cmb->add_field(array(
      'name' => 'Ticker Position(Top)',
      'desc' => 'Specify Top Margin (in px) - Only For Header Ticker',
      'id' => 'header_ticker_position',
      'type' => 'text',
      'default' => '33',
      'attributes' => array(
         // 'required' => true,        
        'data-conditional-id' => 'type',
        'data-conditional-value' => 'ticker',
      )
    ));

    $cmb->add_field(array(
      'name' => 'Speed of Ticker',
      'desc' => 'Enter the speed of ticker (between 20-140)',
      'id' => 'ticker_speed',
      'type' => 'text',
      'default' => '30',
      'attributes' => array(
         // 'required' => true,        
        'data-conditional-id' => 'type',
        'data-conditional-value' => 'ticker',
      )
    ));

    $cmb->add_field(array(
      'name' => 'Background Color',
      'desc' => 'Select background color',
      'id' => 'back_color',
      'type' => 'colorpicker',
      'default' => '#eee',
	  'attributes' => array(
        'data-conditional-id' => 'type',
        'data-conditional-value' => json_encode(array('multi-currency-tab', 'list-widget', 'ticker')),
      )
    ));

    $cmb->add_field(array(
      'name' => 'Font Color',
      'desc' => 'Select font color',
      'id' => 'font_color',
      'type' => 'colorpicker',
      'default' => '#000',
      'attributes' => array(
        'data-conditional-id' => 'type',
        'data-conditional-value' => json_encode(array('multi-currency-tab', 'list-widget', 'ticker')),
      )
    ));

    $cmb->add_field(array(
      'name' => 'Custom CSS',
      'desc' => 'Enter custom CSS',
      'id' => 'custom_css',
      'type' => 'textarea',

    ));
    
    $cmb->add_field(array(
      'name' => 'Show API Credits',
      'desc' => 'Link back or a mention of ‘<strong>Powered by CoinGecko API</strong>’ would be appreciated!',
      'id' => 'ccwc_coingecko_credits',
      'default' => ccwca_set_checkbox_default_for_new_post( true ),
      'type' => 'checkbox',
      'attributes' => array(
        // 'required' => true,        
       'data-conditional-id' => 'type',
       'data-conditional-value' =>  json_encode(array('ticker','price-label', 'list-widget','multi-currency-tab', 'table-widget'))
      )

    ));

    $cmb->add_field(array(
      'name' => '',
      'desc' => '
  <h3>Check Our Cool Premium Crypto Plugins - Now Create Website Similar Like CoinGecko.com<br/></h3>
  <div class="ccwca_pro">
  <a target="_blank" href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-price-ticker-widget-pro-wordpress-plugin%2F21269050"><img style="max-width:100%;" src="https://res.cloudinary.com/coolplugins/image/upload/v1530694709/crypto-exchanges-plugin/banner-crypto-widget-pro.png"></a>
  </div><hr/>
    <div class="ccwca_pro">
   <a target="_blank" href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcoin-market-cap-prices-wordpress-cryptocurrency-plugin%2F21429844"><img style="max-width:100%;"src="https://res.cloudinary.com/coolplugins/image/upload/v1530695051/crypto-exchanges-plugin/banner-coinmarketcap.png"></a>
   </div><hr/>
    <div class="ccwca_pro">
   <a target="_blank" href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-exchanges-list-pro-wordpress-plugin%2F22098669"><img style="max-width:100%;"src="https://res.cloudinary.com/coolplugins/image/upload/v1530694721/crypto-exchanges-plugin/banner-crypto-exchanges.png"></a> </div>',
      'type' => 'title',
      'id' => 'ccwca_title'
    ));
    // Add other metaboxes as needed

  }

  function ccwca_set_checkbox_default_for_new_post( $default ) {
    return isset( $_GET['post'] ) ? '' : ( $default ? (string) $default : '' );
  }