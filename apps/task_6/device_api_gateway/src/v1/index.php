<?php

$router = new Api($_SERVER);
$router->run();
exit;

class Api
{
    private const KNOWN_CMD = ['healthcheck'];
    private const HEALTH_DEVICES = ['3481097512'];

    private ?string $serialNumber = null;

    private ?string $command = null;

    public function __construct(private readonly array $server)
    {}

    public function run(): void
    {
        $this->parseRequest();

        if (null === $this->serialNumber || null === $this->command || !in_array($this->command, self::KNOWN_CMD, true)) {
            http_response_code(400);
            return;
        }

        $res = match ($this->command) {
            'healthcheck' => static function (string $serialNumber) {
                if (in_array($serialNumber, self::HEALTH_DEVICES, true)) {
                    http_response_code(200);
                    return;
                }
                http_response_code(503);
            },
            default => function () {
                http_response_code(400);
            }
        };
        $res($this->serialNumber);
    }

    private function parseRequest(): void
    {
        $pathInfo = $_SERVER['PATH_INFO'] ?: $this->preparePathInfo();
        preg_match('/^(\/device\/)(\d*)\/([a-z]*)$/', $pathInfo, $matches);
        $this->serialNumber = !empty($matches[2]) ? $matches[2] : null;
        $this->command = !empty($matches[3]) ? $matches[3] : null;
    }

    private function preparePathInfo(): string
    {
        if (null === ($requestUri = $this->server['REQUEST_URI'])) {
            return '/';
        }

        if (false !== $pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        $requestUri = str_replace($_SERVER['PATH_PREFIX'], '', $requestUri);

        if ('' !== $requestUri && '/' !== $requestUri[0]) {
            $requestUri = '/' . $requestUri;
        }

        return rawurldecode($requestUri);
    }
}
