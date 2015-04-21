<?php
/*	Project:	EQdkp-Plus
 *	Package:	MediaCenter Plugin
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
if ( !defined('EQDKP_INC') ){
	die('Do not access this file directly.');
}
				
if ( !class_exists( "pdh_w_statistics_plugin" ) ) {
	class pdh_w_statistics_plugin extends pdh_w_generic {
		
		public function insert($strType, $intValue=1){
			$y = $this->time->date("Y");
			$d = $this->time->date("d");
			$m = $this->time->date("m");
			$date = $this->time->mktime(0, 0, 0, $m, $d, $y);
			
			$sql = "INSERT	INTO __plugin_statistics_external
				(dateID, name, value)
			VALUES	(?, ?, ?)
			ON DUPLICATE KEY UPDATE
				value=value+?";
				
			$this->db->prepare($sql)->execute($date, $strType, $intValue, $intValue);
		}
			
	}//end class
}//end if
?>