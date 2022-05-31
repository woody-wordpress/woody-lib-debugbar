<?php

/**
 * @author Léo POIROUX
 * @copyright Raccourci Agency 2022
 */

namespace Woody\Lib\DebugBar;

use Woody\App\Container;
use Woody\Modules\Module;
use Woody\Services\ParameterManager;
use Symfony\Component\Finder\Finder;

final class DebugBar extends Module
{
    protected $debugbarManager;

    protected static $key = 'woody_lib_debugbar';

    public function initialize(ParameterManager $parameterManager, Container $container)
    {
        define('WOODY_LIB_DEBUGBAR_VERSION', '1.0.0');
        define('WOODY_LIB_DEBUGBAR_ROOT', __FILE__);
        define('WOODY_LIB_DEBUGBAR_DIR_ROOT', dirname(WOODY_LIB_DEBUGBAR_ROOT));
        define('WOODY_LIB_DEBUGBAR_DIR_RESOURCES', WOODY_LIB_DEBUGBAR_DIR_ROOT . '/Resources');

        parent::initialize($parameterManager, $container);
        $this->debugbarManager = $this->container->get('debugbar.manager');
    }

    public static function dependencyServiceDefinitions()
    {
        return \Woody\Lib\DebugBar\Configurations\Services::loadDefinitions();
    }

    public function subscribeHooks()
    {
        // Admin settings
        add_action('members_register_caps', [$this, 'membersRegisterCaps']);
        // add_action('admin_menu', [$this, 'generateMenu']);

        // Enqueue scripts
        // add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        // add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);

        // Register Views folder as Timber locations
        add_filter('timber_locations', [$this, 'injectTimberLocation']);

        // ACF filters
        add_filter('acf/settings/load_json', [$this, 'acfJsonLoad']);
        add_filter('woody_acf_save_paths', [$this, 'acfJsonSave']);

        // Register translations
        add_action('after_setup_theme', [$this, 'loadThemeTextdomain']);
    }

    public function membersRegisterCaps()
    {
        members_register_cap('woody_debugbar', array(
            'label' => _x('Woody DebugBar', '', 'woody'),
            'group' => 'woody',
        ));
    }

    // public function generateMenu()
    // {
    //     acf_add_options_page([
    //         'page_title'    => 'DebugBar',
    //         'menu_title'    => 'DebugBar',
    //         'menu_slug'     => 'debugbar-settings',
    //         'capability'    => 'edit_pages',
    //         'icon_url'      => 'dashicons-bell',
    //         'position'      => 40,
    //     ]);
    // }

    // public function enqueueAssets()
    // {
    //     wp_enqueue_script('lib-debugbar-javascripts', $this->libAssetPath('woody-lib-debugbar', 'js/woody-lib-debugbar.js'), ['jquery'], WOODY_LIB_DEBUGBAR_VERSION, true);
    // }

    // public function enqueueAdminAssets()
    // {
    //     $screen = get_current_screen();
    //     if (!empty($screen->id) && strpos($screen->id, 'debugbar-settings') !== false) {
    //         wp_enqueue_script('lib-admin-debugbar-javascripts', $this->libAssetPath('woody-lib-debugbar', 'js/woody-admin-lib-debugbar.js'), ['jquery'], WOODY_LIB_DEBUGBAR_VERSION, true);
    //         wp_enqueue_style('lib-admin-debugbar-stylesheet', $this->libAssetPath('woody-lib-debugbar', 'scss/woody-admin-lib-debugbar.css'), [], null);
    //     }
    // }

    public function injectTimberLocation($locations)
    {
        $locations[] = WOODY_LIB_DEBUGBAR_DIR_RESOURCES . '/Views' ;

        return $locations;
    }

    /**
     * Register ACF Json load directory
     *
     * @since 1.0.0
     */
    public function acfJsonLoad($paths)
    {
        $paths[] = WOODY_LIB_DEBUGBAR_DIR_RESOURCES . '/ACF';
        return $paths;
    }

    /**
     * Register ACF Json Save directory
     *
     * @since 1.0.0
     */
    public function acfJsonSave($groups)
    {
        $acf_json_path = WOODY_LIB_DEBUGBAR_DIR_RESOURCES . '/ACF';

        $finder = new Finder();
        $finder->files()->in($acf_json_path)->name('*.json');
        foreach ($finder as $file) {
            $filename = str_replace('.json', '', $file->getRelativePathname());
            $groups[$filename] = $acf_json_path;
        }

        return $groups;
    }

    public function loadThemeTextdomain()
    {
        load_theme_textdomain('woody-lib-debugbar', WOODY_LIB_DEBUGBAR_DIR_ROOT . '/Languages');
    }

    /**
     * @noRector
     * Commande pour créer automatiquement woody-lib-debugbar.pot
     * A ouvrir ensuite avec PoEdit.app sous Mac
     * cd ~/www/wordpress/current/vendor/woody-wordpress/woody-lib-debugbar/
     * wp i18n make-pot . Languages/woody-lib-debugbar.pot
     */
    private function twigExtractPot()
    {
    }
}
