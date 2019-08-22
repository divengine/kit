<?php

$commands['check-repo'] = [
    'help' => 'Clear local repo files',
    'type' => 'optional:string',
    'do' => function ($args, &$data = []) {
        global $config;

        $list = listFiles(PACKAGES, function ($full_path, &$data = []) {
            global $config;

            if (is_dir($full_path)) return false;

            message('Checking local resource ' . $full_path);

            if (is_file($full_path)) {
                $dest = $config['repo']['destination'];
                $ldest = strlen($dest);

                if (substr($full_path, 0, $ldest) == $dest) $full_path = substr($full_path, $ldest);

                $uri = 'repo://' . $full_path;
                $uri = str_replace('///', '//', $uri);

                executor('get', ['value' => $uri], $data);

                if (isset($data['not_found'])) if (in_array($uri, $data['not_found'])) {
                    echo "\n";
                    message("Resource $uri not found in remote repository", "FATAL");
                    echo "\n";

                    return true;
                }
            }

            return false;
        });

        if (count($list) > 0) {
            echo "\n";
            message("The following local resources not found in the remote repository {$config['repo']['origin']}");
            echo "\n";

            foreach ($list as $full_path) {
                $dest = $config['repo']['destination'];
                $ldest = strlen($dest);

                if (substr($full_path, 0, $ldest) == $dest) $full_path = substr($full_path, $ldest);

                echo "- $full_path\n";
            }

            echo "\n";
            $r = input("Do you want to delete this resources (Y/N) [Y]?", 'Y', ['y', 'yes', 'n', 'no']);
            $r = strtolower($r);
            if ($r == 'y' || $r == 'yes') {
                foreach ($list as $item) {
                    message("Deleting $item");
                    unlink($item);
                }
            }
        }

        // TODO: show more stats (count of updated (md5 check), count deleted, count new, ...)
        $data['not_found'] = $list;
    }
];