<?php

/**
 * Command Line Interface for Div Development Kit
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 * for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program as the file LICENSE.txt; if not, please see
 * https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package com.divengine
 * @author  Rafa Rodriguez [@rafageist] <rafageist@hotmail.com>
 *
 * @link    https://divengine.com
 *
 * @version 1.0
 */

define ('DIV_DEFAULT_TPL_FILE_EXT', 'tpl');
include __DIR__.'/../vendor/autoload.php';
include_once __DIR__.'/helpers.php';

use divengine\div;


//  ------------------------ Custom modifiers
/**
 * Convert bool value to bool string
 *
 * @param $value
 *
 * @return string
 */

function div_cli_bool($value)
{
    return div::mixedBool($value) ? 'true' : 'false';
}

// ---------------------------------------------

div::addCustomModifier('bool:', 'div_cli_bool');

// ----------------------------------------------------

// Globals
$config = [];
$commands = [];

loadConfig();

$repos = getReposFromConfig($config);
foreach ($repos as $repo) {
    div::addIncludePath($repo['destination']);
}

// Commands implementation
$commands = [];

$bin_dir = scandir(__DIR__);

foreach ($bin_dir as $entry) {
    if ($entry !== '..' && $entry !== '.') {
        @include_once __DIR__."/$entry";
    }
}

// Starter
$sys_args = $_SERVER['argv'];
foreach ($sys_args as $k => $v) {
    if (empty(trim($v))) {
        unset($sys_args[$k]);
    }
}

$interactive_mode = !isset($sys_args[1]);
$welcome = false;

do {

    if ($interactive_mode) {
        if (!$welcome) {
            showWelcomeMessage();
        }
        $welcome = true;
        echo "\n";
        $prompt = input('div> ', null, [], '');
        $prompt = trim($prompt);
        $prompt = parseArgs($prompt);
    } else {
        $prompt = $sys_args;
        $prompt[0] = '';
    }

    // remove empty args
    foreach ($prompt as $index => $value) {
        if (trim($value) == '') {
            unset($prompt[$index]);
        }
    }

    // get the command
    $command = array_shift($prompt);

    $args = [];
    $args_total = count($prompt);

    $silent = false;

    if (isset($command[0])) {
        if ($command[0] === '@') {
            $silent = true;
            $command = substr($command, 1);
        }
    }

    // welcome message
    /*if (!$silent && !$interactive_mode) {
        showWelcomeMessage();
    }*/

    $command = strtolower(trim($command));

    // exit command
    if (in_array($command, ['exit', 'quit', 'bye'])) {
        message('Good bye!');
        break;
    }

    // check if command exists
    if (!isset($commands[$command])) {
        message("Command not found or unknown. Use 'help' for show available commands.", 'FATAL');
        if ($interactive_mode) {
            continue;
        }
        exit();
    }

    // load configuration
    if (!isset($commands[$command]['config-required'])) {
        $commands[$command]['config-required'] = true;
    }
    if ($commands[$command]['config-required']) {
        loadConfig();
    }

    $cmd = $commands[$command]['type'];

    // parsing args
    if (is_string($cmd)) {
        $arr = explode(':', $cmd);
        $v = trim(implode(' ', $prompt));

        switch ($arr[1]) {
            case 'string':
                break;
            case 'integer':
                $v = (int)$v;
                break;
        }
        $args = $prompt;
        $args = ['value' => $v];

    } elseif (is_array($cmd)) {
        for ($i = 0; $i < $args_total; $i++) {
            $v = $prompt[$i];

            if (strpos($v, '=')) {
                $arr = explode('=', $v);
                $v = trim($arr[1]);
                if (strpos($v, '"') === 0 && substr($v, strlen($v) - 1) === '"') {
                    $v = substr($v, 0, strlen($v) - 2);
                }
                $args[trim($arr[0])] = $v;
                continue;
            }

            if (isset($prompt[$i + 1])) {
                $args[$v] = $prompt[++$i];
            } else {
                $args[$v] = null;
            }
        }
    }

    // Executor
    executor($command, $args);

} while ($interactive_mode);


