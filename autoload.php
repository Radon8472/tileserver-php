<?php

/**
 * Enable autoloading for the application.
 *
 * @param string $className
 *
 * @return void
 */
function tileServerAutoloader ($className) {
    $className = str_replace('TileServer', 'app', $className);
    $parts = explode('_', $className);

    require_once implode('/', $parts) . '.php';
}

spl_autoload_register('tileServerAutoloader');