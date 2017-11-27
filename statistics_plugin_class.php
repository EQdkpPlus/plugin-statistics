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

if (!defined('EQDKP_INC')){
	header('HTTP/1.0 404 Not Found'); exit;
}


/*+----------------------------------------------------------------------------
  | statistics
  +--------------------------------------------------------------------------*/
class statistics extends plugin_generic{

	public $version		= '0.2.1';
	public $build		= '1';
	public $copyright	= 'GodMod';
	public $vstatus		= 'Beta';

	protected static $apiLevel = 23;

	/**
	* Constructor
	* Initialize all informations for installing/uninstalling plugin
	*/
	public function __construct(){
		parent::__construct();

		$this->add_data(array (
			'name'				=> 'Statistics',
			'code'				=> 'statistics',
			'path'				=> 'statistics',
			'template_path'		=> 'plugins/statistics/templates/',
			'icon'				=> 'fa-line-chart',
			'version'			=> $this->version,
			'author'			=> $this->copyright,
			'description'		=> $this->user->lang('st_short_desc'),
			'long_description'	=> $this->user->lang('st_long_desc'),
			'homepage'			=> EQDKP_PROJECT_URL,
			'manuallink'		=> false,
			'plus_version'		=> '2.3',
			'build'				=> $this->build,
		));

		$this->add_dependency(array(
			'plus_version'		=> '2.3'
		));

		// -- Register our permissions ------------------------
		// permissions: 'a'=admins, 'u'=user
		// ('a'/'u', Permission-Name, Enable? 'Y'/'N', Language string, array of user-group-ids that should have this permission)
		// Groups: 2 = Super-Admin, 3 = Admin, 4 = Member
		$this->add_permission('a', 'manage',	'N', $this->user->lang('view'),	array(2,3));

		// -- Menu --------------------------------------------
		$this->add_menu('admin', $this->gen_admin_menu());
		
		$this->add_pdh_write_module('statistics_plugin');

		// -- PDH Modules -------------------------------------
		//$this->add_pdh_read_module('statistics');
		//$this->add_pdh_write_module('statistics');
		
		$this->add_hook('portal', 'statistics_portal_hook', 'portal');
		
		$this->add_portal_module('statistics');
	}

	/**
	* pre_install
	* Define Installation
	*/
	public function pre_install(){
		// include SQL and default configuration data for installation
		include($this->root_path.'plugins/statistics/includes/sql.php');

		// define installation
		for ($i = 1; $i <= count($statisticsSQL['install']); $i++)
			$this->add_sql(SQL_INSTALL, $statisticsSQL['install'][$i]);
	}

	/**
	* pre_uninstall
	* Define uninstallation
	*/
	public function pre_uninstall(){
		// include SQL data for uninstallation
		include($this->root_path.'plugins/statistics/includes/sql.php');

		for ($i = 1; $i <= count($statisticsSQL['uninstall']); $i++)
			$this->add_sql(SQL_UNINSTALL, $statisticsSQL['uninstall'][$i]);
	}

	/**
	* post_uninstall
	* Define Post Uninstall
	*/
	public function post_uninstall(){
		// clear cache
		$this->pdc->del('pdh_statistics_table');
	}


	/**
	* gen_admin_menu
	* Generate the Admin Menu
	*/
	private function gen_admin_menu(){
		$admin_menu = array (array(
			'name' => $this->user->lang('statistics'),
			'icon' => 'fa-line-chart',
			1 => array (
				'link'	=> 'plugins/statistics/admin/statistics.php'.$this->SID,
				'text'	=> $this->user->lang('st_view_statistics'),
				'check'	=> 'a_statistics_manage',
				'icon'	=> 'fa-line-chart'
			),
		));

		return $admin_menu;
	}
}
?>
