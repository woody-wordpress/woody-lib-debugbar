<?php

/**
 * @author Léo POIROUX
 * @copyright Raccourci Agency 2022
 */

namespace Woody\Lib\DebugBar;

use Woody\App\Container;
use Woody\Modules\Module;
use Woody\Services\ParameterManager;
use DebugBar\StandardDebugBar;

final class DebugBar extends Module
{
    protected $debugbar;
    protected $debugbarRenderer;

    protected static $key = 'woody_lib_debugbar';

    public function initialize(ParameterManager $parameterManager, Container $container)
    {
        define('WOODY_LIB_DEBUGBAR_VERSION', '1.0.0');
        define('WOODY_LIB_DEBUGBAR_ROOT', __FILE__);
        define('WOODY_LIB_DEBUGBAR_DIR_ROOT', dirname(WOODY_LIB_DEBUGBAR_ROOT));
        define('WOODY_LIB_DEBUGBAR_DIR_RESOURCES', WOODY_LIB_DEBUGBAR_DIR_ROOT . '/Resources');

        parent::initialize($parameterManager, $container);
        $this->debugbar = new StandardDebugBar();
        $this->debugbarRenderer = $this->debugbar->getJavascriptRenderer();

        // $this->debugbar->addCollector(new WPActionsCollector());
        // $this->debugbar->addCollector(new WPFiltersCollector());

        // if (defined('SAVEQUERIES') && SAVEQUERIES) {
        //     $this->debugbar->addCollector(new WPDBCollector());
        // }
    }

    public static function dependencyServiceDefinitions()
    {
        return \Woody\Lib\DebugBar\Configurations\Services::loadDefinitions();
    }

    public function subscribeHooks()
    {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            add_action('admin_init', [$this, 'init_ajax']);
        }

        add_action('init', [$this, 'init']);
    }

    public function init()
    {
        // if (!is_super_admin() || $this->is_wp_login()) {
        //     return;
        // }

        add_action('wp_footer', [$this, 'render'], 1000);
        add_action('wp_head', [$this, 'header'], 1);
    }

    public function init_ajax()
    {
        if (!is_super_admin()) {
            return;
        }

        $this->debugbar->sendDataInHeaders();
    }

    public function render()
    {
        echo $this->debugbarRenderer->render();
    }

    public function header()
    {
        echo $this->debugbarRenderer->renderHead();
    }

    private function is_wp_login()
    {
        return 'wp-login.php' == basename($_SERVER['SCRIPT_NAME']);
    }

    public function __call($name, $args)
    {
        if ($name == 'startMeasure') {
            $this->debugbar['time']->startMeasure($args[0], $args[1]);
        } elseif ($name == 'stopMeasure') {
            $this->debugbar['time']->stopMeasure($args[0]);
        } elseif ($name == 'addException') {
            $this->debugbar['exceptions']->addException($args[0]);
        } elseif ($name == 'info' || $name == 'debug') {
            $this->debugbar['messages']->info($args[0]);
        }
    }
}
