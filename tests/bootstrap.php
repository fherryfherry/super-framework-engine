<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('BASE_DIR', '');

require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL & ~E_DEPRECATED);

require_once __DIR__ . '/../src/Helpers/Helper.php';
require_once __DIR__ . '/../src/App/UtilRequest/Configs/Helper.php';
require_once __DIR__ . '/../src/App/UtilORM/Configs/Helper.php';
require_once __DIR__ . '/../src/App/UtilDateTime/Configs/Helper.php';
require_once __DIR__ . '/../src/App/UtilString/Configs/Helper.php';
require_once __DIR__ . '/../src/App/UtilSession/Configs/Helper.php';
require_once __DIR__ . '/../src/App/UtilCache/Configs/Helper.php';
require_once __DIR__ . '/../src/App/UtilLang/Configs/Helper.php';
require_once __DIR__ . '/../src/App/UtilResponse/Configs/Helper.php';
require_once __DIR__ . '/../src/App/UtilView/Configs/Helper.php';
