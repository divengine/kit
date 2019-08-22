<?php

$commands['init'] = [
    'help' => 'Init development with div',
    'type' => 'simple:string',
    'do' => function ($args, &$data = []) {

        global $config;

        if (file_exists("./.div")) {
            message("The folder .div already exists. Exiting without changes.");
            return false;
        }

        @mkdir('./.div');
        @mkdir('./.div/contrib');

        save_ini_file($config,"./.div/config.ini");

        message('Initialized empty div environment in '.getcwd().'/.div/', false);
    }

];