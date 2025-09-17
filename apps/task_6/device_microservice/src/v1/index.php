<?php
$deviceApiGatewayBaseUrl = $_SERVER['DEVICE_API_GATEWAY'] ?? null;
if (null === $deviceApiGatewayBaseUrl) {
    header('Content-type: application/json');
    $response = [
        'code' => 500,
        'message' => 'Internal server error',
    ];
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

$curler = new Curler($deviceApiGatewayBaseUrl);
$deviceService = new DeviceService($curler);

$router = new Api($_SERVER, $_GET, $deviceService);
$router->run();
exit;

class Api
{
    private const CMD_CREATE = 'create';
    private const CMD_LIST = 'list';
    private const CMD_HEALTHCHECK = 'healthcheck';

    private const KNOWN_CMD = [self::CMD_HEALTHCHECK, self::CMD_CREATE, self::CMD_LIST];

    private ?int $deviceId = null;
    private ?int $userId = null;

    private ?string $command = null;
    private ?array $httpParsedBody = [];

    public function __construct(
        private readonly array $server,
        private readonly array $get,
        private readonly DeviceService $deviceService,
    ) {}

    public function run(): void
    {
        $this->parseRequest();

        if ((null === $this->command)
            || !in_array($this->command, self::KNOWN_CMD, true)
        ) {
            http_response_code(400);
            return;
        }
//var_dump($this->command, $this->deviceId, $this->httpParsedBody, $this->userId);
//        return;
        $handler = match ($this->command) {
            self::CMD_CREATE => function () {
                if (!is_array($this->httpParsedBody)) {
                    throw new Exception('Invalid POST data', 400);
                }
                return $this->deviceService->createDevice($this->httpParsedBody);
            },
            self::CMD_LIST => function () {
                if (null === $this->userId) {
                    throw new Exception('Invalid userId in GET', 400);
                }

                return $this->deviceService->getList($this->userId);
            },
            self::CMD_HEALTHCHECK => function () {
                if (null === $this->deviceId) {
                    throw new Exception('Invalid deviceId in PATH', 400);
                }

                return $this->deviceService->updateAvailabilityStatus($this->deviceId);
            },
            default => function () {
                http_response_code(400);
                exit;
            }
        };
        try {
            $result = $handler();
        } catch (Exception $exception) {
            $result = [
                'code' => $exception->getCode() ?? 500,
                'message' => $exception->getMessage(),
            ];
        }
        header('Content-type: application/json');
        echo json_encode($result, JSON_PRETTY_PRINT);
    }

    private function parseRequest(): void
    {
        $pathInfo = $_SERVER['PATH_INFO'] ?: $this->preparePathInfo();

        if (preg_match('/^(\/device\/?)$/', $pathInfo)
            && ('POST' === $_SERVER['REQUEST_METHOD'])
            && ('application/json' === $_SERVER['HTTP_CONTENT_TYPE'])
            && $_SERVER['CONTENT_LENGTH'] > 0)
        {
            $content = file_get_contents('php://input');
            $this->httpParsedBody = json_decode($content, true, 512, \JSON_BIGINT_AS_STRING);
            $this->command = self::CMD_CREATE;
            return;
        }

        if (preg_match('/^(\/device\/list\/?)$/', $pathInfo)
            && ('GET' === $_SERVER['REQUEST_METHOD'])
            && !empty($this->get['userId'])
        ) {
            $this->userId = (int) $this->get['userId'];
            $this->command = self::CMD_LIST;
            return;
        }

        preg_match('/^(\/device\/)(\d*)\/([a-z]*)$/', $pathInfo, $matches);
        $this->deviceId = !empty($matches[2]) ? $matches[2] : null;
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

class Curler
{
    public function __construct(private readonly string $baseUrl)
    {}

    public function requestGET(string $url): CurlResponseDto
    {
        $path = $this->baseUrl . $url;
        $ch = curl_init();
        if (!$ch) {
            throw new Exception('Unable to init curl');
        }

        curl_setopt_array(
            $ch,
            [
                CURLOPT_URL => $path,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
            ]
        );

        $responseBody = (string) curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrorMessage = curl_error($ch);
        $curlErrorCode = curl_errno($ch);
        curl_close($ch);

        if (CURLE_OK !== $curlErrorCode) {
            throw new Exception($curlErrorMessage);
        }

        return new CurlResponseDto($responseBody, $httpCode);
    }
}

class CurlResponseDto {
    public function __construct(
        private readonly string $body,
        private readonly int $statusCode = 0,
    ) {}

    public function getBody(): string {
        return $this->body;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }
}

class DeviceService
{
    public function __construct(
        private readonly Curler $curler,
    ) {}

    public function createDevice(array $data): array
    {
        //todo db
return ['created'];
    }

    public function updateAvailabilityStatus(int $deviceId): array
    {
        //todo db

        $path = sprintf('/device/%s/healthcheck', $deviceId);
        $curlResponseDto = $this->curler->requestGET($path);
        $availabilityStatus = match ($curlResponseDto->getStatusCode()) {
            200 => 1, // device is available
            503 => 0, // device is unavailable
            default => null,
        };
        if (null === $availabilityStatus) {
            throw new Exception('Invalid External Device request', $curlResponseDto->getStatusCode());
        }

        // todo db

        return ['availabilityStatus' => $availabilityStatus];
    }

    public function getList(int $userId): array
    {
        //todo db
return ['list'];
    }
}