<?php 

namespace Drupal\shop_spider;

class DataSpiderFactory implements DataSpiderInterface{
	
	private $database = 'intel_shop';
	public $newProductIds;
	
	/**
	 * Step 1:
	 * Fetch data from PConline 
	 * (non-PHPdoc)
	 * @see \Drupal\shop_spider\DataSpiderInterface::crawling()
	 */
	public function crawling() {
		//var_dump($this->newProductIds);exit;
		
		db_set_active($this->database);
		
		$types_cn_prodlist = $this->newProductLoader($this->newProductIds,'cn');
		
		//print '<pre>';print_r($types_cn_prodlist);print '</pre>';
		
		$modules = array("basic", "properties");
		
		foreach ($types_cn_prodlist as $typeName => $typeData) {
		
			if ($typeData == 0) {
				continue;
			}
		
			$data = array("productIds"=>$typeData);
			//New product ids
			$productIds = $data['productIds'];
		
			$moduleProductTotal = count($data['productIds']);
			echo "$typeName get $moduleProductTotal products <br/>";
			$i=1;
		
			foreach ($productIds as $productId) {
				$msg = "$typeName process $i / $moduleProductTotal productId: $productId ";
				$data = array("product_id"=>$productId, "prod_type"=>$typeName);
		
				//$existed = $db->fetchOne("select count(*) from pcol_all_products where product_id='{$productId}' and type='{$typeName}'");
		
				//Load basic info from PConline
				$product_base_url = 'http://pdlib.pconline.com.cn/product/intel/product_base_array_js.jsp?productId='.$productId;
				$basicInfoRawData = drupal_http_request($product_base_url);
				$basicInfo = $this->decodeRawdata($basicInfoRawData->data);
					
				if (!empty($basicInfo)) {
					$data['prod_name'] = $basicInfo['name'];
					$data['pic_url'] = $basicInfo['picUrl'];
					$data['price'] = $basicInfo['price'];
					$data['brand_id'] = $basicInfo['bid'];
					$data['created_time'] = date("Y-m-d H:i:s");
		
					//Insert/Update basic info
					db_merge('pcol_all_products')
					->key(array(
							'product_id'=>$productId,
							'prod_type'=>$typeName
					))
					->fields($data)
					->execute();
		
					//$db->replaceData("pcol_all_products", $data, "product_id='{$productId}' and type='{$typeName}'");
		
					foreach ($basicInfo['items'] as $item) {
						$detailData = array("product_id"=>$productId, "module"=>'basic');
						$detailData["item_name"] = $item["title"];
						$detailData["item_value"] = $item["value"];
						$detailData['created_time'] = date("Y-m-d H:i:s");
							
						//Insert/Update detail info
						db_merge('pcol_all_product_properties')
						->key(array(
								'product_id'=>$productId,
								'module'=>'basic',
								'group_name'=>'',
								'item_name'=>$item['title']
						))
						->fields($detailData)
						->execute();
		
						//$db->replaceData("pcol_all_product_properties", $detailData, "product_id='{$productId}' and module='basic' and `group`='' and item_name='{$item["title"]}'");
					}
		
					$msg .= "bi_1 ";
				} else {
					$msg .= "bi_0 ";
				}
		
				//Load properties
				$product_item_url = 'http://pdlib.pconline.com.cn/product/intel/product_item_array_js.jsp?productId='.$productId;
				$propertiesRawData = drupal_http_request($product_item_url);
				$properties = $this->decodeRawdata($propertiesRawData->data);
					
				if(!empty($properties)) {
					$j = 0;
					foreach ($properties['groups'] as $group) {
						$groupName = trim(str_replace($properties['name'], "", $group['groupName']));
						foreach ($group['items'] as $item) {
		
							//Insert/Update detail info
							db_merge('pcol_all_product_properties')
							->key(array(
									'product_id'=>$productId,
									'module'=>'properties',
									'group_name'=> $groupName,
									'item_name'=>$item['title']
							))
							->fields(array(
									'product_id'=>$productId,
									'module'=>'properties',
									'group_name'=>$groupName,
									'item_name'=>$item['title'],
									'item_value'=>$item['value'],
									'created_time'=>date("Y-m-d H:i:s")
							))
							->execute();
		
							//$db->replaceData("pcol_all_product_properties", array('product_id'=>$productId, 'module'=>'properties', '`group`'=>$groupName, 'item_name'=>$itemName, 'item_value'=>$itemValue), "product_id='{$productId}' and module='properties' and  `group`= '{$groupName}' and item_name='{$itemName}'");
							$j++;
						}
					}
					$msg .="p_".$j;
				} else {
					$_data = iconv("gbk", "utf-8", $propertiesRawData->data);
					$_data = preg_replace(array("/\/\/[\<\>a-zA-Z\/\s]+?\n/", "/\/\*[\s\S]+?\*\//"), "", $_data);
					$_data = trim($_data);
					$_data = substr($_data, 1, strlen($_data)-(strrpos($_data, ";")==strlen($_data)-1 ? 3 : 2));
					$_data = str_replace("'", '"', $_data);
					$_data = preg_replace("/([a-zA-Z0-9]+)\:([^\/])/", '"$1":$2', $_data);
					echo $_data;
					$msg .= "p_0";
				}
		
				//new basic info
				$base_info_url = "http://pdlib.pconline.com.cn/product/intel/base_info_json.jsp?productId={$productId}&callback=";
				$newBasicInfoRawData = drupal_http_request($base_info_url);
				$basicInfo = json_decode(iconv("gbk", "utf-8", trim($newBasicInfoRawData->data)), true);
					
				if (!empty($basicInfo['data'])) {
					//comment general/detail
					if (!empty($basicInfo['data']['comment'])) {
						$comment = $basicInfo['data']['comment'];
						foreach ($comment as $key => $value) {
							$commentData = array(
									'product_id'=>$productId,
									'module'=>'comment',
									'group_name'=>'general',
									'item_name'=>$key,
									'item_value'=>$value,
									'created_time' => date("Y-m-d H:i:s")
							);
		
							if ($key == 'items') {
								$commentData['group_name'] = 'detail';
								$commentData['item_value'] = json_encode($value);
							}
							//Insert/Update comment info
							db_merge('pcol_all_product_properties')
							->key(array(
									'product_id'=>$productId,
									'module'=>'comment',
									'group_name'=>$commentData['group_name'],
									'item_name'=>$commentData['item_name']
							))
							->fields($commentData)
							->execute();
		
							//$db->replaceData("pcol_all_product_properties", $commentData, "product_id='{$productId}' and module='comment' and  `group`='{$commentData['`group`']}' and item_name='{$commentData['item_name']}'");
						}
						$msg .=" cmt_1";
					} else {
						$msg .=" cmt_0";
					}
		
					//article '' article_count / detail
					if (!empty($basicInfo['data']['article'])) {
						$article = $basicInfo['data']['article'];
						$articleData = array(
								'product_id'=>$productId,
								'module'=>'article',
								'group_name'=>'',
								'item_name'=>'article_count',
								'item_value'=>count($article),
								'created_time'=>date("Y-m-d H:i:s")
						);
							
						//Insert/Update article info
						db_merge('pcol_all_product_properties')
						->key(array(
								'product_id'=>$productId,
								'module'=>'article',
								'item_name'=>$articleData['item_name']
						))
						->fields($articleData)
						->execute();
							
						//$db->replaceData("pcol_all_product_properties", $articleData, "product_id='{$productId}' and module='article' and item_name='{$articleData['item_name']}'");
							
						$articleData['item_name'] = 'detail';
						$articleData['item_value'] = json_encode($article);
							
						//Insert/Update article info
						db_merge('pcol_all_product_properties')
						->key(array(
								'product_id'=>$productId,
								'module'=>'article',
								'item_name'=>$articleData['item_name']
						))
						->fields($articleData)
						->execute();
							
						//$db->replaceData("pcol_all_product_properties", $articleData, "product_id='{$productId}' and module='article' and item_name='{$articleData['item_name']}'");
						$msg .=" atl_".count($article);
					} else {
						$msg .=" atl_0";
					}
		
					//company
					if (!empty($basicInfo['data']['company'])) {
						$company = $basicInfo['data']['company'];
						$companyData = array(
								'product_id'=>$productId,
								'module'=>'company',
								'group_name'=>'',
								'item_name'=>'company_count',
								'item_value'=>count($company),
								'created_time'=>date("Y-m-d H:i:s")
						);
							
						//Insert/Update company info
						db_merge('pcol_all_product_properties')
						->key(array(
								'product_id'=>$productId,
								'module'=>'company',
								'item_name'=>$companyData['item_name']
						))
						->fields($companyData)
						->execute();
		
						//$db->replaceData("pcol_all_product_properties", $companyData, "product_id='{$productId}' and module='company' and item_name='{$companyData['item_name']}'");
						$companyData['item_name'] = 'detail';
						$companyData['item_value'] = json_encode($company);
							
						//Insert/Update company info
						db_merge('pcol_all_product_properties')
						->key(array(
								'product_id'=>$productId,
								'module'=>'company',
								'item_name'=>$companyData['item_name']
						))
						->fields($companyData)
						->execute();
		
						//$db->replaceData("pcol_all_product_properties", $companyData, "product_id='{$productId}' and module='company' and item_name='{$companyData['item_name']}'");
						$msg .=" cpy_".count($company);
					} else {
						$msg .=" cpy_0";
					}
		
					//parameter general / detail / pics
					if (!empty($basicInfo['data']['parameter'])) {
						$parameter = $basicInfo['data']['parameter'];
						$parameterDetail = false;
						$msg .=" parm_";
						foreach ($parameter as $key => $value) {
							$parameterData = array('product_id'=>$productId, 'module'=>'parameter', 'group_name'=>'general', 'created_time'=>date("Y-m-d H:i:s"));
							if ($key == 'items') {
								foreach ($parameter['items'] as $item) {
									$parameterDetail = $parameterData;
									$parameterDetail['group_name'] = 'detail';
									$parameterDetail['item_name'] = $item['key'];
									$parameterDetail['item_value'] = $item['value'];
									$parameterDetail['item_value2'] = $item['displayValue'];
									$parameterDetail['item_value3'] = $item['nValue'];
		
									//Insert/Update parameter info
									db_merge('pcol_all_product_properties')
									->key(array(
											'product_id'=>$productId,
											'module'=>'parameter',
											'group_name'=>'detail',
											'item_name'=>$parameterDetail['item_name']
									))
									->fields($parameterDetail)
									->execute();
		
									//$db->replaceData("pcol_all_product_properties", $parameterDetail, "product_id='{$productId}' and module='parameter'  and  `group`='detail'  and item_name='{$parameterDetail['item_name']}'");
								}
								$msg .="items_".count($parameter['items']);
								continue;
							} elseif ($key == 'pics') {
								foreach ($parameter['pics'] as $pic) {
									$parameterDetail = $parameterData;
									$parameterDetail['group_name'] = 'pics';
									$parameterDetail['item_name'] = $pic['name'];
									$parameterDetail['item_value'] = $pic['pic'];
									$parameterDetail['item_value2'] = $pic['thumbPic'];
		
									//Insert/Update parameter info
									db_merge('pcol_all_product_properties')
									->key(array(
											'product_id'=>$productId,
											'module'=>'parameter',
											'group_name'=>'pics',
											'item_name'=>$parameterDetail['item_name']
									))
									->fields($parameterDetail)
									->execute();
		
									//$db->replaceData("pcol_all_product_properties", $parameterDetail, "product_id='{$productId}' and module='parameter'  and  `group`='pics' and item_name='{$parameterDetail['item_name']}'");
								}
								$msg .="_pics_".count($parameter['pics']);
								continue;
							}
							$parameterData['item_name'] = $key;
							$parameterData['item_value'] = $value;
		
							//Insert/Update parameter info
							db_merge('pcol_all_product_properties')
							->key(array(
									'product_id'=>$productId,
									'module'=>'parameter',
									'group_name'=>'general',
									'item_name'=>$parameterData['item_name']
							))
							->fields($parameterData)
							->execute();
		
							//$db->replaceData("pcol_all_product_properties", $parameterData, "product_id='{$productId}' and module='parameter'  and  `group`='general'  and item_name='{$parameterData['item_name']}'");
						}
						$msg .="_total_".count($parameter);
					} else {
						$msg .=" parm_0";
					}
		
				}
				echo $msg.'<br/>';
				$i++;
			}
		
		}
		db_set_active();
		
		watchdog('Spider crawling done', 'Products {%prod_ids} fetched.',
				array(
					'%prod_ids' => json_encode($this->newProductIds))
		);
				
		echo 'done';
		echo '<br/><br/><br/>';
		print l(t('Next Step'),'shop_spider/s2');
	}
	
	/**
	 * Step 2:
	 * Extract data from fetched storage. Save to product tables. 
	 * (non-PHPdoc)
	 * @see \Drupal\shop_spider\DataSpiderInterface::fix()
	 */
	public function fix() {
		//Load Data Map for fields mapping
		require_once './'. drupal_get_path('module', 'shop_spider') .'/extra/data_map.inc';

		db_set_active($this->database);
		
		$types_cn_prodlist = $this->newProductLoader($this->newProductIds,'cn');
		//print_r($types_cn_prodlist);
		$types = array_keys($exportMap);
		
		foreach ($types as $t) {
		
			$typeKey = $exportMap[$t]['type'];
			$tableName = $exportMap[$t]['table'];
		
			//echo "$typeKey \n";
		
			if (empty($types_cn_prodlist[$typeKey])) {
				continue;
			}
		
			$newIds = $types_cn_prodlist[$typeKey];
			//print_r($newIds);
			$products = db_select('pcol_all_products','pcol_all')
			->fields('pcol_all')
			->condition( 'prod_type', $typeKey, '=' )
			->condition( 'product_id', $newIds, 'IN' )
			->execute()
			->fetchAll();
		
			//$products = $db->fetchAll("select * from pcol_all_products where `type`='{$typeKey}' and product_id in ('".implode("','", $newIds)."')");
			//print_r($products);
			$num = count($products);
			$i = 1;
			foreach ($products as $p) {
				$msg = "$t ";
				$msg .= $i . " / " . $num . " pid:".$p->product_id;
				//var_dump($p);
				$data = array(
						"name"=>$p->prod_name,
						"pconline_id"=>$p->product_id,
						"price"=>$p->price,
						"pic_url"=>$p->pic_url
				);
		
				$properties = db_select('pcol_all_product_properties','pcol_prop')
				->fields('pcol_prop')
				->condition('product_id',$p->product_id,'=')
				->execute()
				->fetchAll();
				//$properties = $db->fetchAll("select * from pcol_all_product_properties where product_id='{$p['product_id']}' ");
		
				$pcache = array();
				foreach ($properties as $prop) {
					$propKey = $prop->module."_".$prop->group_name."_".$prop->item_name;
						
					if (in_array($propKey, $pcache)) {
						continue;
					} else {
						$pcache[] = $propKey;
					}
					$propValue = $prop->item_value;
						
					if (array_key_exists($propKey, $exportMap[$t]['property_fields'])) {
						$fieldName = $exportMap[$t]['property_fields'][$propKey]["field_name"];
						$data[$fieldName] = trim($propValue);
					}
					//print_r($data);
				}
				//print_r($data);
				if (isset($data['price'])) {
					$data['price'] = intval($data['price']);
				}
				if (isset($data['weight'])) {
					$data['weight'] = floatval($data['weight']);
				}
				if (isset($data['harddisc_size'])) {
					$data['harddisc_size'] = intval($data['harddisc_size']);
				}
				if (isset($data['thickness'])) {
					$data['thickness'] = floatval($data['thickness']);
				}
				if (isset($data['disk_size'])) {
					$data['disk_size'] = intval($data['disk_size']);
				}
				if (isset($data['memory_size'])) {
					$data['memory_size'] = intval($data['memory_size']);
				}
				if (isset($data['mem_daxiao'])) {
					$data['mem_daxiao'] = intval($data['mem_daxiao']);
				}
				if (isset($data['mem_rongliang'])) {
					$data['mem_rongliang'] = intval($data['mem_rongliang']);
				}
				if (isset($data['rongliang'])) {
					$data['rongliang'] = intval($data['rongliang']);
				}
				if (isset($data['main_frequency'])) {
					$data['main_frequency'] = floatval($data['main_frequency']);
				}
				if (isset($data['xianshiqi_size'])) {
					$data['xianshiqi_size'] = floatval($data['xianshiqi_size']);
				}
					
				//var_dump($data);
				//Insert/Update product info
				db_merge($tableName)
				->key(array(
						'pconline_id' => $p->product_id
				))
				->fields($data)
				->execute();
		
				//$db->replaceData($tableName, $data, "pconline_id='{$p['product_id']}'", false);
				//print_r($data);
				echo $msg;
				$i++;
			}
		}
		
		db_set_active();
		watchdog('Spider Fix done', 'Products {%prod_ids} fixed.',
				array(
						'%prod_ids' => json_encode($this->newProductIds))
		);
				
		echo 'done';
		echo '<br/><br/><br/>';
		print l(t('Next Step'),'shop_spider/s3');
	}
	
	/**
	 * Step 3: 
	 * Merge part of data into product all table for search index.
	 * This function might need to fix the fieldMap.
	 * (non-PHPdoc)
	 * @see \Drupal\shop_spider\DataSpiderInterface::merge()
	 */
	public function merge() {
		
		db_set_active($this->database);
		
		$types_en_prodlist = $this->newProductLoader($this->newProductIds,'en');
		//print_r($types_en_prodlist);
		$fieldMap = array(
				"products_tablet"=>array("name"=>"name", "price"=>"price", "pconline_id"=>"pconline_id", "hot"=>"hot", "cover_image"=>"cover_image", "m_cpu"=>"cpu", "m_brand_en"=>"brand_en", "screen_size"=>"screen_size", "mem_rongliang"=>"mem_size", "mem_type"=>"mem_type", "m_system"=>"operating_system")
				,"products_2in1"=>array("name"=>"name", "price"=>"price", "pconline_id"=>"pconline_id", "hot"=>"hot", "cover_image"=>"cover_image", "cpu"=>"cpu", "brand_name_en"=>"brand_en", "screen_size"=>"screen_size", "harddisc_size"=>"disk_size", "harddist_type"=>"disk_type", "operating_system"=>"operating_system")
				,"products_desktop"=>array("name"=>"name", "price"=>"price", "pconline_id"=>"pconline_id", "hot"=>"hot", "cover_image"=>"cover_image", "m_cpu"=>"cpu", "m_brand_en"=>"brand_en", "leixing"=>"product_type", "xianka_type"=>"xianka_type", "mem_daxiao"=>"mem_size", "mem_type"=>"mem_type")
				,"products_laptop"=>array("name"=>"name", "price"=>"price", "pconline_id"=>"pconline_id", "hot"=>"hot", "cover_image"=>"cover_image", "m_cpu"=>"cpu", "m_brand_en"=>"brand_en", "screen_size"=>"screen_size", "disk_size"=>"disk_size", "disk_type"=>"disk_type", "operating_system"=>"operating_system")
				,"products_allin1"=>array("name"=>"name", "price"=>"price", "pconline_id"=>"pconline_id", "hot"=>"hot", "cover_image"=>"cover_image", "cpu_type"=>"cpu", "m_brand_en"=>"brand_en", "screen_size"=>"screen_size", "disk_size"=>"disk_size", "disk_type"=>"disk_type", "operating_system"=>"operating_system")
				,"products_mobile"=>array("name"=>"name", "price"=>"price", "pconline_id"=>"pconline_id", "hot"=>"hot", "cover_image"=>"cover_image", "cpu"=>"cpu", "mainscreen_size"=>"screen_size", "screen_ratio"=>"screen_ratio", "product_tezheng"=>"product_spec")
				,"products_cpu"=>array("name"=>"name", "price"=>"price", "pconline_id"=>"pconline_id", "hot"=>"hot", "cover_image"=>"cover_image", "model_type"=>"cpu", "interface"=>"interface", "core_num"=>"core_num", "packaging"=>"packaging")
				,"products_ssd"=>array("name"=>"name", "price"=>"price", "pconline_id"=>"pconline_id", "hot"=>"hot", "cover_image"=>"cover_image", "rongliang"=>"disk_size", "disk_chicun"=>"disk_dimension", "read_speed"=>"read_speed", "main_chip"=>"main_chip")
				,"products_mainboard"=>array("name"=>"name", "price"=>"price", "pconline_id"=>"pconline_id", "hot"=>"hot", "cover_image"=>"cover_image", "m_support_cpu"=>"cpu", "waixing_chicun"=>"outline_dimension", "max_mem"=>"max_mem_size", "fit_model"=>"fit_type")
		);
				
		//$cols = db_field_names('products_all');
		//$cols = $db->getColumns("products_all");
		
		foreach ($fieldMap as $tableName => $fields) {
			if (empty($types_en_prodlist[$tableName])) {
				continue;
			}
			$newIds = $types_en_prodlist[$tableName];
			$prod_name = substr($tableName,9);
		
			$products = db_select( $tableName, $prod_name )
			->fields($prod_name)
			->condition( 'pconline_id', $newIds, 'IN' )
			->execute()
			->fetchAll();
			//$products = $db->fetchAll("select * from $tableName where pconline_id in ('".implode("','", $newIds)."')");
		
			echo ($tableName." ".count($products));
		
			$fields = $fieldMap[$tableName];
			/* Reserved code for table alter. Might not need anymore.
				print $tableName.'<br/>';
				foreach ($fields as $oldField => $newField) {
		
				if ( !db_field_exists($tableName,$newField) ) {
				print $newField.'<br/>';
		
				db_add_field( 'products_all', $newField, array(
				'type' => 'VARCHAR(50)',
				'not null' => TRUE
				));
				//$db->query("ALTER TABLE `products_all` ADD `{$newField}` VARCHAR( 50 ) NOT NULL;");
				//$cols[] = $newField;
				}
				}
			*/
			$i=1;
			$total = count($products);
			foreach ($products as $p) {
				$data = array("type"=>$tableName);
				foreach ($fields as $oldField => $newField) {
					$data[$newField] = $p->$oldField;
				}
					
				db_merge('products_all')
				->key(array(
						'pconline_id'=>$p->pconline_id
				))
				->fields($data)
				->execute();
					
				//$db->replaceData("products_all", $data, "pconline_id='{$p['pconline_id']}'");
				echo ($tableName." $i/$total ");
				$i++;
			}
		
			echo ($tableName . " done");
		}
		
		db_set_active();
		watchdog('Spider merge done', 'Products {%prod_ids} merged.',
				array(
						'%prod_ids' => json_encode($this->newProductIds))
		);
						
		echo '<br/><br/><br/>';
		print l(t('Next Step'),'shop_spider/s4');
	}
	
	/**
	 * Step 4: 
	 * Download cover image from PConline. (PConline have anti-stealing-link)
	 * (non-PHPdoc)
	 * @see \Drupal\shop_spider\DataSpiderInterface::download()
	 */
	public function download(){
		db_set_active($this->database);
		
		$types_en_prodlist = $this->newProductLoader($this->newProductIds,'en');
		
		foreach ($types_en_prodlist as $tableName => $newIds) {
			if (empty($newIds)) {
				continue;
			}
		
			$prod_name = substr($tableName,9);
			$products = db_select( $tableName, $prod_name )
			->fields($prod_name)
			->condition( 'pconline_id', $newIds, 'IN' )
			->execute()
			->fetchAll();
			//$products = $db->fetchAll("select * from $tableName where pconline_id in ('".implode("','", $newIds)."')");
		
			/*
				$cols = $db->getColumns($tableName);
		
				if (!array_key_exists("cover_image", $cols)) {
				$db->query("ALTER TABLE `{$tableName}` ADD `cover_image` VARCHAR( 50 ) NOT NULL;");
			} */
		
			$i = 0;
			$total = count($products);
		
			foreach($products as $p) {
				$i++;
				$data = array();
				$imageUrl = $p->pic_url;
		
				if (empty($imageUrl)) {
					echo("{$i}/{$total} 0");
					continue;
				}
		
				$imageData = drupal_http_request($imageUrl);
		
				//file_put_contents(__DIR__.'/../../../../../sites/default/files/intel_product_images/'.$p->pconline_id.'.jpg', $imageData->data);
				file_put_contents(__DIR__.'/../../../../../../../shop/product_images/'.$p->pconline_id.'.jpg', $imageData->data);
				db_update($tableName)
				->fields(array(
						'cover_image' => $p->pconline_id.'.jpg'
				))
				->condition('pconline_id', $p->pconline_id, '=')
				->execute();
				//$db->query("update $tableName set cover_image='{$p['pconline_id']}.jpg' where pconline_id='{$p['pconline_id']}'");
					
				db_update('products_all')
				->fields(array(
						'cover_image' => $p->pconline_id.'.jpg'
				))
				->condition('pconline_id', $p->pconline_id, '=')
				->execute();
				//$db->query("update products_all set cover_image='{$p['pconline_id']}.jpg' where pconline_id='{$p['pconline_id']}'");
		
				echo("{$i}/{$total} 1").'<br/>';
			}
		}
		
		db_set_active();
		
		watchdog('Spider download done', 'Products {%prod_ids} picture downloaded.',
				array(
						'%prod_ids' => json_encode($this->newProductIds))
		);
				
		echo '<br/><br/><br/>';
		print l(t('Add More Product'),'shop_spider');
	}
	
	/**
	 * Tool function for product id organize.
	 * 
	 * @param unknown $add_new_prod
	 * @param unknown $return_type
	 * @return multitype:number unknown
	 */
	public static function newProductLoader($add_new_prod , $return_type){
		$types_en_prodlist = array( 'key'=>0 );
		
		$types_cn_prodlist = array(	'key'=>0 );
		
		if ( $return_type == 'en' ){
			foreach ( $add_new_prod as $type => $ids ){
				if($ids==0){continue;}
				$type = 'products_'.$type;
				$types_en_prodlist[$type] = $ids;
			}
			return $types_en_prodlist;
		}else if( $return_type == 'cn' ){
			foreach ( $add_new_prod as $type => $ids ){
				if($ids==0){continue;}
				switch ($type){
					case 'tablet':
						$types_cn_prodlist['平板电脑'] = $ids; break;
					case '2in1':
						$types_cn_prodlist['2in1'] = $ids; break;
					case 'desktop':
						$types_cn_prodlist['台式机'] = $ids; break;
					case 'laptop':
						$types_cn_prodlist['笔记本'] = $ids; break;
					case 'allin1':
						$types_cn_prodlist['一体电脑'] = $ids; break;
					case 'mobile':
						$types_cn_prodlist['手机'] = $ids; break;
					case 'cpu':
						$types_cn_prodlist['CPU'] = $ids; break;
					case 'ssd':
						$types_cn_prodlist['SSD'] = $ids; break;
					case 'mainboard':
						$types_cn_prodlist['主板'] = $ids; break;
				}
			}
			return $types_cn_prodlist;
		}
	}
	
	/**
	 * Legecy tool function for data filter.
	 * PConline return GBK encode data. 
	 * 
	 * @param unknown $rawData
	 * @return mixed
	 */
	public static function decodeRawdata($rawData){
		$data = json_decode($rawData, true);
		if (!empty($data)) {
			return $data;
		}
		
		$data = iconv("gbk", "utf-8", $rawData);
		$data = preg_replace(array("/\/\/[\<\>a-zA-Z\/\s]+?\n/", "/\/\*[\s\S]+?\*\//"), "", $data);
		$data = trim($data);
		$data = substr($data, 1, strlen($data)-(strrpos($data, ";")==strlen($data)-1 ? 3 : 2));
		$data = str_replace("'", '"', $data);
		$data = preg_replace("/([a-zA-Z]+)\:([^\/])/", '"$1":$2', $data);
		$realData = json_decode($data, true);
		return $realData;
	}
	
}