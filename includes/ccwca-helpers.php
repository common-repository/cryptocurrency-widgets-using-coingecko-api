<?php

/*
 |------------------------------------------------
 | Fetching data through API and save in database
 |------------------------------------------------
*/

function ccwca_save_coins_data(){

    $cache  =   get_transient('ccwca-coin-updates');
    
    /**
     * Avoid running if cache found
     */
    if( $cache != false ){
        return;
    }

   
    $DB         = new ccwca_Coins;
    $coins      = array();
    $api_url   = 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&order=market_cap_desc&per_page=250';

    $request = wp_remote_get($api_url,array('timeout' => 120));
    if (is_wp_error($request)) {
        return false; // Bail early
    }
    $body = wp_remote_retrieve_body($request);
    $coins = json_decode($body);
    $response = array();
    $coin_data = array();
    
    if( isset($coins) && $coins!="" && is_array($coins)){
        foreach($coins as $coin){
            $response['coin_id'] = $coin->id;
            $response['symbol']  = strtoupper($coin->symbol);
            $response['name']    = $coin->name;
            $response['rank']    = $coin->market_cap_rank;
            $response['price']   = ccwca_set_default_if_empty($coin->current_price,0.00);
            $response['price_change_24h'] = ccwca_set_default_if_empty($coin->price_change_24h,0);
            $response['price_change_percentage_24h'] = ccwca_set_default_if_empty($coin->price_change_percentage_24h,0);
            $response['image'] =    ccwca_set_default_if_empty($coin->image);
            $response['market_cap'] =     ccwca_set_default_if_empty($coin->market_cap,0);
            $response['market_cap_change_24h'] =    ccwca_set_default_if_empty($coin->market_cap_change_24h,0);
            $response['market_cap_change_percentage_24h'] =     ccwca_set_default_if_empty($coin->market_cap_change_percentage_24h);
            $response['total_volume'] =         ccwca_set_default_if_empty($coin->total_volume);
            $response['high_24h'] =             ccwca_set_default_if_empty($coin->high_24h);
            $response['low_24h'] =             ccwca_set_default_if_empty($coin->low_24h);
            $response['circulating_supply'] =    ccwca_set_default_if_empty($coin->circulating_supply);
            $response['total_supply'] =          ccwca_set_default_if_empty($coin->total_supply);
            $response['update_at'] =             $coin->last_updated;
            $coin_data[] = $response;
        }

            $DB->ccwca_insert($coin_data);
            set_transient('ccwca-coin-updates','CRON', 5 * MINUTE_IN_SECONDS );

    }
        
}

/*
|--------------------------------------------------------------------------
| getting all coins details
|--------------------------------------------------------------------------
 */		
function ccwca_get_all_coins_details($coin_id){
    $DB = new ccwca_Coins;
    $coin_data =$DB->get_coins(array('coin_id'=> $coin_id));
if(is_array($coin_data)&& isset($coin_data)){
     $coin_data= ccwca_objectToArray($coin_data);
        return $coin_data;
    }else{
        return false;
    }

}