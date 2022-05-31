<?php

/**
 * @author Léo POIROUX
 * @copyright Raccourci Agency 2022
 */

namespace Woody\Lib\DebugBar\Configurations;

class Services
{
    private static $definitions;

    private static function definitions()
    {
        return [
            'debugbar.manager' => [
                'class'     => \Woody\Lib\DebugBar\Services\DebugBarManager::class,
                'arguments' => []
            ]
        ];
    }

    public static function loadDefinitions()
    {
        if (!self::$definitions) {
            self::$definitions = self::definitions();
        }

        return self::$definitions;
    }
}
