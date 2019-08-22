<?php

$commands['get'] = [
    'help' => 'Get resource from remote repository',
    'type' => 'simple:string',
    'do' => function ($args, &$data = []) {
        global $config;

        $doAfter = [];
        $urlParts = fixUrl($args['value'], $config);
        $path = $urlParts['path'];
        $url = $urlParts['url'];

        // try 1: original url
        $content = wget($url, false);

        if ($content == false) {
            // try 2: url as template
            $content = wget($url . ".tpl");

            if ($content == false) {
                message("Resource not found", "FATAL");
                if (!isset($data['not_found'])) $data['not_found'] = [];
                $data['not_found'][] = $args['value'];

                return false;
            }

            $path .= ".tpl";
            $urlParts['virtual_path'] .= ".tpl";
        }

        $filename = "{$urlParts['destination']}/{$urlParts['virtual_path']}";
        $path = dirname($filename);

        if (!file_exists($path)) mkdir($path, 0777, true);

        message("Writing content of $filename ...");

        file_put_contents($filename, $content);

        $tpl = new div($filename, []);
        div::docsReset();
        div::docsOn();
        $tpl->loadTemplateProperties();
        $tpl->prepareDialect();
        $tpl->parseComments("main");
        $docProps = $tpl->getDocs();
        $tplProps = $tpl->__properties;

        // hot injection of dialect as dependency
        if (isset($tplProps['DIALECT'])) {
            $tplProps['DIALECT'] = trim($tplProps['DIALECT']);

            if (!isset($docProps['main']['dependency'])) $docProps['main']['dependency'] = [];

            // relative first
            $docProps['main']['dependency'][] = dirname($urlParts['virtual_path']) . '/' . $tplProps['DIALECT'];

            // absolute second
            $docProps['main']['dependency'][] = $tplProps['DIALECT'];
        }

        // hot injection of custom div-engine as dependency
        if (isset($docProps['main']['engine'])) {
            $docProps['main']['engine'] = trim($docProps['main']['engine']);

            if (!isset($docProps['main']['dependency'])) $docProps['main']['dependency'] = [];

            // relative first
            $docProps['main']['dependency'][] = dirname($urlParts['virtual_path']) . '/' . $docProps['main']['engine'];

            // absolute second
            $docProps['main']['dependency'][] = $docProps['main']['engine'];
        }

        // retrieve dependencies
        if (isset($docProps['main']['dependency'])) {
            $dependencies = $docProps['main']['dependency'];

            if (!is_array($dependencies)) $dependencies = [$dependencies];

            foreach ($dependencies as $dep) {
                $dep = trim($dep);
                $dep = str_replace(["\t", "\n", "\r"], "", $dep);
                $dep_path  = $urlParts['original_url']['scheme']."://".$dep;
                //echo "-- DEPENDENCY: $dep \n -- -- $dep_path\n";
                if (!empty($dep)) $doAfter[] = [
                    'do' => 'get',
                    'args' => ['value' => $dep_path]
                ];
            }
        }

        return $doAfter;
    }
];