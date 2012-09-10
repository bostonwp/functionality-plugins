<?php

/*
Plugin Name: WP Plugin Snapshots
Plugin URI: http://brianzeligson.com
Description: Save and restore snapshots of active plugin configurations
Author: Brian Zeligson
Version: 0.1
Author URI: http://brianzeligson.com
*/

class PluginSnapshotPlugin
{
    public static function initialize()
    {
        $p = WPConfigSnapshotPlugin::instance();
        require_once(dirname(__FILE__).'/PluginSnapshot.php');
        $ps = new PluginSnapshot();
        add_action('pre_current_active_plugins', array($ps, 'call_render_ui'));
        $p->register_snapshot_module($ps);
    }
}

add_action('config_snapshot_plugin_loaded', array('PluginSnapshotPlugin', 'initialize'));