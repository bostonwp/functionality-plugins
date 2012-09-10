<?php

class wcsUtil
{   
    public function dump($var)
    {
        echo '<pre>'.print_r($var, true).'</pre>';
    }
}

class testSnapshot extends WPConfigSnapshotModule
{
    public function type()
    {
        return 'test_snapshot';
    }
    
    public function deprecated_check($version_check_object)
    {
        global $ran_deprecation_check;
        $ran_deprecation_check = true;
        return $version_check_object;
    }
    
    public function ui_action_hook()
    {
        return 'test_action';
    }
    
    public function take_snapshot()
    {
        return 'test_snapshot';
    }
    
    public function restore_snapshot($value)
    {
        global $restored;
        $restored = $value;
    }
}

class ConfigSnapshotsAPITests extends \Enhance\TestFixture
{
    
    public function setUp()
    {
        $this->util = new wcsUtil();
        $ts = new testSnapshot();
        WPConfigSnapshotPlugin::instance()->register_snapshot_module($ts);
    }
    
    public function tearDown()
    {
        
    }
    
    public function it_lets_me_register_snapshot_modules_of_the_right_type()
    {
        $m = WPConfigSnapshotPlugin::instance()->snapshot_modules();
        \Enhance\Assert::isTrue(array_key_exists('test_snapshot', $m));
        \Enhance\Assert::areIdentical('testSnapshot', get_class($m['test_snapshot']));
    }
    
    public function it_uses_the_version_check_before_rendering_a_ui()
    {
        global $ran_deprecation_check;
        $ran_deprecation_check = false;
        $ts = new testSnapshot();
        $ts->call_render_ui();
        \Enhance\Assert::isTrue($ran_deprecation_check);
    }
    
    public function it_lets_me_pass_it_snapshotted_data_for_storage()
    {
        delete_option('wp_config_snapshots');
        $option = get_option('wp_config_snapshots');
        \Enhance\Assert::isFalse($option);
        $ts = new testSnapshot();
        WPConfigSnapshotPlugin::instance()->take_snapshot($ts->type(), 'first_snapshot');
        WPConfigSnapshotPlugin::instance()->take_snapshot($ts->type(), 'second_snapshot');
        $option = get_option('wp_config_snapshots');
        \Enhance\Assert::areIdentical(1, count($option));
        \Enhance\Assert::areIdentical(2, count($option[$ts->type()]));
        \Enhance\Assert::areIdentical($ts->type(), $option[$ts->type()]['first_snapshot']->value());
        \Enhance\Assert::areIdentical($ts->type(), $option[$ts->type()]['second_snapshot']->value());
    }
    
    public function it_lets_me_tell_it_how_to_restore_a_snapshot()
    {
        delete_option('wp_config_snapshots');
        $option = get_option('wp_config_snapshots');
        \Enhance\Assert::isFalse($option);
        $ts = new testSnapshot();
        WPConfigSnapshotPlugin::instance()->take_snapshot($ts->type(), 'first_snapshot');
        WPConfigSnapshotPlugin::instance()->take_snapshot($ts->type(), 'second_snapshot');
        global $restored;
        $restored = false;
        WPConfigSnapshotPlugin::instance()->restore_snapshot($ts->type(), 'second_snapshot');
        \Enhance\Assert::areIdentical($ts->type(), $restored);
    }
}

class ConfigSnapshotPluginTests extends \Enhance\TestFixture
{
    public function setUp()
    {
        delete_option('wp_config_snapshots');
        $this->util = new wcsUtil();
        $ts = new testSnapshot();
        WPConfigSnapshotPlugin::instance()->register_snapshot_module($ts);
    }
    
    public function tearDown()
    {
        
    }
    
    public function it_sets_the_correct_snapshot_active_when_one_is_restored_or_taken()
    {
        $ts = new testSnapshot();
        WPConfigSnapshotPlugin::instance()->take_snapshot($ts->type(), 'first_snapshot');
        WPConfigSnapshotPlugin::instance()->take_snapshot($ts->type(), 'second_snapshot');
        $option = get_option('wp_config_snapshots');
        \Enhance\Assert::isTrue($option[$ts->type()]['second_snapshot']->is_active());
        \Enhance\Assert::isFalse($option[$ts->type()]['first_snapshot']->is_active());
        WPConfigSnapshotPlugin::instance()->restore_snapshot($ts->type(), 'first_snapshot');
        \Enhance\Assert::isFalse($option[$ts->type()]['second_snapshot']->is_active());
        \Enhance\Assert::isTrue($option[$ts->type()]['first_snapshot']->is_active());   
    }
}