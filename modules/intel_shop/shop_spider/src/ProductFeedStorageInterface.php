<?php 

namespace Drupal\shop_spider;

interface ProductFeedStorageInterface{
	
	public static function import($pcOnlineId, $productType);
	
	public static function expire();
	
	public static function refresh($pcOnlineId);
}