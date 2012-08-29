<?php
/*
Plugin Name: BWP Cache
Plugin URI: https://github.com/BostonWP/functionality-plugins
Description: Provides a mechanism for caching queried data to be accessed from separate parts of WP codebase
Version: 0.1
Author: Brian Zeligson
Author URI: http://brianzeligson.com
License: GPL2
*/

/*
 *
 * Example usage:
 *  $data = 'string';
 *  bwpCache::instance()->set('data', $data);
 *
 *   // laterfrom widget, sidebar, footer, etc
 *  $data = (bwpCache::instance()->get('data')) ?: 'default';
 *  $data === 'string' //true
 *
 *  Available hooks/filters:
 *      set_bwp_cache - as filter before setting cache. Accepts key name for cache data as second argument
 *      set_bwp_cache - as action before setting cache. Accepts filtered data and key name for cache data as arguments.
 *
 *      get_bwp_cache - as filter before returning cached data. Accepts key name for cache data as second argument
 *      get_bwp_cache - as action before returning cached data. Accepts filtered data and key name for cache data as arguments.
 */


class bwpCache
{
    private $_cache = array();
    private static $_instance;
    
    private function __construct()
    {
        
    }
    
    public function instance()
    {
        if (!isset(self::$_instance)) self::$_instance = new bwpCache();
        return self::$_instance;
    }
    
    public function set($name, $data)
    {
        $data = apply_filters('set_bwp_cache', $data, $name);
        do_action('set_bwp_cache', $data, $name);
        $this->_cache[$name] = $data;
    }
    
    public function get($name)
    {
        if (!array_key_exists($name, $this->_cache[$name])) return false;
        $data = apply_filters('get_bwp_cache', $this->_cache[$name], $name);
        do_action('get_bwp_cache', $data, $name);
        return $data;
    }
}