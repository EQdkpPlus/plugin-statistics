<?php
/*	Project:	EQdkp-Plus
 *	Package:	RaidLogImport Plugin
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

if(!defined('EQDKP_INC')) {
	header('HTTP/1.0 Not Found');
	exit;
}

include_once(registry::get_const('root_path').'maintenance/includes/sql_update_task.class.php');

if (!class_exists('update_statistics_020')) {
class update_statistics_020 extends sql_update_task {
	public $author		= 'GodMod';
	public $version		= '0.2.0';
	public $name		= 'Statistics 0.2.0 Update';
	public $type		= 'plugin_update';
	public $plugin_path	= 'statistics';
	
	private $data		= array();
	
	public static function __shortcuts() {
		$shortcuts = array('config');
		return array_merge(parent::__shortcuts(), $shortcuts);
	}
	
	// init language
	public $langs = array(
		'english' => array(
			'update_statistics_020' => 'Statistics 0.2.0 Update Package',
			'update_function' 		=> 'Add Table',
		),
		'german' => array(
			'update_statistics_020' => 'Statistics 0.2.0 Update Package',
			'update_function' 		=> 'Add Table',
		),
	);
	
	public function update_function() {
		$this->db->query('CREATE TABLE IF NOT EXISTS `__plugin_statistics_external` (
				dateID INT(10) UNSIGNED NOT NULL DEFAULT 0,
				value INT(10) UNSIGNED NOT NULL DEFAULT 0,
				name VARCHAR(50) NOT NULL DEFAULT \'\',
				PRIMARY KEY (dateID, name)
			) DEFAULT CHARSET=utf8 COLLATE=utf8_bin;');
		
		
		return true;
	}
}
}

?>