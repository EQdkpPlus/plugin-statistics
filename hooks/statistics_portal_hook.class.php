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
	header('HTTP/1.0 404 Not Found');exit;
}


/*+----------------------------------------------------------------------------
  | statistics_portal_hook
  +--------------------------------------------------------------------------*/
if (!class_exists('statistics_portal_hook')){
	class statistics_portal_hook extends gen_class{

		/**
		* hook_portal
		* Do the hook 'portal'
		*
		* @return array
		*/
		public function portal(){
			//Get date
			$y = $this->time->date("Y");
			$d = $this->time->date("d");
			$m = $this->time->date("m");
			$date = $this->time->mktime(0, 0, 0, $m, $d, $y);
			
			//Return if bot
			if($this->env->is_bot($this->user->data['session_browser'])) return;
			
			//Update visits
			if(!isset($this->user->data['session_vars']['visitCounted'])){
				$sql = "INSERT	INTO __plugin_statistics
					(dateID, visits)
				VALUES	(?, 1)
				ON DUPLICATE KEY UPDATE
					visits=visits+1";
				
				$this->db->prepare($sql)->execute($date);
				
				//Update Session Var
				$this->user->setSessionVar('visitCounted', 1);
			}
			
			//Update clicks
			$sql = "INSERT	INTO __plugin_statistics
				(dateID, clicks)
			VALUES	(?, 1)
			ON DUPLICATE KEY UPDATE
				clicks=clicks+1";
			
			$this->db->prepare($sql)->execute($date);
		}
	}
}
?>