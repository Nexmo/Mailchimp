<?php
//load defaults from config file, allow enviroment override
$config = parse_ini_file(__DIR__ . '/config.ini');
foreach($config as $setting => $default){
    defined($setting)
    || define($setting, (getenv($setting) ? getenv($setting) : $default));
}
