<?php 

namespace Drupal\data_cleansing;
use Drupal\data_cleansing\DataStorageManager;

class DataCleansingFactory implements DataCleansingInterface{
	
	public $product_type;
	public $product_ids;
	
	public function __construct($product_type, $product_ids = NULL){
		$this->product_type = $product_type;
		$this->product_ids = $product_ids;
		
		return $this;
	}
	
	/**
	 * This function should call data cleansing functions which considered should be done automatically
	 * during spider's executing. 
	 * (non-PHPdoc)
	 * @see \Drupal\data_cleansing\DataCleansingInterface::main()
	 */
	public function main(){		
		self::detailsImagesExplorer();
		self::cpuNameFix();
		self::brandsNameFix();
	}
	
	/**
	 * 
	 */
	public function brandsNameFix(){
		$brands = DataStorageManager::brandsLoader();
		
		db_set_active('intel_shop');
		
		$table_name = 'products_'.$this->product_type;
		
		//Add field if table don't have this field
		if(!db_field_exists($table_name,'brand_id')){
			$field = array(
				'type' => 'int',
				'unsigned' => TRUE,
				'not null' => FALSE,
				'default' => NUll,
				'description' => 'The {brands}.id of the brand.',
			);
			db_add_field($table_name, 'brand_id', $field);
		}
		
		if (!db_field_exists($table_name,'brand_name')){
			$field = array(
					'type' => 'varchar',
					'length' => '50',
					'not null' => FALSE,
					'default' => NUll,
					'description' => 'The {brands}.name of the brand.',
			);
			db_add_field($table_name, 'brand_name', $field);
		}
		
		if (!db_field_exists($table_name,'brand_name_en')){
			$field = array(
					'type' => 'varchar',
					'length' => '50',
					'not null' => FALSE,
					'default' => NUll,
					'description' => 'The {brands}.name_en of the brand.',
			);
			db_add_field($table_name, 'brand_name_en', $field);
		}
		
		if(isset($this->product_ids)){
			$products = db_select($table_name, $this->product_type)
				->fields( $this->product_type, array('name','pconline_id') )
				->condition( 'pconline_id', $this->product_ids, 'IN' )
				->condition( 'brand_id', NULL, 'is' )
				->execute()
				->fetchAll();
		}else {
			$products = db_select($table_name, $this->product_type)
				->fields( $this->product_type, array('name','pconline_id') )
				->condition( 'brand_id', NULL, 'is' )
				->execute()
				->fetchAll();
		}
		
		foreach ($products as $product){
			foreach ($brands as $brand){
				if(preg_match("/".$brand->name."/i", $product->name)){
					db_update($table_name)
						->fields(array(
							'brand_id' => $brand->id,
							'brand_name' => $brand->name,
							'brand_name_en' => $brand->name_en	
						))
						->condition('pconline_id', $product->pconline_id, '=')
						->execute();
				}
			}
		}
		
		db_set_active();
		
// 		dpm($brands);
// 		dpm($products);
	}
	
	/**
	 * This function will not working for 'mainboard', 'ssd'
	 */
	public function cpuNameFix(){
		$cpus = DataStorageManager::cpuLoader();
		
		if($this->product_type == 'mainboard' || $this->product_type == 'ssd'){
			return;
		}
		
		$table_name = 'products_'.$this->product_type;
		
		db_set_active('intel_shop');
		
		if (!db_field_exists($table_name,'chuliqi')){
			$field = array(
					'type' => 'varchar',
					'length' => '50',
					'not null' => FALSE,
					'default' => NUll,
					'description' => 'The {tech}.extra name of the CPU.',
			);
			db_add_field($table_name, 'chuliqi', $field);
		}
		
		if(isset($this->product_ids)){
			$products = db_select($table_name, $this->product_type)
			->fields( $this->product_type, array('cpu','pconline_id') )
			->condition( 'pconline_id', $this->product_ids, 'IN' )
			->execute()
			->fetchAll();
		}else {
			$products = db_select($table_name, $this->product_type)
			->fields( $this->product_type, array('cpu','pconline_id') )
			->execute()
			->fetchAll();
		}
		
		foreach ($products as $product){
			foreach ($cpus as $cpu){
				if(preg_match("/".$cpu->tech."/i", $product->cpu)){
					db_update($table_name)
						->fields(array(
							'chuliqi' => $cpu->extra,								
						))
						->condition('pconline_id', $product->pconline_id, '=')
						->execute();
				}
			}
		}
				
		db_set_active();
	}
	
	/**
	 * Product deletion.
	 * 
	 */
	public function delete() {
		
		if(isset($this->product_ids)){
			db_set_active('intel_shop');
			db_delete('pcol_all_products')
			->condition('product_id',$this->product_ids,'IN')
			->execute();
			
			db_delete('pcol_all_product_properties')
			->condition('product_id',$this->product_ids,'IN')
			->execute();
			
			db_delete('products_all')
			->condition('pconline_id',$this->product_ids,'IN')
			->execute();
			
			db_delete('products_'.$this->product_type)
			->condition('pconline_id',$this->product_ids,'IN')
			->execute();
			db_set_active();
		}else {
			drupal_set_message(t('Product ids can not be empty when calling this function.'));
		}
				
	}
	
	/**
	 * Data Cleansing: Merge image Urls into product table field. 
	 * This should be done in spider.
	 * 
	 * @see \Drupal\data_cleansing\DataCleansingInterface::detailsImagesExplorer()
	 */
	public function detailsImagesExplorer(){
		db_set_active('intel_shop');
		
		$details_images = array();
		$products = db_query('SELECT pconline_id FROM {products_'.$this->product_type.'}')->fetchAll();
		foreach ($products as $product){			
			$prduct_image_urls = db_query("SELECT item_value FROM {pcol_all_product_properties} WHERE product_id=".$product->pconline_id." 
					AND module='parameter' AND group_name='pics' LIMIT 3")->fetchAll();

			$url_appends = '';
			$index = 1;
			foreach ($prduct_image_urls as $prduct_image_url){				
				$index == 3 ? $separator = '' : $separator = '|';
				$url_appends .= $prduct_image_url->item_value.$separator;				
				$index++;				
			}
			!empty($url_appends) ? $details_images[$product->pconline_id] = $url_appends : '';
			
			db_update('products_'.$this->product_type)
			->fields(array(
				'pic_url' => $url_appends
			))
			->condition('pconline_id', $product->pconline_id, '=')
			->execute();
			
		}
//  	dpm($details_images);
		
		db_set_active();
	}
	
	/**
	 * Data format fix. 
	 * Invoke this function only when neccesary.
	 * 
	 */
	public static function fieldsCleanUp(){
		db_set_active('intel_shop');
		
		if(db_field_exists('products_2in1','memory_size')){
			$products_2in1 = db_query('SELECT id, memory_size FROM {products_2in1}')
			->fetchAll();
			//$ids = array();
			foreach($products_2in1 as $product){
				if(preg_match('/^(\d+(GB))?$/',$product->memory_size)){
					//array_push($ids,$product->id);
					db_update('products_2in1')
					->fields(array(
							'memory_size' => intval($product->memory_size),
					))
					->condition('id', $product->id, '=')
					->execute();
				}
			}
			//dpm(implode(',',$ids));
		}
		
		if(db_field_exists('products_allin1','mem_rongliang')){
			$products_allin1 = db_query('SELECT id, mem_rongliang FROM {products_allin1}')
			->fetchAll();
			//$ids = array();
			foreach($products_allin1 as $product){
				if(preg_match('/^(\d+(GB))?$/',$product->mem_rongliang)){
					//array_push($ids,$product->id);
					db_update('products_allin1')
					->fields(array(
							'mem_rongliang' => intval($product->mem_rongliang),
					))
					->condition('id', $product->id, '=')
					->execute();
				}
			}
			//dpm(implode(',',$ids));
		}
		
		db_set_active();
	}
}