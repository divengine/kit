<?php

$commands['mkdir'] = [
    'help' => 'Create nested directories specified in the pathname',
    'type' => 'simple:string',
    'do' => function ($args, &$data = []) {
        $extra_msg = '';
        if (file_exists($args['value'])) $extra_msg = '[FOLDER EXISTS]';
        message("Creating directory {$args['value']} $extra_msg");
        @mkdir($args['value'], 0777, true);
    }
];