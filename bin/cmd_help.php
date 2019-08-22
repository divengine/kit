<?php

$commands['help'] = [
    "type" => "simple:string",
    "do" => function ($args, &$data = []) {
        echo "usage: div <command> [<args>]\n";
        echo "\n";
        echo "These are common Div commands used in various situations:\n";
        echo "\n";

        global $commands;

        $maxlen = 0;
        foreach ($commands as $command => $info) {
            $l = strlen($command);
            $maxlen = $l > $maxlen ? $l : $maxlen;
        }

        foreach ($commands as $command => $info) {
            echo str_repeat(' ', $maxlen - strlen($command)) . "$command \t " . $info['help'] . "\n";
        }
    },
    'help' => 'Show this help'
];