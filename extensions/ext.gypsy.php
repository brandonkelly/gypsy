<?php

/**
 * Gypsy
 *
 * This extension enables Custom Fields to only display on specified weblogs.
 *
 * The concept for this extension comes from Matt Weinberg (EE Forums user slapshotw).
 *
 * @package   Gypsy
 * @author    Brandon Kelly <me@brandon-kelly.com>
 * @link      http://brandon-kelly.com/apps/gypsy/
 * @copyright Copyright (c) 2008 Brandon Kelly
 * @license   http://creativecommons.org/licenses/by-sa/3.0/   Attribution-Share Alike 3.0 Unported
 */
class Gypsy
{
	/**
	 * Extension Settings
	 *
	 * @var array
	 */
	var $settings = array();

	/**
	 * Extension Name
	 *
	 * @var string
	 */
	var $name = 'Gypsy';
	
	/**
	 * Extension Class Name
	 *
	 * @var string
	 */
	var $class_name = 'Gypsy';
	
	/**
	 * Extension Version
	 *
	 * @var string
	 */
	var $version = '1.0.6';
	
	/**
	 * Extension Description
	 *
	 * @var string
	 */
	var $description = 'Free your custom fields from their field groups';
	
	/**
	 * Extension Settings Exist
	 *
	 * If set to 'y', a settings page will be shown in the Extensions Manager
	 *
	 * @var string
	 */
	var $settings_exist = 'y';
	
	/**
	 * Documentation URL
	 *
	 * @var string
	 */
	var $docs_url = 'http://brandon-kelly.com/apps/gypsy/?utm_campaign=gypsy_em';




	/**
	 * Extension Constructor
	 *
	 * @param array   $settings
	 * @since version 1.0.0
	 */
	function Gypsy($settings=array())
	{
		$this->settings = $this->get_site_settings($settings);
	}



	/**
	 * Get All Settings
	 *
	 * @return array   All extension settings
	 * @since  version 1.0.0
	 */
	function get_all_settings()
	{
		global $DB;
		
		$query = $DB->query("SELECT settings
		                     FROM exp_extensions
		                     WHERE class = '{$this->class_name}'
		                       AND settings != ''
		                     LIMIT 1");
		
		return $query->num_rows
			? unserialize($query->row['settings'])
			: array();
	}
	
	
	
	/**
	 * Get Default Settings
	 * 
	 * @return array   Default settings for site
	 * @since 1.0.1
	 */
	function get_default_settings()
	{
		$settings = array(
			'check_for_extension_updates' => 'y'
		);

		return $settings;
	}



	/**
	 * Get Site Settings
	 *
	 * @param  array   $settings   Current extension settings (not site-specific)
	 * @return array               Site-specific extension settings
	 * @since  version 1.1.0
	 */
	function get_site_settings($settings=array())
	{
		global $PREFS;
		
		$site_settings = $this->get_default_settings();
		
		$site_id = $PREFS->ini('site_id');
		if (isset($settings[$site_id]))
		{
			$site_settings = array_merge($site_settings, $settings[$site_id]);
		}

		return $site_settings;
	}



	/**
	 * Settings Form
	 *
	 * Construct the custom settings form.
	 *
	 * Look and feel based on LG Addon Updater's settings form.
	 *
	 * @param  array   $current   Current extension settings (not site-specific)
	 * @see    http://expressionengine.com/docs/development/extensions.html#settings
	 * @author Leevi Graham <http://leevigraham.com>
	 * @since  version 1.0.0
	 */
	function settings_form($current)
	{
	    global $DB, $DSP, $LANG, $IN, $PREFS;
	    
	    $current = $this->get_site_settings($current);
		
	    
	    // Form Header
	    
	    $DSP->crumbline = TRUE;
	    
	    $DSP->title  = $LANG->line('extension_settings');
	    $DSP->crumb  = $DSP->anchor(BASE.AMP.'C=admin'.AMP.'area=utilities', $LANG->line('utilities')).
	                               $DSP->crumb_item($DSP->anchor(BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=extensions_manager', $LANG->line('extensions_manager')));
	    $DSP->crumb .= $DSP->crumb_item($this->name);
	    
	    $DSP->right_crumb($LANG->line('disable_extension'), BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=toggle_extension_confirm'.AMP.'which=disable'.AMP.'name='.$IN->GBL('name'));
	    
	    $DSP->body = '';
	    
	    // Donations button
	    $DSP->body .= '<div style="float:right;">'
	                . '<a style="display:block; margin:-2px 10px 0 0; padding:5px 0 5px 70px; width:190px; height:15px; font-size:12px; line-height:15px;'
	                . ' background:url(http://brandon-kelly.com/images/shared/donations.png) no-repeat 0 0; color:#000; font-weight:bold;"'
	                . ' href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2181794" target="_blank">'
	                . $LANG->line('donate')
	                . '</a>'
	                . '</div>';
	    
	    $DSP->body .= "<h1>{$this->name} <small>{$this->version}</small></h1>";
	    
	    $DSP->body .= $DSP->form_open(
	                                array(
	                                        'action' => 'C=admin'.AMP.'M=utilities'.AMP.'P=save_extension_settings',
	                                        'name'   => 'settings_example',
	                                        'id'     => 'settings_example'
	                                    ),
	                                array('name' => strtolower($this->class_name))
	                            );
	    
	    
	    // Updates Setting

		$lgau_query = $DB->query("SELECT class
		                          FROM exp_extensions
		                          WHERE class = 'Lg_addon_updater_ext'
		                            AND enabled = 'y'
		                          LIMIT 1");
		$lgau_enabled = $lgau_query->num_rows ? TRUE : FALSE;
		$check_for_extension_updates = ($lgau_enabled AND $current['check_for_extension_updates'] == 'y') ? TRUE : FALSE;

		$DSP->body .= $DSP->table_open(
		                                   array(
		                                       'class'  => 'tableBorder',
		                                       'border' => '0',
		                                       'style' => 'margin-top:18px; width:100%'
		                                   )
		                               )

		            . $DSP->tr()
		            . $DSP->td('tableHeading', '', '2')
		            . $LANG->line("check_for_extension_updates_title")
		            . $DSP->td_c()
		            . $DSP->tr_c()

		            . $DSP->tr()
		            . $DSP->td('', '', '2')
		            . '<div class="box" style="border-width:0 0 1px 0; margin:0; padding:10px 5px"><p>'.$LANG->line('check_for_extension_updates_info').'</p></div>'
		            . $DSP->td_c()
		            . $DSP->tr_c()

		            . $DSP->tr()
		            . $DSP->td('tableCellOne', '60%')
		            . $DSP->qdiv('defaultBold', $LANG->line("check_for_extension_updates_label"))
		            . $DSP->td_c()

		            . $DSP->td('tableCellOne')
		            . '<select name="check_for_extension_updates"'.($lgau_enabled ? '' : ' disabled="disabled"').'>'
		            . $DSP->input_select_option('y', $LANG->line('yes'), ($current['check_for_extension_updates'] == 'y' ? 'y' : ''))
		            . $DSP->input_select_option('n', $LANG->line('no'),  ($current['check_for_extension_updates'] != 'y' ? 'y' : ''))
		            . $DSP->input_select_footer()
		            . ($lgau_enabled ? '' : NBS.NBS.NBS.$LANG->line('check_for_extension_updates_nolgau'))
		            . $DSP->td_c()
		            . $DSP->tr_c()

		            . $DSP->table_c();
	    
	    
	    // Close Form
	    
	    $DSP->body .= $DSP->qdiv('itemWrapperTop', $DSP->input_submit())
					. $DSP->form_c();
	}



	/**
	 * Save Settings
	 *
	 * @since version 1.0.0
	 */
	function save_settings()
	{
		global $DB, $PREFS;
		
		
		$settings = $this->get_all_settings();
		$current = $this->get_site_settings($settings);
		
		// Save new settings
		$settings[$PREFS->ini('site_id')] = $this->settings = array(
			'check_for_extension_updates' => $_POST['check_for_extension_updates']
		);
		
		$DB->query("UPDATE exp_extensions
		            SET settings = '".addslashes(serialize($settings))."'
		            WHERE class = '{$this->class_name}'");
	}



	/**
	 * Activate Extension
	 *
	 * Resets all Gypsy exp_extensions rows
	 *
	 * @since version 1.0.0
	 */
	function activate_extension()
	{
		global $DB;
		
		
		// Get settings
		$settings = $this->get_all_settings();
		
		
		// Delete old hooks
		$DB->query("DELETE FROM exp_extensions
		            WHERE class = '{$this->class_name}'");
		
		
		// Add new extensions
		$ext_template = array(
			'class'    => $this->class_name,
			'settings' => $settings ? addslashes(serialize($settings)) : '',
			'priority' => 10,
			'version'  => $this->version,
			'enabled'  => 'y'
		);
		
		$extensions = array(
			// LG Addon Updater
			array('hook'=>'lg_addon_update_register_source',    'method'=>'register_my_addon_source'),
			array('hook'=>'lg_addon_update_register_addon',     'method'=>'register_my_addon_id'),

			// Admin > Field Groups
			array('hook'=>'publish_admin_edit_field_extra_row', 'method'=>'edit_custom_field'),
			array('hook'=>'sessions_start',                     'method'=>'save_custom_field'),

			// Publish / Edit
			array('hook'=>'publish_form_weblog_preferences',    'method'=>'get_weblog_id'),
			array('hook'=>'publish_form_field_query',           'method'=>'get_fields')
		);
		
		foreach($extensions as $extension)
		{
			$ext = array_merge($ext_template, $extension);
			$DB->query($DB->insert_string('exp_extensions', $ext));
		}
		
		
		// Add field_is_gypsy to exp_weblog_fields
		$query = $DB->query("SHOW COLUMNS FROM `{$DB->prefix}weblog_fields` LIKE 'field_is_gypsy'");
		if ( ! $query->num_rows)
		{
			$DB->query("ALTER TABLE {$DB->prefix}weblog_fields ADD COLUMN field_is_gypsy CHAR(1) NOT NULL DEFAULT 'n'");
		}
		
		// Add gypsy_weblogs to exp_weblog_fields
		$query = $DB->query("SHOW COLUMNS FROM `{$DB->prefix}weblog_fields` LIKE 'gypsy_weblogs'");
		if ( ! $query->num_rows)
		{
			$DB->query("ALTER TABLE {$DB->prefix}weblog_fields ADD COLUMN gypsy_weblogs text NOT NULL DEFAULT ''");
		}
	}



	/**
	 * Update Extension
	 *
	 * @param string   $current   Previous installed version of the extension
	 * @since version 1.0.0
	 */
	function update_extension($current='')
	{
		global $DB;

		
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
		
		
		if ($current < '1.0.6')
		{
			$this->activate_extension();
			return;
		}
		
		$DB->query("UPDATE exp_extensions
		            SET version = '".$DB->escape_str($this->version)."'
		            WHERE class = '{$this->class_name}'");
	}



	/**
	 * Disable Extension
	 *
	 * @since version 1.0.0
	 */
	function disable_extension()
	{
		global $DB;

		$DB->query("UPDATE exp_extensions
		            SET enabled='n'
		            WHERE class='{$this->class_name}'");
	}



	/**
	 * Register a New Addon Source
	 *
	 * @param  array   $sources   The existing sources
	 * @return array              The new source list
	 * @see    http://leevigraham.com/cms-customisation/expressionengine/lg-addon-updater/
	 * @author Leevi Graham <http://leevigraham.com>
	 * @since  version 1.0.0
	 */
	function register_my_addon_source($sources)
	{
		global $EXT;
		
	    // Check if we're not the only one using this hook
	    if($EXT->last_call !== FALSE)
	        $sources = $EXT->last_call;
	
	    // add a new source
	    // must be in the following format:
	    /*
	    <versions>
	        <addon id='LG Addon Updater' version='2.0.0' last_updated="1218852797" docs_url="http://leevigraham.com/" />
	    </versions>
	    */
	    if(( ! isset($this->settings['check_for_extension_updates'])) OR $this->settings['check_for_extension_updates'] == 'y')
	    {
	        $sources[] = 'http://brandon-kelly.com/downloads/versions.xml';
	    }
	    return $sources;
	
	}



	/**
	 * Register a New Addon
	 *
	 * @param  array   $addons   The existing sources
	 * @return array             The new addon list
	 * @see    http://leevigraham.com/cms-customisation/expressionengine/lg-addon-updater/
	 * @author Leevi Graham <http://leevigraham.com>
	 * @since  version 1.0.0
	 */
	function register_my_addon_id($addons)
	{
		global $EXT;
		
	    // Check if we're not the only one using this hook
	    if($EXT->last_call !== FALSE)
	        $addons = $EXT->last_call;
	
	    // add a new addon
	    // the key must match the id attribute in the source xml
	    // the value must be the addons current version
	    if(( ! isset($this->settings['check_for_extension_updates'])) OR $this->settings['check_for_extension_updates'] == 'y')
	    {
	        $addons[$this->class_name] = $this->version;
	    }
	    return $addons;
	}



	/**
	 * Edit Custom Field
	 *
	 * @param  array    $data   The data about this field from the database
	 * @param  string   $r      The current contents of the page
	 * @return string           Modified $r
	 * @see    http://expressionengine.com/developers/extension_hooks/publish_admin_edit_field_extra_row/
	 * @since  version 1.0.0
	 */
	function edit_custom_field($data, $r)
	{
		global $EXT, $LANG, $DB, $PREFS, $DSP;
		
		// Check if we're not the only one using this hook
		if($EXT->last_call !== false)
		{
			$r = $EXT->last_call;
		}
		
		
		// Get the weblogs using this field group
		$weblogs = $DB->query("SELECT weblog_id, blog_title
		                       FROM exp_weblogs
		                       WHERE site_id = '".$PREFS->ini('site_id')."'
		                       ORDER BY blog_title");
		if ( ! $weblogs->num_rows)
		{
			return $r;
		}
		
		
		// Find "Show this field by default?" row
		$pattern = "<tr>.*[\s]+" .
		           "<td.+class='(\w+)'.*[\s]+" .
		           "<div.+class='defaultBold'.+[\s]+" .
		           "<div.+class='itemWrapper'.+[\s]+" .
		           "<\/td>.*[\s]+" .
		           "<td.+[\s]+" .
		           ".+<input.+name='field_is_hidden'*";
		preg_match("/{$pattern}/i", $r, $matches, PREG_OFFSET_CAPTURE);
		$offset = $matches[0][1];
		$class = $matches[1][0];
		$r_top = substr($r, 0, $offset);
		$r_bottom = substr($r, $offset);
		
		
		// ---------------------
		// Create Gypsy row
		// ---------------------
		
		// Load lang.gypsy.php
		$LANG->fetch_language_file('gypsy');
		
		$field_is_gypsy = ($data['field_is_gypsy'] == 'y') ? TRUE : FALSE;
		$indent = NBS.NBS.NBS.'<img src="'.PATH_CP_IMG.'cat_marker.gif" border="0"  width="18" height="14" alt="" title="" />'.NBS.NBS;
		$gypsy_weblogs = $data['gypsy_weblogs'] ? array_filter(explode(' ', $data['gypsy_weblogs'])) : array();
		
		// Assemble the first row
		$r_top .= $DSP->tr()
		        . $DSP->td($class, '50%')
		        . $DSP->qdiv('defaultBold', $LANG->line('field_is_gypsy_label'))
		        . $DSP->qdiv('itemWrapper', $LANG->line('field_is_gypsy_info'))
		        . $DSP->td_c()
		        . $DSP->td($class, '50%')
		        . $LANG->line('yes').NBS
		        . $DSP->input_radio('field_is_gypsy', 'y', ($field_is_gypsy ? 1 : 0), 'onclick="document.getElementById(\'gypsy_weblogs\').style.display=\'table-row\';"')
		        . NBS.NBS.NBS.$LANG->line('no').NBS
		        . $DSP->input_radio('field_is_gypsy', 'n', ($field_is_gypsy ? 0 : 1), 'onclick="document.getElementById(\'gypsy_weblogs\').style.display=\'none\';"')
		        . $DSP->td_c()
		        . $DSP->tr_c();
		
		// Assemble the second row
		$r_top .= '<tr id="gypsy_weblogs"' . ( ! $field_is_gypsy ? ' style="display:none;">' : '')
		        . '<td class="'.$class.'" style="width:50%; vertical-align:top;">'
		        . $DSP->qdiv('defaultBold', $indent.$LANG->line('gypsy_weblogs_label'))
		        . $DSP->td_c()
		        . $DSP->td($class, '50%')
		        . $DSP->input_select_header('gypsy_weblogs[]', 'y', ($weblogs->num_rows > 20 ? 20 : $weblogs->num_rows));
		foreach($weblogs->result as $weblog)
		{
			$selected = (array_search($weblog['weblog_id'], $gypsy_weblogs) !== FALSE) ? 1 : 0;
			$r_top .= $DSP->input_select_option($weblog['weblog_id'], $weblog['blog_title'], $selected);
		}
		$r_top .= $DSP->input_select_footer()
		        . $DSP->td_c()
		        . $DSP->tr_c();
		
		
		// Swap remaining row colors
		$r_bottom = str_replace(array('tableCellOne',  'tableCellTwo', 'tableCellOne_'),
		                        array('tableCellOne_', 'tableCellOne', 'tableCellTwo'), $r_bottom);
		
		
		// Return re-assembled $r
		return $r_top.$r_bottom;
	}



	/**
	 * Save Custom Field
	 *
	 * @see   http://expressionengine.com/developers/extension_hooks/sessions_start/
	 * @since version 1.0.0
	 */
	function save_custom_field()
	{
		if (isset($_POST['field_is_gypsy']))
		{
			$gypsy_weblogs = array();
			if ($_POST['field_is_gypsy'] == 'y' AND isset($_POST['gypsy_weblogs']))
			{
				foreach($_POST['gypsy_weblogs'] as $i=>$weblog)
				{
					$gypsy_weblogs[] = $weblog;
				}
			}
			$_POST['gypsy_weblogs'] = ' '.addslashes(implode(' ', $gypsy_weblogs)).' ';
			
			
		}
		elseif (isset($_POST['gypsy_weblogs']))
		{
			unset($_POST['gypsy_weblogs']);
		}
		
		$i = 0;
		while(isset($_POST["gypsy_weblogs_{$i}"]))
		{
			unset($_POST["gypsy_weblogs_{$i}"]);
			$i++;
		}
	}



	/**
	 * Get Weblog ID
	 *
	 * @param  array    $row   Row of results from database for the weblog of this entry form
	 * @return array           unmodified $row
	 * @see    http://expressionengine.com/developers/extension_hooks/publish_form_weblog_preferences/
	 * @since  version 1.0.0
	 */
	function get_weblog_id($row)
	{
		global $EXT, $GYPSY_WEBLOG_ID;
		
		
		$GYPSY_WEBLOG_ID = $row['weblog_id'];
		
		
		return ($EXT->last_call !== FALSE)
			? $EXT->last_call
			: $row;
	}



	/**
	 * Get Fields
	 *
	 * @param  array    $obj           the Publish class object
	 * @param  string   $field_group   the custom field group assigned to this weblog
	 * @return object                  the DB object
	 * @see    http://expressionengine.com/developers/extension_hooks/publish_form_field_query/
	 * @since  version 1.0.0
	 */
	function get_fields($obj, $field_group)
	{
		global $DB, $DSP, $EXT, $LANG, $GYPSY_WEBLOG_ID;
		
		
		// Get fields
		if ($EXT->last_call !== FALSE)
		{
	    	$field_query = $EXT->last_call;
	    }
	    else
	    {
			$field_query = $DB->query("SELECT *
			                           FROM exp_weblog_fields
			                           WHERE group_id = '{$field_group}'
			                             AND field_is_gypsy != 'y'
			                           ORDER BY field_order");
		}
		
		
		$result = array();
		
		
		// Filter out Gypsy fields
		
		foreach($field_query->result as $field)
		{
			if (( ! $field['field_is_gypsy']) OR ($field['field_is_gypsy'] != 'y'))
			{
				$result[] = $field;
			}
		}
		
		
		// Insert all assigned Gypsy rows
		$gypsy_query = $DB->query("SELECT *
		                           FROM exp_weblog_fields
		                           WHERE field_is_gypsy = 'y'
		                             AND gypsy_weblogs LIKE '% {$GYPSY_WEBLOG_ID} %'");
		if ($gypsy_query->num_rows)
		{
			$result = array_merge($result, $gypsy_query->result);
			
			// Sort the array by field_order
			function cmp($a, $b)
			{
				if ($a['field_order'] == $b['field_order']) return 0;
				return ($a['field_order'] < $b['field_order']) ? -1 : 1;
			}
			usort($result, 'cmp');
		}
		
		
		$field_query->result = $result;
		$field_query->num_rows = count($result);
		$field_query->row = $field_query->num_rows ? $result[0] : array();
		
		
		return $field_query;
		
	}
}

?>
