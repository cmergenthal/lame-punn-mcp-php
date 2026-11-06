<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/LamePunRepository.php';
require_once __DIR__ . '/../src/McpServer.php';

use LamePunnMcp\LamePunRepository;
use LamePunnMcp\McpServer;

$expectedToken = getenv('DAD_JOKES_API_TOKEN') ?: 'change-me';
$tokenHeaderName = getenv('DAD_JOKES_TOKEN_HEADER') ?: 'X-Dad-Jokes-Token';
$repository = new LamePunRepository();
$server = new McpServer($expectedToken, $tokenHeaderName, $repository);
$server->handleHttpRequest();
