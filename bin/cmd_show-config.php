<?php

$commands['show-config'] = [
    'help' => "Show configuration",
    'type' => "optional:string",
    'do' => function ($args, &$data = []) {
        global $config;
        global $configPath;
        echo "Configuration file: $configPath\n";

        $show = $config;
        if (isset($args[0]) && !empty($args[0]) && isset($config[$args[0]])) $show = [$args => $config[$args[0]]];

        foreach ($show as $key => $value) {
            if (is_array($value)) {
                echo "- $key:\n";
                foreach ($value as $kk => $vv) {
                    echo "  - $kk: $vv\n";
                }
            } else {
                echo "- $key: $value\n";
            }
        }
    }
];