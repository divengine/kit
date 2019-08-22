<?php

$commands['merge-json'] = [
    'help' => "Create a JSON file or output it, resulting from merge of others two JSON files",
    'type' => [
        '-j1' => 'required:string',
        '-j2' => 'required:string',
        '-o' => 'optional:string'
    ],
    'do' => function ($args, &$data = []) {

        if (isset($args['-j1']) && isset($args['-j2'])) {
            $j1 = $args["-j1"];
            $j2 = $args["-j2"];

            if (!file_exists($j1)) {
                message("JSON file $j1 not found");

                return false;
            }

            if (!file_exists($j2)) {
                message("JSON file $j2 not found");

                return false;
            }

            // TODO: ...
        }
    }
];