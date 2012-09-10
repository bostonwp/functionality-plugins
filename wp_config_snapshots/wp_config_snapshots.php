<?php

/*
Plugin Name: WP Config Snapshots
Plugin URI: http://brianzeligson.com
Description: Framework to enable creation and use of settings snapshots in a WordPress installation
Author: Brian Zeligson
Version: 0.1
Author URI: http://brianzeligson.com
*/

do_action('config_snapshot_plugin_loaded');

class WPConfigSnapshotPlugin
{
    private static $_instance;
    private $_snapshot_modules;
    
    private function __construct()
    {
        $this->initialize_snapshot_modules();
        $this->require_dependencies();
        $this->add_actions();
    }
    
    public static function instance()
    {
        if (!isset(self::$_instance)) self::$_instance = new WPConfigSnapshotPlugin();
        return self::$_instance;
    }
    
    private function initialize_snapshot_modules()
    {
        $this->_snapshot_modules = array();
    }
    
    public function register_snapshot_module(WPConfigSnapshotModule $snapshot_module)
    {
        $this->_snapshot_modules[$snapshot_module->type()] = $snapshot_module;
    }
    
    public function snapshot_modules()
    {
        return $this->_snapshot_modules;
    }
    
    public function module_is_valid($type)
    {
        return (is_object($this->_snapshot_modules[$type]) and
                        get_parent_class($this->_snapshot_modules[$type]) === 'WPConfigSnapshotModule');
    }
    
    public function require_dependencies()
    {
        require_once(dirname(__FILE__).'/Stamp.php');
    }
    
    public function add_actions()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_js'));
        add_action('wp_ajax_add_wp_config_snapshot', array($this, 'take_snapshot_ajax_handler'));
        add_action('wp_ajax_restore_wp_config_snapshot', array($this, 'restore_snapshot_ajax_handler'));
    }
    
    public function enqueue_js()
    {
        wp_enqueue_script('snapshots_js', get_bloginfo('url').'/wp-content/plugins/wp_config_snapshots/js/snapshots.js', array('jquery'));
    }
    
    public function take_snapshot_ajax_handler()
    {
        $response = $this->take_snapshot($_POST['snapshot_type'], $_POST['snapshot_name']);
        die(json_encode($response));
    }
    
    public function clear_active_snapshot($snapshots)
    {
        if (!is_array($snapshots)) $snapshots = array();
        foreach($snapshots as $snapshot)
            if (get_class($snapshot) === 'WPConfigSnapshot') $snapshot->set_active(false);
        return $snapshots;
    }
    
    public function validate_snapshot_request($type=false, $name=false)
    {
        if (!isset($type) or !$this->module_is_valid($type))
            return new wpcsAjaxResponse('failed', 'Invalid snapshot type provided.');
        if (!isset($name)) return new wpcsAjaxResponse('failed', 'Invalid snapshot name provided.');
        return false;
    }
    
    public function take_snapshot($type=false, $name=false)
    {
        if ($invalid = $this->validate_snapshot_request($type, $name)) return $invalid;
        $option = get_option('wp_config_snapshots');
        $option[$type] = $this->clear_active_snapshot($option[$type]);
        $option[$type][$name] = new WPConfigSnapshot($name, $this->_snapshot_modules[$type]->take_snapshot(), true);
        update_option('wp_config_snapshots', $option);
        return new wpcsAjaxResponse('success');
    }
    
    public function restore_snapshot_ajax_handler()
    {
        $response = $this->restore_snapshot($_POST['snapshot_type'], $_POST['snapshot_name']);
        die(json_encode($response));
    }
    
    public function restore_snapshot($type=false, $name=false)
    {
        if ($invalid = $this->validate_snapshot_request($type, $name)) return $invalid;
        $option = get_option('wp_config_snapshots');
        if (!isset($option[$type]) or !isset($option[$type][$name]) or !is_callable(array($option[$type][$name], 'value')))
            return new wpcsAjaxResponse('failed', 'Could not load the specified snapshot from the database.');
        $this->_snapshot_modules[$type]->restore_snapshot($option[$type][$name]->value());
        $option[$type] = $this->clear_active_snapshot($option[$type]);
        $option[$type][$name]->set_active(true);
        update_option('wp_config_snapshots', $option);
        return new wpcsAjaxResponse('success');
    }
}

class wpcsAjaxResponse
{
    public $status, $message;
    
    public function __construct($status='', $message='')
    {
        $this->status = $status;
        $this->message = $message;
    }
}

add_action('plugins_loaded', array('WPConfigSnapshotPlugin', 'instance'));

class WPConfigSnapshotModule
{
    
    public function type()
    {
        return 'base_snapshot';
    }
    
    public function label()
    {
        return 'base snapshot';
    }
    
    public function ui_action_hook()
    {
        return 'default_ui_hook';
    }
    
    private function render_ui()
    {
        $output = new wpsStamp(wpsStamp::load(dirname(__FILE__).'/views/ui.tpl'));
        $option = get_option('wp_config_snapshots');
        $select_options = '';
        foreach($option[$this->type()] as $snapshot)
        {
            $selected = ($snapshot->is_active()) ? "selected='selected'" : '';
            $select_options .= "<option $selected value='".$snapshot->name()."'>".$snapshot->name()."</option>";
        }
        if (trim($select_options) !== '') $output = $output->replace('existing_snapshot_option', $select_options);
        $version_check = $this->deprecated_check(new WPConfigSnapshotVersionCheck());
        if ($version_check->is_deprecated())
            $output = $output->replace($version_check->deprecated_ui_block(), $version_check->deprecated_message());
        echo $output->replace('label', $this->label())->replace('type', $this->type());
    }
    
    public function call_render_ui()
    {
        return $this->render_ui();
    }

    public function deprecated_check(WPConfigSnapshotVersionCheck $version_check_object)
    {
        $version_check_object->current_version(1);
        $version_check_object->supported_version(1);
        $version_check_object->deprecated_message = '';
        return $version_check_object;
    }
    
    public function take_snapshot()
    {
        return array();
    }
    
    public function restore_snapshot($snapshot_data)
    {
        return;
    }
}

class WPConfigSnapshot
{
    private $_name, $_value, $_active;
    
    public function __construct($name, $value, $active=false)
    {
        $this->_name = $name;
        $this->_value = $value;
        $this->_active = $active;
    }
    
    public function name($val=false)
    {
        if (!$val) return $this->_name;
        $this->_name = $val;
    }
    
    public function value($val=false)
    {
        if (!$val) return $this->_value;
        $this->_value = $val;
    }
    
    public function is_active()
    {
        return $this->_active;
    }
    
    public function set_active($val)
    {
        $this->_active = $val;
    }
}

class WPConfigSnapshotVersionCheck
{
    private $_current_version, $_supported_version, $_deprecated_message, $_block_ui;
    
    public function current_version($version)
    {
        $this->_current_version = $version;
    }
    
    public function supported_version($version)
    {
        $this->_supported_version = $version;
    }
    
    public function set_block_ui($bool)
    {
        $this->_block_ui = $bool;
    }
    
    public function block_ui()
    {
        return $this->_block_ui;
    }
    
    public function deprecated_ui_block()
    {
        return ($this->_block_ui) ? 'ui_block' : 'dep_note';
    }
    
    public function deprecated_message($message=false)
    {
        if (!$message) return $this->_deprecated_message;
        $this->_deprecated_message = $message;
    }
    
    public function is_deprecated()
    {
        return ($this->_current_version === $this->_supported_version) ? '' : $this->_deprecated_message;
    }
}