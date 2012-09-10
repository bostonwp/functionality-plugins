<?php

class PluginSnapshot extends WPConfigSnapshotModule
{
    public function label()
    {
        return "Active Plugin";
    }
    
    public function type()
    {
        return "active_plugin";
    }
    
    public function take_snapshot()
    {
        return get_option('active_plugins');
    }
    
    public function  restore_snapshot($value)
    {
        update_option('active_plugins', $value);
    }
    
    public function deprecated_check($vco)
    {
        global $wp_version;
        $vco->current_version($wp_version);
        $vco->supported_version('3.4.1');
        $vco->deprecated_message(
            'Notice: The Plugin Snapshot framework is only supported to WP version 3.4.1. Please check for updates.');
        $vco->set_block_ui(false);
        return $vco;
    }
}