<?php
require_once('VatsimParser.php');
use Parser\VatsimParser;

header('Content-Type: Application/json');

/**
 *  By Adam Eastwood
 */
$parser = new VatsimParser;
echo $parser->data;