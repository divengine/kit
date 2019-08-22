<?php

$commands['translate'] = [
    'help' => "Translate template's syntax to specific dialect",
    'type' => [
        '-t' => 'required:string', // template
        '-fd' => 'optional:string', // from dialect
        '-d' => 'required:string', // to dialect
        '-o' => 'optional:string' // output result to
    ],
    'do' => function ($args, &$data = []) {

        $dialectFrom = [];
        $dialectTo = [];

        if (isset($args['-fd'])) $dialectFrom = file_get_contents($args['-fd']);

        if (isset($args['-d'])) $dialectTo = file_get_contents($args['-d']);

        $src = file_get_contents($args['-t']);

        $tpl = new div($src, []);

        // Template's properties
        $prop = $tpl->getTemplateProperties();

        // Preparing dialect
        $tpl->__src = $tpl->prepareDialect(null, $prop);

        // Translating...
        $src = $tpl->translate($dialectFrom, $dialectTo);

        // Save result
        file_put_contents($args['-o'], $src);
    }
];