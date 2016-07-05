<?php 

namespace Drupal\shop_spider;

use Drupal\shop_spider\ProductFeed;

class ProductFeedStorage implements ProductFeedStorageInterface{
	
	public static function import($pcOnlineId, $productType){
		//Insert/Update basic info
		db_merge('intel_product_feed')
			->key(array(
				'pconline_id'=>$pcOnlineId,
			))
			->fields(array(
				'product_type'=>$productType,
				'refresh'=>ProductFeed::INTEL_PRODUCT_FEED_REFRESH_DEFAULT,
				'checked'=>0,
				'queued'=>0,
				'modified'=>time(),
			))
			->execute();
	}
	
	public static function refresh($pcOnlineId){
		db_update('intel_product_feed')
			->fields(array(
				'refresh' => ProductFeed::INTEL_PRODUCT_FEED_NOT_REFRESH,
				'checked' => REQUEST_TIME,
				'modified' => time(),
			))
			->condition('pconline_id', $pcOnlineId, '=')
			->execute();
	}
	
	public static function expire(){
		//$intel_product_feed_clear = variable_get('intel_product_feed_clear', 9676800);
		
		// Remove all items that are older than flush item timer.
		//$age = REQUEST_TIME - $intel_product_feed_clear;
		$fids = db_query('SELECT fid FROM {intel_product_feed} WHERE refresh = :refresh AND checked < :checked', array(
				':refresh' => ProductFeed::INTEL_PRODUCT_FEED_NOT_REFRESH,
				':checked' => REQUEST_TIME,
		))->fetchCol();
		// 	dpm(REQUEST_TIME);
		// 	dpm($age);
		// 	dpm($fids);
		if ($fids) {
			db_delete('intel_product_feed')
			->condition('fid', $fids, 'IN')
			->execute();
			
			watchdog('Intel Shop Product feed', 'Product feeds {%fids} expired.',
				array(
					'%fids' => json_encode($fids))
			);
		}
	}
	
	public static function init(){
		
		db_update('intel_product_feed')
		->fields(array(
				'refresh'=>ProductFeed::INTEL_PRODUCT_FEED_REFRESH_DEFAULT,
				'checked'=>0,
				'queued'=>0,
				'modified'=>time(),
		))
		->execute();

	}
}