<?php 

namespace Drupal\data_cleansing;

class DataStorageManager {
		
	public static function brandsLoader(){
		db_set_active('intel_shop');
		$brands = db_query('SELECT * FROM {brands}')->fetchAll();
		db_set_active();
		return $brands;
	}
	
	public static function cpuLoader() {
		db_set_active('intel_shop');
		$cpu_tech = db_query('SELECT * FROM {tech}')->fetchAll();
		db_set_active();
		return $cpu_tech;
	}
}