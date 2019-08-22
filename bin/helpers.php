<?php

/**
 * Show message in console
 *
 * @param        $msg
 * @param string $icon
 */
function message($msg, $icon = 'DIV', $date = true)
{
    echo trim(($icon !== false ? "[$icon] " : "") . ($date ? date("h:i:s") : "") . " $msg")."\n";
}

function input($msg, $default = null, $expected = [], $separator = ": ")
{

    $f = fopen("php://stdin", "r");
    while (true) {
        echo $msg . $separator;
        $s = fgets($f);
        $s = trim($s);

        if (empty($s)) $s = $default;
        if (empty($s)) continue;
        if (in_array($s, $expected) || count($expected) == 0) break;

        echo "\n Wrong answer. Please type " . implode(",", $expected) . "\n";
    }

    fclose($f);

    return $s;
}

/**
 * Download from url
 *
 * @param string $url
 * @param boolean $echo
 *
 * @return bool|mixed
 */
function wget($url, $echo = true)
{
    if ($echo) message("Download $url");
    $c = curl_init($url);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($c);
    $info = curl_getinfo($c);

    //message("HTTP response content type: ". $info['content_type']);
    //message("HTTP response code: ". $info['http_code']);

    if ($info['http_code'] == 404) return false;

    return $result;
}

function listFiles($from, $closure = '')
{
    $list = [];

    if (file_exists($from)) {
        $add = true;
        if (!is_null($closure) && !empty($closure) && is_callable($closure)) $add = $closure($from);

        if ($add) $list[$from] = $from;

        $stack = [];
        if (!is_file($from)) $stack = [$from => $from];

        while (count($stack) > 0) // avoid recursive calls!!
        {
            $from = array_shift($stack);
            $dir = scandir($from);

            foreach ($dir as $entry) {
                $full_path = str_replace("//", "/", "$from/$entry");

                if ($entry != '.' && $entry != '..') {
                    $add = true;

                    if (!is_file($full_path)) $stack[$full_path] = $full_path;

                    if (!is_null($closure) && !empty($closure) && is_callable($closure)) $add = $closure($full_path);

                    if ($add) $list[$full_path] = $full_path;

                }
            }
        }
    }

    return $list;
}

function getReposFromConfig($config)
{
    $repos = [];
    foreach ($config as $prop => $value) {
        if (substr($prop, 0, 5) == "repo ") {
            $repo_id = trim(substr($prop, 5));

            // fix uri base, from //... to repo_id://...
            if (substr($value['uri_base'], 0, 2) == "//")
                $value['uri_base'] = "$repo_id:{$value['uri']}";

            $repos[$repo_id] = $value;
        }
    }

    return $repos;
}


function repoUriToRemote($uri, $remote, $uri_base)
{
    $args = [];

    if (divWays::match($uri_base, $uri, $args)) {
        foreach ($args as $arg => $value)
            $uri_base = str_replace('{' . $arg . '}', $value, $uri_base);

        // comparar el uri con el uri_base
        $uri_base = str_replace(["/...", ".../"], "/", $uri_base);
        $pos = strpos($uri, $uri_base);
        $preffix = '';
        $suffix = '';

        if ($pos !== false) {
            $preffix = substr($uri, 0, $pos);
            $suffix = substr($uri, $pos + strlen($uri_base));
        }

        foreach ($args as $arg => $value) $remote = str_replace('{' . $arg . '}', $value, $remote);

        if (substr($remote, -4) == '/...') $remote = substr($remote, 0, -4) . "/" . $suffix;
        if (substr($remote, 0, 4) == '.../') $remote = $preffix . "/" . substr($remote, 4);

        $id = uniqid();

        $remote = str_replace("://", "{{$id}}", $remote);
        $remote = str_replace("//", "/", $remote);
        $remote = str_replace("{{$id}}", "://", $remote);

        return $remote;
    }

    return false;
}

function getBasePath($url)
{
    $purl = parse_url($url);
    if ($purl === false) return false;
    if (!isset($purl['path'])) return false;
    if (!isset($purl['scheme'])) $purl['scheme'] = 'http';
    if (!isset($purl['port'])) $purl['port'] = '';
    else $purl['port'] = ":" . $purl['port'];
    if (!isset($purl['user'])) $purl['user'] = '';
    if (!isset($purl['pass'])) $purl['pass'] = '';
    elseif ($purl['user'] != '') $purl['pass'] = ':' . $purl['pass'];
    else $purl['pass'] = '';
    if (!isset($purl['query'])) $purl['query'] = '';
    else $purl['query'] = '?' . $purl['query'];

    if (!isset($purl['host'])) {
        $p = strpos($purl['path'], '/');
        $purl['host'] = substr($purl['path'], 0, $p);
        $purl['path'] = substr($purl['path'], $p + 1);
    }

    $path = "{$purl['path']}{$purl['query']}";

    if (isset($path[0])) if ($path[0] == "/") $path = substr($path, 1);

    return [
        "base" => "{$purl['scheme']}://{$purl['user']}{$purl['pass']}" . ($purl['user'] != '' ? "@" : "") . "{$purl['host']}{$purl['port']}/",
        "path" => $path,
        "url" => $purl
    ];
}

function fixUrl($value, $config, $default_destination = "./.div/contrib")
{
    $repos = getReposFromConfig($config);

    $url = $value;
    $url_parts = parse_url($value);

    $repository = [
        'destination' => $default_destination
    ];

    foreach ($repos as $repo_id => $repo) {
        $url = repoUriToRemote($value, $repo['url_base'], $repo['uri_base']);
        if ($url !== false) {
            $repository = $repo;
            break;
        }

    }

    $basePath = getBasePath($url);
    $virtualBasePath = getBasePath($value);

    return [
        'original_url' => $url_parts,
        'url' => $url,
        'basePath' => $basePath['base'],
        'path' => $basePath['path'],
        'virtual_path' => $virtualBasePath['url']['host'] . "/" . $virtualBasePath['path'],
        'destination' => $repository['destination']];
}

function parseArgs($line)
{
    $line = trim($line);
    $len = strlen($line);

    $args = [];
    $quote = false;
    $arg = '';
    for ($i = 0; $i < $len; $i++) {
        if ($line[$i] == '"') {
            $quote = !$quote;

            if (!$quote) {
                $args[] = $arg;
                $arg = '';
                continue;
            }
        }

        if ($line[$i] == ' ')
            if (!$quote) {
                $args[] = $arg;
                $arg = '';
            } else $arg .= $line[$i];
        else $arg .= $line[$i];
    }

    if ($arg != '') $args[] = $arg;
    return $args;
}

function showWelcomeMessage()
{
    echo "\n";
    message("div-cli 1.0 (Command Line Interface of Div Software Solutions) ", false, false);
    message("Today is " . date("Y-m-d h:i:s"), false, false);
    message('Type "help", "copyright", "credits" or "license" for more information.', false, false);
    message('Type "quit" or "exit", for exit from interactive mode.', false, false);
    echo "\n";
}


/**
 * Execute a command
 *
 * @param       $command
 * @param       $args
 * @param array $data
 */
function executor($command, $args, &$data = [])
{
    global $commands;

    // Executor
    $doAfter = [['do' => $command, 'args' => $args]];

    while (count($doAfter) > 0) {
        $moreDoAfter = [];
        foreach ($doAfter as $doa) {
            $do = $commands[$doa['do']]['do'];
            $moreDo = $do($doa['args'], $data);

            if (is_array($moreDo)) $moreDoAfter = array_merge($moreDoAfter, $moreDo);
        }
        $doAfter = $moreDoAfter;
    }
}


/**
 * Load configuration
 */
function loadConfig()
{
    global $config;

    $configPath = "./.div/config.ini";

    if (!file_exists($configPath)) {
        // message("Configuration $configPath not found. Using default config...", 'WARN');

        $config = [
            "repo div" => [
                "uri_base" => "div://...",
                "url_base" => "https://github.com/divengine/repo/raw/master/...",
                "destination" => "./.div/contrib"
            ],
            "repo github" => [
                "uri_base" => "github://{organization}/{repository}/...",
                "url_base" => "https://github.com/{organization}/{repository}/raw/master/...",
                "destination" => "./.div/contrib"
            ]
        ];

    } else {
        //message("Loading configuration from $configPath");
        $config = parse_ini_file($configPath, INI_SCANNER_RAW);
    }
}

/**
 * @param $array
 * @param string $out
 * @return string
 */
function array_to_ini($array, $out = "")
{
    $t = "";
    $q = false;
    foreach ($array as $c => $d) {
        if (is_array($d)) $t .= "\r\n\r\n[$c]\r\n".array_to_ini($d, $c);
        else {
            if ($c === intval($c)) {
                if (!empty($out)) {
                    $t .= "\r\n" . $out . " = \"" . $d . "\"";
                    if ($q != 2) $q = true;
                } else $t .= "\r\n" . $d;
            } else {
                $t .= "\r\n" . $c . " = \"" . $d . "\"";
                $q = 2;
            }
        }
    }
    if ($q != true && !empty($out)) return "[" . $out . "]\r\n" . $t;
    if (!empty($out)) return $t;
    return trim($t);
}


/**
 * Save array to ini file
 *
 * @param array $array
 * @param string $file_name
 */
function save_ini_file($array, $file_name)
{
    $a = array_to_ini($array);
    file_put_contents($file_name, $a);
}