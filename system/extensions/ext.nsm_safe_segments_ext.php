<?php
/**
* LG Multi Language extension file
* 
* This file must be placed in the
* /system/extensions/ folder in your ExpressionEngine installation.
*
* @package NsmSafeSegments
* @version 1.1.0
* @author Leevi Graham <http://leevigraham.com>
* @copyright 2007
* @see http://leevigraham.com/cms-customisation/expressionengine/addon/lg-mult-lang/
* @copyright Copyright (c) 2007-2008 Leevi Graham
* @license http://leevigraham.com/cms-customisation/commercial-license-agreement
*/

if ( ! defined('EXT')) exit('Invalid file request');

/**
* This extension add multi language support to EE front end
*
* @package NsmSafeSegments
* @version 1.1.0
* @author Leevi Graham <http://leevigraham.com>
* @copyright 2007
* @see http://leevigraham.com/cms-customisation/expressionengine/addon/lg-mult-lang/
* @copyright Copyright (c) 2007-2008 Leevi Graham
* @license http://leevigraham.com/cms-customisation/commercial-license-agreement
*
*/
class Nsm_safe_segments_ext {

	/**
	* Extension settings
	* @var array
	*/
	var $settings			= array();

	/**
	* Extension name
	* @var string
	*/
	var $name				= 'NSM Safe Segments';

	/**
	* Extension version
	* @var string
	*/
	var $version			= '0.0.0';

	/**
	* Extension description
	* @var string
	*/
	var $description		= '';

	/**
	* If $settings_exist = 'y' then a settings page will be shown in the ExpressionEngine admin
	* @var string
	*/
	var $settings_exist 	= 'y';

	/**
	* Link to extension documentation
	* @var string
	*/
	var $docs_url			= 'http://leevigraham.com/cms-customisation/expressionengine/addon/nsm-safe-segments/';


	/**
	* PHP4 Constructor
	*
	* @see __construct()
	*/
	function Nsm_safe_segments($settings='')
	{
		$this->__construct($settings);
	}

	/**
	* PHP 5 Constructor
	*
	* @param	array|string $settings Extension settings associative array or an empty string
	*/
	function __construct($settings='')
	{
		$this->settings = $settings;
	}

	/**
	* Configuration for the extension settings page
	*
	* @return	array The settings array
	*/
	function settings()
	{
		global $LANG;
		$settings = array();
		$settings['safe_segments'] = 'success|error';
		$settings['break_segments'] = '';
		$settings['break_categories'] = array('r', array('y' => "yes", 'n' => "no"), 'n');
		$settings['check_for_updates'] = array('r', array('y' => "yes", 'n' => "no"), 'y');
		return $settings;
	}

	/**
	* Checks the url to see if the last segment matches one of the languages defined in the extension settings.
	*/
	function sessions_start(&$obj)
	{
		global $IN, $PREFS, $SESS;
		$segments			= $this->settings['safe_segments'];
		$breaks				= (isset($this->settings['break_segments']) && $this->settings['break_segments']) ? explode("|", $this->settings['break_segments']) : false;
		if($this->settings['break_categories'] == 'y') {
			# we're supposed to break everything after the category key word
			
		}

		// if this is a page request
		if(REQ == "PAGE")
		{
			$dirty_array		= explode('/', substr($IN->URI, 1, -1));
			$clean_array		= array();				# contains URL segments
			$pulled_array		= array();				# contains ignored segments
			
			$break				= false;
			$dsid				= 0;					# dirty segment id
			
			
			foreach ($dirty_array as $segment) {
				if (!preg_match('#^('.$segments.')$#', $segment) && $break == false) {
					#segment is clean
					array_push($clean_array, $segment);
					$break = in_array($segment, $breaks);
				} else {
					#segment isn't clean
					array_push($pulled_array, $segment);
					++$i;
					$IN->global_vars["safe_segment_$i"] = $segment;
				}
			}
			# var_dump($dirty_array, $IN->global_vars);
			$IN->URI = (count($clean_array)) ? "/".implode('/', $clean_array)."/" : "/";
			# $IN->URI = preg_replace("#/(".$this->settings['safe_segments'].")/#", "/", $IN->URI);
			$IN->parse_qstr();
		}
	}



	/**
	* Activates the extension
	*
	* @return	bool Always TRUE
	*/
	function activate_extension()
	{
		global $DB;

		$hooks = array(
			'sessions_start'					=> 'sessions_start',
			'lg_addon_update_register_source'	=> 'lg_addon_update_register_source',
			'lg_addon_update_register_addon'	=> 'lg_addon_update_register_addon'
		);

		foreach ($hooks as $hook => $method)
		{
			$sql[] = $DB->insert_string( 'exp_extensions', 
											array('extension_id' 	=> '',
												'class'			=> get_class($this),
												'method'		=> $method,
												'hook'			=> $hook,
												'settings'		=> '',
												'priority'		=> 1,
												'version'		=> $this->version,
												'enabled'		=> "y"
											)
										);
		}

		// run all sql queries
		foreach ($sql as $query)
		{
			$DB->query($query);
		}
		return TRUE;
	}



	/**
	* Updates the extension
	*
	* If the existing version is below 1.2 then the update process changes some
	* method names. This may cause an error which can be resolved by reloading
	* the page.
	*
	* @param	string $current If installed the current version of the extension otherwise an empty string
	* @return	bool FALSE if the extension is not installed or is the current version
	*/
	function update_extension($current = '')
	{
		global $DB;

		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		// Introducing LG Auto Updater!!!!
		if ($current < '1.1.0')
		{
			// create two new hooks
			$hooks = array(
				'lg_addon_update_register_source'	=> 'lg_addon_update_register_source',
				'lg_addon_update_register_addon'	=> 'lg_addon_update_register_addon'
			);
			// for each of the new hooks
			foreach ($hooks as $hook => $method)
			{
				// build the sql
				$sql[] = $DB->insert_string( 'exp_extensions', 
												array('extension_id' 	=> '',
													'class'				=> get_class($this),
													'method'			=> $method,
													'hook'				=> $hook,
													'settings'			=> addslashes(serialize($this->settings)),
													'priority'			=> 10,
													'version'			=> $this->version,
													'enabled'			=> "y"
												)
											);
			}
		}

		$sql[] = "UPDATE exp_extensions SET version = '" . $DB->escape_str($this->version) . "' WHERE class = '" . get_class($this) . "'";

		// run all sql queries
		foreach ($sql as $query)
		{
			$DB->query($query);
		}
	}

	/**
	* Disables the extension the extension and deletes settings from DB
	*/
	function disable_extension()
	{
		global $DB;
		$DB->query("DELETE FROM exp_extensions WHERE class = '" . get_class($this) . "'");
	}
	
	/**
	* Register a new Addon Source
	*
	* @param	array $sources The existing sources
	* @return	array The new source list
	* @since 	Version 1.0.0
	*/
	function lg_addon_update_register_source($sources)
	{
		global $EXT;
		// -- Check if we're not the only one using this hook
		if($EXT->last_call !== FALSE)
			$sources = $EXT->last_call;

		// add a new source
		// must be in the following format:
		/*
		<versions>
			<addon id='LG Addon Updater' version='2.0.0' last_updated="1218852797" docs_url="http://leevigraham.com/" />
		</versions>
		*/
		if($this->settings['check_for_updates'] == 'y')
		{
			$sources[] = 'http://leevigraham.com/version-check/versions.xml';
		}

		return $sources;

	}

	/**
	* Register a new Addon
	*
	* @param	array $addons The existing sources
	* @return	array The new addon list
	* @since 	Version 1.0.0
	*/
	function lg_addon_update_register_addon($addons)
	{
		global $EXT;
		// -- Check if we're not the only one using this hook
		if($EXT->last_call !== FALSE)
			$addons = $EXT->last_call;

		// add a new addon
		// the key must match the id attribute in the source xml
		// the value must be the addons current version
		if($this->settings['check_for_updates'] == 'y')
		{
			$addons["LG Multi Language"] = $this->version;
		}

		return $addons;
	}
	
}

?>