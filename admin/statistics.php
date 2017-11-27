<?php
/*	Project:	EQdkp-Plus
 *	Package:	statistics Plugin
 *	Link:		http://eqdkp-plus.eu
 *
 *	Copyright (C) 2006-2015 EQdkp-Plus Developer Team
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU Affero General Public License as published
 *	by the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Affero General Public License for more details.
 *
 *	You should have received a copy of the GNU Affero General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// EQdkp required files/vars
define('EQDKP_INC', true);
define('IN_ADMIN', true);
define('PLUGIN', 'statistics');

$eqdkp_root_path = './../../../';
include_once($eqdkp_root_path.'common.php');


/*+----------------------------------------------------------------------------
  | statisticsSettings
  +--------------------------------------------------------------------------*/
class StatisticsViewer extends page_generic{

	/**
	* Constructor
	*/
	public function __construct(){
		// plugin installed?
		if (!$this->pm->check('statistics', PLUGIN_INSTALLED))
			message_die($this->user->lang('st_plugin_not_installed'));

		$handler = array(
			'data'	=> array('process' => 'ajax_data', 'check' => 'a_statistics_manage'),
		);
		
		parent::__construct('a_statistics_manage', $handler);

		$this->process();
	}

	public function ajax_data(){		
		$date_from = ($this->in->get('from') != "") ? $this->time->fromformat($this->in->get('from','1.1.1970').' 00:00', 1) : false;
		$date_to = ($this->in->get('to') != "") ? $this->time->fromformat($this->in->get('to','1.1.1970').' 23:59', 1) : false;
		//Build Date Array
		$dateArray = array();
		$indexArray = array();
		$intKey = 0;
		for ( $i = $date_from; $i <= $date_to; $i = $i + (86400/2) ) {
			$date = $this->timeformat($i);
			if(!isset($indexArray[$date])){
				$dateArray[] = array($this->time->date("Y-m-d h:i:s", $date), 0);
				$indexArray[$date] = $intKey;
				$intKey++;
			}
		}
		
		//Get visits and clicks
		if($this->in->get('clicks', 0) || $this->in->get('visits', 0)){
			$arrVisits = $dateArray;
			$arrClicks = $dateArray;
			$objQuery = $this->db->prepare("SELECT * FROM __plugin_statistics WHERE dateID >= ? AND dateID <= ?")->execute($date_from, $date_to);
			if($objQuery){
				while($row = $objQuery->fetchAssoc()){
					$date = $this->timeformat($row['dateID']);
					$intIndex = $indexArray[$date];
					if($intIndex !== false){
						$arrVisits[$intIndex][1] = (int)$row['visits'];
						$arrClicks[$intIndex][1] = (int)$row['clicks'];
					}
				}
			}
			if($this->in->get('clicks', 0)) $out['clicks'] = array('label' => $this->user->lang('st_clicks'), 'data' => $arrClicks);
			if($this->in->get('visits', 0)) $out['visits'] = array('label' => $this->user->lang('st_visits'), 'data' => $arrVisits);
		}

		//Get user registrations
		if($this->in->get('user_regs', 0)){
			$arrUserRegistrations = $dateArray;
			$objQuery = $this->db->prepare("SELECT * FROM __users WHERE user_registered >= ? AND user_registered <= ?")->execute($date_from, $date_to);
			if($objQuery){
				while($row = $objQuery->fetchAssoc()){
					$date = $this->timeformat($row['user_registered']);
					$intIndex = $indexArray[$date];
					if($intIndex !== false){
						$arrUserRegistrations[$intIndex][1] = $arrUserRegistrations[$intIndex][1]+1;
					}
				}
			}
	
			$out['user_regs'] = array('label' => $this->user->lang('st_user_regs'), 'data' => $arrUserRegistrations);
		}
		
		//Get raids
		if($this->in->get('raids', 0)){
			$arrRaids = $dateArray;
			$objQuery = $this->db->prepare("SELECT * FROM __raids WHERE raid_date >= ? AND raid_date <= ?")->execute($date_from, $date_to);
			if($objQuery){
				while($row = $objQuery->fetchAssoc()){
					$date = $this->timeformat($row['raid_date']);
					$intIndex = $indexArray[$date];
					if($intIndex !== false){
						$arrRaids[$intIndex][1] = $arrRaids[$intIndex][1]+1;
					}
				}
			}
			$out['raids'] = array('label' => $this->user->lang('raids'), 'data' => $arrRaids);
		}
		
		//Get items
		if($this->in->get('items', 0)){
			$arrItems = $dateArray;
			$objQuery = $this->db->prepare("SELECT * FROM __items WHERE item_date >= ? AND item_date <= ?")->execute($date_from, $date_to);
			if($objQuery){
				while($row = $objQuery->fetchAssoc()){
					$date = $this->timeformat($row['item_date']);
					$intIndex = $indexArray[$date];
					if($intIndex !== false){
						$arrItems[$intIndex][1] = $arrItems[$intIndex][1]+1;
					}
				}
			}
			$out['items'] = array('label' => $this->user->lang('items'), 'data' => $arrItems);
		}
		
		//Get raidevent signups
		if($this->in->get('raidsignups', 0)){
			$arrRaidsignups = $dateArray;
			$objQuery = $this->db->prepare("SELECT * FROM __calendar_raid_attendees WHERE timestamp_signup >= ? AND timestamp_signup <= ?")->execute($date_from, $date_to);
			if($objQuery){
				while($row = $objQuery->fetchAssoc()){
					$date = $this->timeformat($row['timestamp_signup']);
					$intIndex = $indexArray[$date];
					if($intIndex !== false){
						$arrRaidsignups[$intIndex][1] = $arrRaidsignups[$intIndex][1]+1;
					}
				}
			}
			$out['raidsignups'] = array('label' => $this->user->lang('st_raidsignups'), 'data' => $arrRaidsignups);
		}
		
		//External
		$arrExternal = $this->in->getArray('external', 'string');
		if(is_array($arrExternal)){
		foreach($arrExternal as $strType){
			$arrOut = $dateArray;
			$objQuery = $this->db->prepare("SELECT * FROM __plugin_statistics_external WHERE name=? AND dateID >= ? AND dateID <= ?")->execute($strType, $date_from, $date_to);
			if($objQuery){
				while($row = $objQuery->fetchAssoc()){
					$date = $this->timeformat($row['dateID']);
					$intIndex = $indexArray[$date];
					if($intIndex !== false){
						$arrOut[$intIndex][1] = (int)$row['value'];
					}
				}
			}
			$out[$strType] = array('label' => $this->user->lang('plugin_statistics_'.$strType), 'data' => $arrOut);
		}
		}
		
		echo json_encode($out);
		die();
	}
	

	/**
	* display
	* Display the page
	*
	* @param    array  $messages   Array of Messages to output
	*/
	public function display($messages=array()){
		$y = $this->time->date("Y");
		$d = $this->time->date("d");
		$m = $this->time->date("m");		
		
		$date_from = $this->time->mktime(0, 0, 0, $m, 1, $y);
		$date_to = $this->time->mktime(0, 0, 0, $m, $d, $y);
		if($date_from == $date_to){
			if($m > 1) $m = $m-1;
			$date_from = $this->time->mktime(0, 0, 0, $m, 1, $y);
		}
		
		
		$_date_from = $this->time->user_date($date_from , false, false, false, function_exists('date_create_from_format'));
		$_date_to = $this->time->user_date($date_to , false, false, false, function_exists('date_create_from_format'));

		//$this->jquery->charts('line', 'statisticsGraph2', array(0 => array('name' => '2015-02-01', 'value'=>0)), array('xrenderer' => 'date'));
		$this->jquery->init_jqplot();
		// -- Template ------------------------------------------------------------
		$this->tpl->assign_vars(array (
			'FILTER_DATE_FROM'		=> $this->jquery->Calendar('filter_date_from', $_date_from, '', array('change_year' => true,'change_month' => true,'other_months' => true, 'number_months' => 3, 'onclose' => ' $( "#cal_filter_date_to" ).datepicker( "option", "minDate", selectedDate );')),
			'FILTER_DATE_TO'		=> $this->jquery->Calendar('filter_date_to', $_date_to, '', array('change_year' => true,'change_month' => true,'other_months' => true, 'number_months' => 3,  'onclose' => ' $( "#cal_filter_date_from" ).datepicker( "option", "maxDate", selectedDate );')),		
		));

		$arrHooks = $this->hooks->process('plugin_statistics');
		$arrExternal = array();
		foreach($arrHooks as $strPlugin => $arrData){
			$arrExternal = array_merge($arrExternal, $arrData);
		}
		
		foreach($arrExternal as $strExternalKey){
			$this->tpl->assign_block_vars('external_row', array(
				'ID'	=> $strExternalKey,
				'NAME'	=> $this->user->lang('plugin_statistics_'.$strExternalKey),	
			));
		}
		
		// -- EQDKP ---------------------------------------------------------------
		$this->core->set_vars(array(
			'page_title'	=> $this->user->lang('st_view_statistics'),
			'template_path'	=> $this->pm->get_data('statistics', 'template_path'),
			'template_file'	=> 'admin/statistics.html',
				'page_path'			=> [
						['title'=>$this->user->lang('menu_admin_panel'), 'url'=>$this->root_path.'admin/'.$this->SID],
						['title'=>$this->user->lang('st_view_statistics'), 'url'=>' '],
				],
			'display'		=> true
		));
	}
	
	private function timeformat($time){
		$y = $this->time->date("Y", $time);
		$d = $this->time->date("d", $time);
		$m = $this->time->date("m", $time);
		
		$date = $this->time->mktime(0, 0, 0, $m, $d, $y);
		return $date;
	}
}
registry::register('StatisticsViewer');
?>
