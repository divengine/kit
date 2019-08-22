<?php

$commands['copy'] = [
    'help' => 'Copy file',
    'type' => [
        '-f' => 'required:string', // from
        '-t' => 'required:string', // to
    ], 'simple:string',
    'do' => function ($args, &$data = []) {

        global $silent;

        $from = $args['-f'];
        $to = $args['-t'];

        if (!file_exists($from)) {
            if (!$silent) message("File $from not exists");
        } elseif (!is_file($from)) {
            message("$from is a folder");
        } else {
            $fn = $to;

            if (file_exists($to) && !is_file($to)) {
                $fn = pathinfo($from, PATHINFO_FILENAME . (empty(PATHINFO_EXTENSION) ? "" : "." . PATHINFO_EXTENSION));
            } elseif (file_exists($to) && is_file($to)) {
                message("Destination file $to exists");
                return;
            }

            message("Copying file to $fn");
            copy($from, $fn);
        }
    }
];