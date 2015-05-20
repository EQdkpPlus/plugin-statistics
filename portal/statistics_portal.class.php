<?php
/*	Project:	EQdkp-Plus
 *	Package:	Statistics Plugin
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

if (!defined('EQDKP_INC')){
	header('HTTP/1.0 404 Not Found'); exit;
}

/*+----------------------------------------------------------------------------
  | statistics_portal
  +--------------------------------------------------------------------------*/
class statistics_portal extends portal_generic{

	/**
	* Portal path
	*/
	protected static $path = 'statistics';
	/**
	* Portal data
	*/
	protected static $data = array(
		'name'			=> 'Statistics Module',
		'version'		=> '0.1.0',
		'author'		=> 'GodMod',
		'description'	=> 'Displays visit statistics',
		'lang_prefix'	=> 'st_',
		'icon'			=> 'fa-line-chart',
		'contact'		=> EQDKP_PROJECT_URL,
	);
	
	protected static $apiLevel = 20;

	
	/**
	* Settings
	*/	
	public function get_settings($state){
		$settings = array(
				'view'	=> array(
					'type'		=> 'multiselect',
					'options'	=> array('today' => $this->user->lang('st_today'), 'total' => $this->user->lang('st_total'), 'records' => $this->user->lang('st_records')),
					'default'	=> array('today', 'total'),
				),
		);
		return $settings;
	}

	private $statisticsData = null;
	
	private $cachetime = 120;

	/**
	* output
	* Get the portal output
	*
	* @returns string
	*/
	public function output(){
		$this->getData();
		
		$arrView = $this->config('view');
		if(!$arrView || !is_array($arrView)) $arrView = array('today', 'total');
		
		$output = '<table class="table fullwidth colorswitch">';
		
		//Today and Total
		if(in_array('today', $arrView) || in_array('total', $arrView)){
			$output .= '<tr><th></th>';
			if(in_array('today', $arrView)) $output.= '<th>'.$this->user->lang('st_today').'</th>';
			if(in_array('total', $arrView)) $output.= '<th>'.$this->user->lang('st_total').'</th>';
			$output .= '</tr>';
			
			$output .= '<tr><td>'.$this->user->lang('st_visits').'</td>';
			if(in_array('today', $arrView)) $output.= '<td>'.$this->statisticsData['visits_today'].'</td>';
			if(in_array('total', $arrView)) $output.= '<td>'.$this->statisticsData['visits_total'].'</td>';
			$output .= '</tr>';

			$output .= '<tr><td>'.$this->user->lang('st_clicks').'</td>';
			if(in_array('today', $arrView)) $output.= '<td>'.$this->statisticsData['clicks_today'].'</td>';
			if(in_array('total', $arrView)) $output.= '<td>'.$this->statisticsData['clicks_total'].'</td>';
			$output .= '</tr>';
		}
		
		$output .= '</table>';
		
		//Records
		if(in_array('records', $arrView)){
			$output .= '<table class="table fullwidth colorswitch">';
			$output .= '<tr><th colspan="2">'.$this->user->lang('st_records').'</th></tr>';
			$output .= '<tr><td>'.$this->user->lang('st_visits').'</td><td>'.$this->statisticsData['visits_record']['visits'].' ('.$this->time->user_date($this->statisticsData['visits_record']['date']).')</td></tr>';
			$output .= '<tr><td>'.$this->user->lang('st_clicks').'</td><td>'.$this->statisticsData['clicks_record']['clicks'].' ('.$this->time->user_date($this->statisticsData['clicks_record']['date']).')</td></tr>';
			$output .= '</table>';
		}
		 
		return $output;
	}
	
	private function getData(){
		$this->statisticsData = $this->pdc->get('portal.module.statistics', false, true);
		
		if (!$this->statisticsData){
			//Get date
			$y = $this->time->date("Y");
			$d = $this->time->date("d");
			$m = $this->time->date("m");
			$date = $this->time->mktime(0, 0, 0, $m, $d, $y);
			
			//Visits and clicks today
			$objQuery = $this->db->prepare("SELECT * FROM __plugin_statistics WHERE dateID=?")->execute($date);
			if($objQuery){
				$arrRow = $objQuery->fetchAssoc();
				$this->statisticsData['visits_today'] = $arrRow['visits'];
				$this->statisticsData['clicks_today'] = $arrRow['clicks'];
			}
			
			//Clicks and visits total
			$objQuery = $this->db->prepare("SELECT SUM(visits) as total_visits, SUM(clicks) as total_clicks FROM __plugin_statistics")->execute();
			if($objQuery){
				$arrRow = $objQuery->fetchAssoc();
				$this->statisticsData['visits_total'] = $arrRow['total_visits'];
				$this->statisticsData['clicks_total'] = $arrRow['total_clicks'];
			}
			
			//Record clicks
			$objQuery = $this->db->query("SELECT * FROM __plugin_statistics WHERE visits = (SELECT  MAX(visits) FROM __plugin_statistics) ORDER BY dateID DESC Limit 1;");
			if($objQuery){
				$arrRow = $objQuery->fetchAssoc();
				$this->statisticsData['visits_record'] = array(
					'visits' => $arrRow['visits'],
					'date'	 => $arrRow['dateID'],
				);
			}
			
			//Record visits
			$objQuery = $this->db->query("SELECT * FROM __plugin_statistics WHERE clicks = (SELECT  MAX(clicks) FROM __plugin_statistics) ORDER BY dateID DESC Limit 1;");
			if($objQuery){
				$arrRow = $objQuery->fetchAssoc();
				$this->statisticsData['clicks_record'] = array(
					'clicks' => $arrRow['clicks'],
					'date'	 => $arrRow['dateID'],
				);
			}
		
			$this->pdc->put('portal.module.statistics', $this->statisticsData, $this->cachetime, false, true);
				
		}
	}
}

?>