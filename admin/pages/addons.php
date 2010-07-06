<?php
/* For licensing terms, see /license.txt */
/**
	BNPanel
		
	@author 	Julio Montoya <gugli100@gmail.com> Beeznest 2010
	@package	tht.addons	
*/



//Check if called by script
if(THT != 1){die();}

class page {
	
	public $navtitle;
	public $navlist = array();
							
	public function __construct() {
		$this->navtitle = "Addons Sub Menu";
		$this->navlist[] = array("Add addons", "package_add.png", "add");
		$this->navlist[] = array("Edit addons", "package_go.png", "edit");
		$this->navlist[] = array("Delete addons", "package_delete.png", "delete");
	}
	
	public function description() {
		return "<strong>Managing Addons</strong><br />
		Welcome to the Addons Management Area. Here you can add, edit and delete web hosting Addons. Have fun :)<br />
		To get started, choose a link from the sidebar's SubMenu.";	
	}
	
	public function content() { 
		# Displays the page 
		global $main, $style, $db, $billing;
		
		switch($main->getvar['sub']) {
			default:
				if($_POST) {
					$n = null;
					foreach($main->postvar as $key => $value) {
						if($value == "" && !$n && $key != "admin" && substr($key,0,13) != "billing_cycle") {
							$main->errors("Please fill in all the fields!");
							$n++;
						}
					}
					if(!$n) {
						foreach($main->postvar as $key => $value) {
							if($key != "name") {
								if($n) {
									$additional .= ",";	
								}
								$additional .= $key."=".$value;
								$n++;
							}
						}
						//var_dump($main->postvar);
						$status = ADDON_STATUS_INACTIVE;
						if ($main->postvar['status'] == 'on') {
							$status = ADDON_STATUS_ACTIVE;
						}
						
						$db->query("INSERT INTO `<PRE>addons` (name, setup_fee, description, status) VALUES('{$main->postvar['name']}', '{$main->postvar['setup_fee']}', '{$main->postvar['description']}', '{$status}')");
						//echo "SELECT * FROM `<PRE>billing_cycles`";
						$query = $db->query("SELECT * FROM `<PRE>billing_cycles` WHERE status = ".BILLING_CYCLE_STATUS_ACTIVE);
						$product_id = mysql_insert_id();
						if($db->num_rows($query) > 0) {											
							$billing_cycle_result = '';
							while($data = $db->fetch_array($query)) {										
								$variable_name = 'billing_cycle_'.$data['id'];
								//var_dump($variable_name);
								if (isset($main->postvar[$variable_name])) {
									$sql_insert ="INSERT INTO `<PRE>billing_products` (billing_id, product_id, amount, type) VALUES('{$data['id']}', '{$product_id}', '{$main->postvar[$variable_name]}', '".BILLING_TYPE_ADDON."')";
									$db->query($sql_insert);									
								}
							}						
						}
						$main->errors("Addon has been added!");
					}
				}
				
				$billing_cycle_result = $billing->generateBillingSelect();
				$array['BILLING_CYCLE'] = $billing_cycle_result;	
				
				
				$array['STATUS'] = $main->createCheckbox('', 'status');						
									
	
				//----- Finish billing cycle					
				echo $style->replaceVar("tpl/addons/add.tpl", $array);
				break;
				
			case 'edit':
				if(isset($main->getvar['do'])) {
															
					$query = $db->query("SELECT * FROM `<PRE>addons` WHERE `id` = '{$main->getvar['do']}'");
					
					if($db->num_rows($query) == 0) {
						echo "That Addon doesn't exist!";	
					} else {
						if($_POST) {							
							foreach($main->postvar as $key => $value) {
								//if($value == "" && !$n && $key != "admin") {
								if($value == "" && !$n && $key != "admin" && substr($key,0,13) != "billing_cycle") {
									$main->errors("Please fill in all the fields!");
									$n++;
								}
							}							
							if(!$n) {
								foreach($main->postvar as $key => $value) {
									if($key != "name" && $key != "backend" && $key != "description" && $key != "type" && $key != "server" && $key != "admin") {
										if($n) {
											$additional .= ",";	
										}
										$additional .= $key."=".$value;
										$n++;
									}
								}
									
								$status = ADDON_STATUS_INACTIVE;
								if ($main->postvar['status'] == 'on') {
									$status = ADDON_STATUS_ACTIVE;
								}
															
										   
								$db->query("UPDATE `<PRE>addons` SET
										   `name`			= '{$main->postvar['name']}',
										   `description` 	= '{$main->postvar['description']}',
										   `status` 		= '{$status}',
										   `setup_fee` 		= '{$main->postvar['setup_fee']}'
										   WHERE `id` 		= '{$main->getvar['do']}'");								
								
								//-----Adding billing cycles 
								
								//Deleting all billing_products relationship							
								$query = $db->query("DELETE FROM `<PRE>billing_products` WHERE product_id = {$main->getvar['do']} AND type='".BILLING_TYPE_ADDON."' ");
								   
								$query = $db->query("SELECT * FROM `<PRE>billing_cycles` WHERE status = ".BILLING_CYCLE_STATUS_ACTIVE);
								$product_id = $main->getvar['do'];
								if($db->num_rows($query) > 0) {										
									$billing_cycle_result = '';
									
									//Add new relations
									while($data = $db->fetch_array($query)) {												
										$variable_name = 'billing_cycle_'.$data['id'];
										//var_dump($variable_name);
										if (isset($main->postvar[$variable_name]) && ! empty($main->postvar[$variable_name]) ) {
											$sql_insert ="INSERT INTO `<PRE>billing_products` (billing_id, product_id, amount, type) VALUES('{$data['id']}', '{$product_id}', '{$main->postvar[$variable_name]}', '".BILLING_TYPE_ADDON."')";
											$db->query($sql_insert);									
										}
									}						
								}
								//-----Finish billing cycles
						
								$main->errors("Package has been edited!");
								$main->done();
							}
						}
						$data = $db->fetch_array($query);
						
						$array['BACKEND'] = $data['backend'];
						$array['DESCRIPTION'] = $data['description'];						
						$array['STATUS'] = $main->createCheckbox('', 'status', $data['status']);						
						$array['NAME'] = $data['name'];
						
						$array['ID'] = $data['id'];
						
						global $type;
						//$array['FORM'] = $type->acpPedit($data['type'], $cform);
			
						
						//----- Adding billing cycle						
						$sql = "SELECT billing_id, b.name, amount FROM `<PRE>billing_cycles`  b INNER JOIN `<PRE>billing_products` bp on (bp.billing_id = b.id) WHERE product_id =".$data['id'];
						$query = $db->query($sql);		
						
						while($data = $db->fetch_array($query)) {
							$myresults [$data['billing_id']] = $data['amount'];				
						}
						
						$billing_cycle_result = $billing->generateBillingSelect($myresults);
						
						$array['BILLING_CYCLE'] = $billing_cycle_result;						
						
						//----- Finish billing cycle						
						
						echo $style->replaceVar("tpl/addons/edit.tpl", $array);
					}
				} else {
					$query = $db->query("SELECT * FROM `<PRE>addons`");
					if($db->num_rows($query) == 0) {
						echo "There are no addons to edit!";	
					} else {
						echo "<ERRORS>";
						while($data = $db->fetch_array($query)) {
							echo $main->sub("<strong>".$data['name']."</strong>", '<a href="?page=addons&sub=edit&do='.$data['id'].'"><img src="'. URL .'themes/icons/pencil.png"></a>');
							$n++;
						}
					}
				}
				break;
				
			case 'delete':
				if($main->getvar['do']) {
					//Deleting addons
					$db->query("DELETE FROM `<PRE>addons` WHERE `id` = '{$main->getvar['do']}'");					
					//$db->query("DELETE FROM `<PRE>billing_products` WHERE `product_id` = '{$main->getvar['do']}' AND type = '".BILLING_TYPE_ADDON."'");
					
					//Deleting relation between addons and packages 
					$db->query("DELETE FROM `<PRE>package_addons` WHERE `addon_id` = '{$main->getvar['do']}'");
					
					$main->errors("The addon has been Deleted!");		
				}
				$query = $db->query("SELECT * FROM `<PRE>addons`");
				if($db->num_rows($query) == 0) {
					echo "There are no addons to delete!";	
				} else {
					echo "<ERRORS>";
					while($data = $db->fetch_array($query)) {
						echo $main->sub("<strong>".$data['name']."</strong>", '<a href="?page=addons&sub=delete&do='.$data['id'].'"><img src="'. URL .'themes/icons/delete.png"></a>');
						$n++;
					}
				}
			break;
		}
	}
}
?>
