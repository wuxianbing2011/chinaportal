<?php 

use Drupal\shop_spider\DataSpiderFactory;
use Drupal\data_cleansing\DataCleansingFactory;
use Drupal\shop_spider\ProductFeed;
use Drupal\shop_spider\ProductFeedStorage;

/**
 * Implementation of hook_form()
 * 
 * @return multitype:
 */
function shop_spider_testing_form(){
	$form = array();
	
	$form ['product_id'] = array (
		'#type' => 'textfield',
		'#title' => t ( 'Product Id' ),
		'#description' => t ( 'PConline id.' )
	);
	
	$form ['submit'] = array (
			'#type' => 'submit',
			'#value' => t ( 'Submit' ) 
	);
	
	
// 	db_set_active('intel_shop');
// 	$products = db_query('SELECT * FROM {products_2in1} LIMIT 165')->fetchAll();
// 	foreach ($products as $product){
// 		db_update('products_2in1')
// 		->fields(array(
// 			'cpu' => $product->chuliqi,
// 		))
// 		->condition('pconline_id', $product->pconline_id, '=')
// 		->execute();
// 	}
// 	db_set_active();
	
// 	$product_types = array('2in1','allin1','desktop','laptop','tablet');
	
// 	foreach ($product_types as $product_type){
// 		$DCFobj = new DataCleansingFactory($product_type);
// 		$DCFobj->brandsNameFix();
// 	}
// 	$DCFobj = new DataCleansingFactory('allin1');
// 	$DCFobj->brandsNameFix();
// 	$DCFobj->main();
	//_data_cleansing_main();
	//_spider_product_feed_expire();
	
	//ProductFeedStorage::init();
	return $form;
}

function shop_spider_testing_form_submit($form,&$form_state){
	$productId = $form_state['values']['product_id'];
	
	module_load_include('inc','shop_spider');
	
  	$product_base_url = 'http://pdlib.pconline.com.cn/product/intel/product_base_array_js.jsp?productId='.$productId;
	$basicInfoRawData = drupal_http_request($product_base_url);
	$basicInfo = DataSpiderFactory::decodeRawdata($basicInfoRawData->data);
	
	dpm($basicInfo); 
		
	//Load properties
	$product_item_url = 'http://pdlib.pconline.com.cn/product/intel/product_item_array_js.jsp?productId='.$productId;
	$propertiesRawData = drupal_http_request($product_item_url);
	$properties = DataSpiderFactory::decodeRawdata($propertiesRawData->data);
	
	dpm($properties); 
	
	//new basic info
	$base_info_url = "http://pdlib.pconline.com.cn/product/intel/base_info_json.jsp?productId={$productId}&callback=";
	$newBasicInfoRawData = drupal_http_request($base_info_url);
	$basicInfoNew = json_decode(iconv("gbk", "utf-8", trim($newBasicInfoRawData->data)), true);
	
	dpm($basicInfoNew);
	//var_dump($basicInfoNew);exit;
}
