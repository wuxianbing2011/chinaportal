<?php 

/**
 * Implementation of hook_form()
 * 
 * @return multitype:multitype:string NULL
 */
function data_cleansing_product_purchase_link_import_form(){
	$form = array();
	
	$form['information'] = array(
		'#markup' => t('Note: This form is for content updates only. Please do not leave any row in your file empty. It will refresh existing cell of data into empty values!<br/><br/>'),	
	);
	
	$form ['upload'] = array(
			'#type' => 'file',
			'#title' => t('Excel file'),
			'#description' => t('Only allowed excel 2007 or above. Extension: *.xlsx'),
	);
	
	$form ['submit'] = array (
			'#type' => 'submit',
			'#value' => t ( 'Import' )
	);
	
	//_product_purchase_link_update('');
	return $form;
}

/**
 * Implementation of hook_form_validate()
 * 
 * @param unknown $form
 * @param unknown $form_state
 */
function data_cleansing_product_purchase_link_import_form_validate($form,&$form_state){
	$validators = array('file_validate_extensions' => array('xlsx'));
	
	// Check for a new uploaded favicon.
	$file = file_save_upload('upload', $validators);
	
	if (isset($file)) {
		// File upload was attempted.
		if ($file) {
			// Put the temporary file in form_values so we can save it on submit.
			$form_state['values']['upload'] = $file;
		}
		else {
			// File upload failed.
			form_set_error('upload', t('The file could not be uploaded.'));
		}
	}else {
		form_set_error('upload',t('Please select a file to upload.'));
	}
	
}

/**
 * Implementation of hook_form_submit()
 * Save file to server and import to database
 * 
 * @param unknown $form
 * @param unknown $form_state
 */
function data_cleansing_product_purchase_link_import_form_submit($form,&$form_state){
	$file = $form_state['values']['upload'];
	unset($form_state['values']['upload']);
	$file->status = FILE_STATUS_PERMANENT;
	$file->destination = 'public://product_purchase_link_import/';
	file_save($file);
	file_move($file, $file->destination, FILE_EXISTS_RENAME);
	//dpm($file);
	drupal_set_message(t('@file is uploaded', array('@file' => $file->filename)));
	
	_product_purchase_link_update(variable_get('file_public_path', conf_path() . '/files').'/product_purchase_link_import/'.$file->filename);
	
	//dpm($file);
}


/**
 * Import purchase links.
 * 
 * @param unknown $prod_type
 * @param unknown $data
 */
function _product_purchase_link_update($import_file_name){
	//$import_file_name = 'sites/default/files/product_purchase_link_import/cpu-ssd-1105.xlsx';
	db_set_active('intel_shop');	
	
	//Call phpexcel module.
	//http://ci.heraldci.org/api/phpexcel/phpexcel.api.php/group/phpexcel_api/7.x-3.x
	module_load_include('inc','phpexcel');
	$data = phpexcel_import($import_file_name,true,true);
	//dpm($data);
	foreach ($data as $sheet_name => &$sheet_data){
		//Only CPU, SSD have tmall fields
		$table_name = 'products_'.strtolower($sheet_name);
		if(db_field_exists($table_name, 'tmall_id') && db_field_exists($table_name, 'tmall_price')){
			$tmall = TRUE;
		}else{
			$tmall = FALSE;
		}
		
		foreach ($sheet_data as &$row){									
			$row['pconline_id'] = !empty($row['pconline_id']) ? intval($row['pconline_id']) : null;
			$row['jd_id'] = !empty($row['jd_id']) ? intval($row['jd_id']) : null;
			$row['jd_price'] = !empty($row['jd_price']) ? intval($row['jd_price']) : null;
			$row['suning_id'] = !empty($row['suning_id']) ? intval($row['suning_id']) : null;
			$row['suning_price'] = !empty($row['suning_price']) ? intval($row['suning_price']) : null;
			
			if($tmall){				
				$row['tmall_id'] = !empty($row['tmall_id']) ? floatval($row['tmall_id']) : null;
				$row['tmall_price'] = !empty($row['tmall_price']) ? intval($row['tmall_price']) : null;
			}else {
				unset($row['tmall_id']);
				unset($row['tmall_price']);
			}						

 			db_update($table_name)
			->fields($row)
			->condition('pconline_id', $row['pconline_id'], '=')
			->execute(); 
			
// 			dpm($row);
		}
	}
		
	db_set_active();
	//dpm($data);
	
	watchdog('Purchaselink import done', '{%data} is imported.', array('%data' => json_encode($data)));
}


/**
 * Not using currently. Only consider to implement if .csv file import is required. 
 * This function will reading Excel 2007(*.xlsx) - All Worksheets
 * Specific for product purchase link import.
 *
 * @param unknown $import_file_name
 */
function _excel_import_handler($import_file_name){

	$import_file_name = 'sites/default/files/product_purchase_link_import/cpu-ssd-1105.xlsx';

	//Methodology: Implement directly with PHPExcel library.
	$lib_path = libraries_get_path('PHPExcel');
	require_once $lib_path.'/Classes/PHPExcel/IOFactory.php';

	$objReader = PHPExcel_IOFactory::createReader('Excel2007');
	echo 'Loading all WorkSheets</br>';

	$objReader->setLoadAllSheets();
	$objPHPExcel = $objReader->load($import_file_name);

	echo $objPHPExcel->getSheetCount(),' worksheet',(($objPHPExcel->getSheetCount() == 1) ? '' : 's'),' loaded</br>';

	$loadedSheetNames = $objPHPExcel->getSheetNames();

	foreach($loadedSheetNames as $sheetIndex => $loadedSheetName) {

		echo 'Worksheet #',$sheetIndex,' -> ',$loadedSheetName;
		$objPHPExcel->setActiveSheetIndexByName($loadedSheetName);
		$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		//var_dump($sheetData);

		$col = array();

		foreach ($sheetData as $sheetRow){
			$data = array();
			if($i == 1){
				$col = $sheetRow;
				//var_dump($col);
			}else {
				$data[$col['A']] = isset($sheetRow['A']) ? intval($sheetRow['A']) : null;
				$data[$col['B']] = isset($sheetRow['B']) ? intval($sheetRow['B']) : null;
				$data[$col['C']] = isset($sheetRow['C']) ? intval($sheetRow['C']) : null;
				$data[$col['D']] = isset($sheetRow['D']) ? intval($sheetRow['D']) : null;
				$data[$col['E']] = isset($sheetRow['E']) ? intval($sheetRow['E']) : null;
				$data[$col['F']] = isset($sheetRow['F']) ? floatval($sheetRow['F']) : null;
				$data[$col['G']] = isset($sheetRow['G']) ? intval($sheetRow['G']) : null;

				dpm($data);
			}

		}
	}

}
