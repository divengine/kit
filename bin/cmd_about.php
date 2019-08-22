<?php

/**
 * Show information about current version of div-cli
 */
$commands['about'] = [
    'help' => "Show information About div-cli",
    'type' => null,
    'do' => function () {
        echo new div('{\n}' .
            '================================================={\n}' .
            '[[]] 2011 - {/div.now:Y/} Div Software Solutions {\n}' .
            '     https://divengine.github.io {\n}' .
            '     div-cli 1.0 {\n}' .
            '-------------------------------------------------{\n}' .
            '     Using: {\n}' .
            '     {\n}' .
            '     [[]] Div PHP Template Engine {$div.version} {\n}' .
            '     [<>] Div PHP Ways ' . divWays::getVersion() . '{\n}' .
            '     [()] Div PHP Nodes ' . divNodes::getVersion() . '{\n}' .
            '     {\n}' .
            '     General Public License 3.0 (GPL) {\n}' .
            '-------------------------------------------------{\n}' .
            '     Current div-cli commands location:{\n}'.
            '     '.__DIR__.'{\n}' .
            '================================================={\n}');
    }
];

