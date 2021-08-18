<?php
    
    $secrets = json_decode(file_get_contents(ROOT_PATH . $_SERVER['APP_SECRETS']), true);

    return [

        'mysql' => [
            'host'      => $secrets['mysql']['host'],
            'dbname'    => $secrets['mysql']['dbname'],
            'username'  => $secrets['mysql']['username'],
            'password'  => $secrets['mysql']['password'],
            'port'      => $secrets['mysql']['port'],
            'charset'   => 'utf8',
        ],
        'gRecaptcha' => [
            'browser_key' => $secrets['gRecaptcha']['browser_key'],
            'secret_key'  => $secrets['gRecaptcha']['secret_key']
        ],
        'debug' => false,
    ];
   