<?php
class RouteRouteHome extends Route{

	function validate(){

		
		return true;

	}

	function render(){
		$page = new Template();
		$data = bank_get_data();
		//$data = bank_trans_in();
		foreach($data as $key=>$row){
			$row[0] = Render::date($row[0]);
			$row[3] = '£'.number_format($row[3], 2);
			$row[4] = '£'.number_format($row[4], 2);
			$row[5] = '£'.number_format((float) $row[5], 2);
			$data[$key] = $row;
		}

		$table = Render::table(array('date', 'type', 'desc', 'out', 'in', 'bal'), $data);
		$gtable = Render::table(array('grp', 'out', 'in'), bank_transaction_group());


		$page->c($table);
		//$page->c($gtable);
		
		$this->output = $page->render();
	}
}