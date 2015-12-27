<?php
date_default_timezone_set('Europe/Sofia');
require_once ("../config/Routes.php");
require_once ("../config/Config.php");
require_once ("../core/Settings.php");
require_once ("../core/autoload.php");
$app = new Core;
$app->Run();