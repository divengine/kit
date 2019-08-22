<?php

/**
 * Show the current version of div-cli
 */
$commands['version'] = [
    'help' => "Show the version of current installed Div PHP Template Engine",
    'type' => null,
    'do' => function () {
        echo "div-cli version 1.0\n";
    }
];

