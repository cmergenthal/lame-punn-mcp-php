# Lame Punn MCP Server in PHP

This project is a minimal MCP server written in plain PHP. It exposes one tool, `get_lame_pun`, and protects every MCP request with the same custom token auth used by the dad jokes MCP server.

## What it does

- Accepts MCP JSON-RPC requests over HTTP `POST`
- Requires the dad jokes auth header, such as `X-Dad-Jokes-Token: <token>`
- Supports `initialize`, `ping`, `tools/list`, and `tools/call`
- Returns a random lame pun when the `get_lame_pun` tool is called

## Project structure

- `public/index.php` - HTTP entry point
- `src/McpServer.php` - MCP protocol handling
- `src/LamePunRepository.php` - pun data source

## Run it

Make sure PHP is installed locally, then start the built-in server:

```bash
DAD_JOKES_API_TOKEN=super-secret-token php -S 127.0.0.1:8081 -t public
```

To change the header name, use the same environment variable as the dad jokes server:

```bash
DAD_JOKES_API_TOKEN=super-secret-token \
DAD_JOKES_TOKEN_HEADER=X-Widget-Token \
php -S 127.0.0.1:8081 -t public
```

## Example request

Set `MCP_ENDPOINT` to your server's endpoint before running this request.

```bash
curl -X POST "$MCP_ENDPOINT" \
  -H "Content-Type: application/json" \
  -H "X-Dad-Jokes-Token: super-secret-token" \
  -d '{
    "jsonrpc": "2.0",
    "id": 1,
    "method": "tools/call",
    "params": {
      "name": "get_lame_pun",
      "arguments": {
        "category": "classic"
      }
    }
  }'
```

## Example MCP flow

1. Send `initialize`
2. Send `tools/list`
3. Send `tools/call` with `name: "get_lame_pun"`

## Notes

- The server reads the token from the `DAD_JOKES_API_TOKEN` environment variable.
- The header name defaults to `X-Dad-Jokes-Token`.
- You can override the header name with `DAD_JOKES_TOKEN_HEADER`.
- If that variable is missing, it falls back to `change-me`.
- Categories currently supported are `classic`, `food`, `animals`, and `work`.
