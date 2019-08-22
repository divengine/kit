<?php

/**
 * Compile a template with a model
 */
$commands['build'] = [
    'type' => [
        '-t' => 'required:string', // template
        '-d' => 'optional:string', // data
        '-o' => 'optional:string', // output file
        '-g' => 'optional:string',
        '--verbose' => 'optional:null'
    ],
    'do' => function ($args, &$data = []) {
        message("Starting builder...");

        $tpl = $args['-t'];
        $out = '';
        $dat = '';

        if (isset($args['-o'])) $out = $args['-o'];
        if (isset($args['-d'])) $dat = $args['-d'];

        if (empty($out)) $out = $tpl . ".out";
        if (empty($dat)) $dat = [];

        $temp_div = new div($tpl, []);
        div::docsReset();
        div::docsOn();
        $temp_div->loadTemplateProperties();
        $temp_div->prepareDialect();
        $temp_div->parseComments("main");
        $docProps = $temp_div->getDocs();

        // hot injection of custom div-engine as argument -g
        if (isset($docProps['main']['engine'])) {
            $args['-g'] = trim($docProps['main']['engine']);
        }

        $className = 'div';
        if (isset($args['-g'])) {
            $path = $args['-g'];
            $full_path = PACKAGES . "/" . $path;

            message("Checking custom engine in $full_path");

            // check if class file exists
            if (!file_exists($full_path) && is_file($full_path) && pathinfo($full_path, PATHINFO_EXTENSION) == ".php") {
                message("Downloading custom engine in $full_path");
                // try to get from remote repository
                executor('get', ['value' => $path], $data);
            }

            if (file_exists($full_path)) {
                include_once $full_path;
                $className = basename($full_path, '.php');
            } else
                message("Custom engine in $full_path not found", "ERROR");
        }

        message("Processing template $tpl" . ((isset($args['-d'])) ? " with data in {$args['-d']} and generator -$className-" : ""));

        div::docsReset();
        div::docsOff();

        $div = new $className($tpl, $dat);
        $className::logOn();
        $t1 = microtime(true);
        $code = $div . "";
        $t2 = microtime(true);
        message("The template was parsed in " . number_format($t2 - $t1, 2) . " secs");

        message("Writing results to file $out");
        file_put_contents($out, $code);

        message("BUILD SUCCESS!");
    },
    'help' => 'Parse a template and write result to a file or stdout'
];