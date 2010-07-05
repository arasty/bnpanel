<?php
//////////////////////////////
// The Hosting Tool
// Client Area - View Package
// By Jonny H
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){die();}

class page {
	
	public function content() { # Displays the page 
		global $style, $db, $main, $server, $invoice;
		$data = $db->client($_SESSION['cuser']);
		$query2 = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `userid` = '{$db->strip($data['id'])}'");
		$data3 = $db->fetch_array($query2);
		$query = $db->query("SELECT * FROM `<PRE>packages` WHERE `id` = '{$db->strip($data3['pid'])}'");
		$data2 = $db->fetch_array($query);		
		$query3 = $db->query("SELECT * FROM `<PRE>users` WHERE `id` = '{$db->strip($data['id'])}'");
		$user_info = $db->fetch_array($query3);
		
		$array['USER'] = $user_info['user'];
		$array['SIGNUP'] = strftime("%D", $data3['signup']);
		$array['DOMAIN'] = $data3['domain'];
		$array['PACKAGE'] = $data2['name'];
		$array['DESCRIPTION'] = $data2['description'];
		
		$invoice_info = $invoice->getLastInvoiceByUser($user_info['id']);
		
		$return_array = $invoice->getInvoice($invoice_info['id'], true, false);
		$array['ADDON'] = $return_array['ADDON'];
		//var_dump($return_array);
		
		if($_POST) {
				if(md5(md5($main->postvar['currentpass']) . md5($data['salt'])) == $data['password']) {
					if($main->postvar['newpass'] == $main->postvar['cpass']) {
						$cmd = $main->changeClientPassword($data3['id'], $main->postvar['cpass']);
						if($cmd === true) {
							$main->errors("Details updated!");
						}
						else {
							$main->errors((string)$cmd);
						}
					}
					else {
						$main->errors("Your passwords don't match!");		
					}
				}
				else {
					$main->errors("Your current password wasn't correct!");	
				}
		}
		
		echo $style->replaceVar("tpl/cview.tpl", $array);
	}
}
?>
