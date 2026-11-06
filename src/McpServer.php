<?php

declare(strict_types=1);

namespace LamePunnMcp;

final class McpServer
{
    public function __construct(
        private readonly string $expectedToken,
        private readonly string $tokenHeaderName,
        private readonly LamePunRepository $repository
    ) {
    }

    public function handleHttpRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJson(
                [
                    'error' => 'Method not allowed. Send MCP JSON-RPC requests with POST.',
                ],
                405
            );
            return;
        }

        if (!$this->isAuthorized()) {
            $this->sendJson(
                [
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => -32001,
                        'message' => sprintf('Unauthorized. Supply the token in the %s header.', $this->tokenHeaderName),
                    ],
                ],
                401
            );
            return;
        }

        $rawBody = file_get_contents('php://input');
        $request = json_decode($rawBody ?: '', true);

        if (!is_array($request)) {
            $this->sendJson(
                [
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => -32700,
                        'message' => 'Parse error',
                    ],
                ],
                400
            );
            return;
        }

        $response = $this->dispatch($request);

        if ($response === null) {
            http_response_code(202);
            return;
        }

        $this->sendJson($response, 200);
    }

    private function isAuthorized(): bool
    {
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $this->tokenHeaderName));
        $header = $_SERVER[$serverKey] ?? '';

        if (!is_string($header) || trim($header) === '') {
            return false;
        }

        return hash_equals($this->expectedToken, trim($header));
    }

    private function dispatch(array $request): ?array
    {
        $method = $request['method'] ?? null;
        $id = $request['id'] ?? null;
        $params = $request['params'] ?? [];

        if (!is_string($method)) {
            return $this->errorResponse($id, -32600, 'Invalid Request');
        }

        return match ($method) {
            'initialize' => $this->initializeResponse($id),
            'ping' => $this->successResponse($id, ['pong' => true]),
            'notifications/initialized' => null,
            'tools/list' => $this->toolsListResponse($id),
            'tools/call' => $this->toolsCallResponse($id, is_array($params) ? $params : []),
            default => $this->errorResponse($id, -32601, 'Method not found'),
        };
    }

    private function initializeResponse(mixed $id): array
    {
        return $this->successResponse(
            $id,
            [
                'protocolVersion' => '2025-03-26',
                'serverInfo' => [
                    'name' => 'lame-punn-mcp-php',
                    'version' => '1.0.0',
                ],
                'capabilities' => [
                    'tools' => new \stdClass(),
                ],
            ]
        );
    }

    private function toolsListResponse(mixed $id): array
    {
        return $this->successResponse(
            $id,
            [
                'tools' => [
                    [
                        'name' => 'get_lame_pun',
                        'title' => 'Get Lame Pun',
                        'description' => 'Returns a random lame pun, optionally filtered by category.',
                        'inputSchema' => [
                            'type' => 'object',
                            'properties' => [
                                'category' => [
                                    'type' => 'string',
                                    'description' => 'Optional pun category.',
                                    'enum' => $this->repository->categories(),
                                ],
                            ],
                            'additionalProperties' => false,
                        ],
                    ],
                ],
            ]
        );
    }

    private function toolsCallResponse(mixed $id, array $params): array
    {
        $toolName = $params['name'] ?? '';
        $arguments = $params['arguments'] ?? [];

        if ($toolName !== 'get_lame_pun') {
            return $this->errorResponse($id, -32602, 'Unknown tool');
        }

        if (!is_array($arguments)) {
            return $this->errorResponse($id, -32602, 'Tool arguments must be an object');
        }

        $category = $arguments['category'] ?? null;
        if ($category !== null && !is_string($category)) {
            return $this->errorResponse($id, -32602, 'Category must be a string');
        }

        $pun = $this->repository->random($category);

        return $this->successResponse(
            $id,
            [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => sprintf('[%s] %s', $pun['category'], $pun['pun']),
                    ],
                ],
                'structuredContent' => $pun,
            ]
        );
    }

    private function successResponse(mixed $id, array $result): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result,
        ];
    }

    private function errorResponse(mixed $id, int $code, string $message): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];
    }

    private function sendJson(array $payload, int $statusCode): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    }
}
