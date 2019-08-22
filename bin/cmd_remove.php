<?php

$commands['remove'] = [
    'help' => 'Remove file',
    'type' => 'simple:string',
    'do' => function ($args, &$data = []) {

        global $silent;

        $fn = $args['value'];
        if (!file_exists($fn)) {
            if (!$silent) message("File $fn not exists");

        } elseif (!is_file($fn)) {
            message("$fn is a folder");
        } else {
            @unlink($fn);
            message("Removing file: $fn");
        }

    }
];