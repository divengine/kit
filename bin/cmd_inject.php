<?php

$commands['inject'] = [
    'help' => 'Inject file/block inside other file',
    'type' => [
        // read from file
        '-ff' => 'required:string:The source of the code that will be injected',

        // read from block (default, entire file)
        '-fb' => 'optional:string:A flag that identify the block that will be read from -ff',

        // write to file
        '-tf' => 'required:string:The destiny of the code',

        // write inside block (default, append)
        '-tb' => 'optional:string:A flag that identify the block that will be replaced with the source',

        // write after line (default, append)
        '-ta' => 'optional:string:A flag in a line of the text, that identify the point where div put source'
    ],
    'do'   => function ($args, &$data = []) {

        if (file_exists($args['-ff'])) {
            //message("FROM FILE = {$args['-ff']}");
            $ff = '';
            if (isset($args['-fb'])) {
                //message("FROM BLOCK = {$args['-fb']}");

                $flag = $args['-fb'];
                $f = fopen($args['-ff'], 'r');

                $start = false;
                while (!feof($f)) {
                    $s = fgets($f);
                    $p = strpos($s, $flag);
                    if ($p !== false) {
                        if ($start) { // only first block
                            $ff .= substr($s, 0, $p); // get content before flag in this line
                            break;
                        }

                        $start = true;
                        $ff .= substr($s, $p + strlen($flag)); // get content after flag in this line
                        continue;
                    }

                    if ($start) {
                        $ff .= $s;
                    }
                }
            } else {
                $ff = file_get_contents($args['-ff']);
            }

            // if destiny file not exists, then create it
            if (!file_exists($args['-tf'])) {
                file_put_contents($args['-tf'], "");
            }

            if (isset($args['-tb'])) {
                $tf = fopen($args['-tf'], 'r');
                $tempfilename = $args['-tf'].".".uniqid();
                $ttf = fopen($tempfilename, 'w');

                //message("Creating temporal file $tempfilename");

                if ($ttf === false) {
                    message("Error when crete a temporal file: $tempfilename. ");

                    return false;
                }

                $inject = false;
                $block = $args['-tb'];
                $start = false;

                $lines = 0;
                while (!feof($tf)) {
                    $s = fgets($tf);
                    $p = strpos($s, $block);
                    if ($p !== false && $start == false) {
                        fputs($ttf, $s);
                        $start = true;
                        continue;
                    }

                    if ($p !== false && $start == true) {
                        if (!$inject) {
                            fputs($ttf, $ff); // inject code
                            $inject = true;
                        }

                        //if ($ff[strlen($ff)-1] != "\n") fputs($ttf, "\n");

                        $lines++;
                        fputs($ttf, substr($s, $p)); // put content after flag
                        $start = false;
                        continue;
                    }

                    if ($start == true) {
                        continue;
                    }

                    fputs($ttf, $s);
                }

                message("$lines lines injected to {$args['-tf']}");
                fclose($tf);
                fclose($ttf);
                rename($args['-tf'], $args['-tf'].".bak");
                rename($tempfilename, $args['-tf']);

            } elseif (isset($args['-ta'])) { // INSERT AFTER

                $tf = fopen($args['-tf'], 'r');
                $tempfilename = $args['-tf'].".".uniqid();
                $ttf = fopen($tempfilename, 'w');

                if ($ttf === false) {
                    message("Error when crete a temporal file: $tempfilename. ");

                    return false;
                }

                $inject = false;
                $block = $args['-ta'];

                $lines = 0;
                while (!feof($tf)) {
                    $s = fgets($tf);
                    fputs($ttf, $s);

                    if (strpos($s, $block) !== false && $inject == false) {
                        if (!$inject) {
                            fputs($ttf, $ff);
                            $inject = true;
                        }
                    }
                }

                message("$lines lines injected to {$args['-tf']}");
                fclose($tf);
                fclose($ttf);
                rename($args['-tf'], $args['-tf'].".bak");
                rename($tempfilename, $args['-tf']);
            } else {
                $tf = fopen($args['-tf'], "a");
                fputs($tf, $ff);
                fclose($tf);
            }
        }

    }
];