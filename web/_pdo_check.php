<?php

header('Content-type: text/plain');
echo PHP_VERSION, PHP_EOL;
echo PHP_SAPI, PHP_EOL;
echo php_ini_loaded_file(), PHP_EOL;
print_r(PDO::getAvailableDrivers());