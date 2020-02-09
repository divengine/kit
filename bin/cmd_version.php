<?php

/**
 * Show the current version of the kit
 */
$commands['version'] = [
    'help' => "Show the version of current installed Div Development Kit",
    'type' => null,
    'do'   => function () {
        echo "Div Development Kit 1.0.0\n";
    }
];

