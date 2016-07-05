
ABOUT SHOP SPIDER
-------------------
The idea is to install this module under 'Intel data center' drupal env which leverage data transactions between Intel shop and PConline. 
This module is helper backend tool for intel shop product addition. Two ways to import intel product.
	* Admin backend import directly. The only problem is script runing low effeciency will cause server time out. 
	* Admin backend import product id for pending. Cron job will run automatically base on system interval settings.

Warning
-------------------
Product addition is irreversible. Once start data crawing from PConline. Product info will save into DB.
There's no way to identify if the product info is correct. Therefore, if the product info is completely
wrong, it has to be removed from DB mannually or by admin backend.  

CHANGE/MODIFICATION
-------------------
Some modification has been done for DB fields' name which is necessary to fix issues caused by 
utilize DB remaining keywords. Otherwise, this module can't work in drupal. Includes:

Table name: pcol_all_products
Fields: 
	* type => prod_type
	* name => prod_name
	
Table name: pcol_all_product_properties
Fields:
	* group => group_name 
	
Code relevant change once DB field change
	* Changed in shop source: pcol_all_product_properties SQL 'group => group_name'
	  	../common/detail_request_processor.php line 17
	  	../common/resource/images/detail_request_processor.php line 17

File download directory under shop  	  	
	* ../product_images/
	  
Pending function list
---------------------
 * Product Viewer dependency on views module. 

DB Table creation
 * Import pconline_id from backend. 
--------------------- 
#intel_product_feed

fid
	*Auto Increment ID
pconline_id
	*Product id at PConline
product_type
	*Product type 
refresh 
	*How often to check for new feed items in seconds. 
checked 
	*Last time feed was checked for new items, as Unix timestamp.
queued 
	*Time when this feed was queued for refresh, 0 if not queued. 
modified 
	*When the feed was last modified, as Unix timestamp.
 