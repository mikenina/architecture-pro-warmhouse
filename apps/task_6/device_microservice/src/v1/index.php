<?php
$deviceApiGatewayBaseUrl = $_SERVER['DEVICE_API_GATEWAY'] ?? null;
$deviceDatabaseDSN = $_SERVER['DEVICE_DATABASE_DSN'] ?? null;
$deviceDatabaseUser = $_SERVER['POSTGRES_USER'] ?? null;
$deviceDatabasePwd = $_SERVER['POSTGRES_PASSWORD'] ?? null;

if (null === $deviceApiGatewayBaseUrl || null === $deviceDatabaseDSN || null === $deviceDatabaseUser || null === $deviceDatabasePwd) {
    header('Content-type: application/json');
    $response = [
        'code' => 500,
        'message' => 'Internal server error',
    ];
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

$curler = new Curler($deviceApiGatewayBaseUrl);
$devicePDO = new DevicePDO($deviceDatabaseDSN, $deviceDatabaseUser, $deviceDatabasePwd);
$deviceService = new DeviceService($curler, $devicePDO);

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

                return $this->deviceService->updateHealthcheckStatus($this->deviceId);
            },
            default => function () {
                http_response_code(400);
                exit;
            }
        };
        try {
            $result = $handler();
        } catch (NotFoundException $exception) {
            http_response_code(404);
            return;
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
        private readonly DevicePDO $devicePDO,
    ) {}

    public function createDevice(array $data): array
    {
        $sn = $data['serial_number'] ?? null;
        $deviceTypeId = $data['device_type_id'] ?? null;
        $userId = $data['user_id'] ?? null;
        $locationId = $data['location_id'] ?? null;
        $description = $data['description'] ?? null;

        $newDeviceId = $this->devicePDO->createDevice(
            (int) $sn,
            (int) $deviceTypeId,
            (int) $userId,
            (int) $locationId,
            (string) $description,
        );
        if (null === $newDeviceId) {
            throw new Exception('Unable to create device with serial_number: ' . $sn);
        }

        return $this->devicePDO->getDevice($newDeviceId) ?? [];
    }

    /**
     * @throws NotFoundException|Exception
     */
    public function updateHealthcheckStatus(int $deviceId): array
    {
        $device = $this->devicePDO->getDevice($deviceId);
        if (!$device) {
            throw new NotFoundException('Device not found', 404);
        }

        $path = sprintf('/device/%s/healthcheck', $device['serial_number'] ?: 0);
        $curlResponseDto = $this->curler->requestGET($path);
        $healthcheckStatus = match ($curlResponseDto->getStatusCode()) {
            200 => true, // device is available
            503 => false, // device is unavailable
            default => null,
        };
        if (null === $healthcheckStatus) {
            throw new Exception('Invalid External Device request', $curlResponseDto->getStatusCode());
        }

        $this->devicePDO->updateHealthcheckStatus($deviceId, $healthcheckStatus);
        return $this->devicePDO->getDevice($deviceId) ?? [];
    }

    public function getList(int $userId): array
    {
        try {
            return $this->devicePDO->getDeviceListByUserId($userId);
        } catch (Exception $e) {
            throw new Exception('Database error: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}

class DevicePDO extends PDO
{
    public function getDeviceListByUserId(int $userId): array
    {
        $query = sprintf('select * from device where user_id = %d order by device_id desc', $userId);
        $stmt = $this->query($query, PDO::FETCH_ASSOC);
        if ($stmt instanceof PDOStatement) {
            return $stmt->fetchAll();
        }
        throw new Exception($this->errorInfo()[2], $this->errorCode());
    }

    public function getDevice(int $deviceId): ?array
    {
        $query = sprintf('select * from device where device_id = %d', $deviceId);
        $stmt = $this->query($query, PDO::FETCH_ASSOC);
        if ($stmt instanceof PDOStatement) {
            return $stmt->fetch() ?: null;
        }
        throw new Exception($this->errorInfo()[2], $this->errorCode());
    }

    public function createDevice(int $sn, int $deviceTypeId, int $userId, int $locationId, string $desciption): ?int
    {
        $query = sprintf(
            'insert into device (serial_number, device_type_id, user_id, location_id, description) values 
            (%d, %d, %d, %d, %s)',
            $sn, $deviceTypeId, $userId, $locationId, $this->quote($desciption)
        );
        $cnt = $this->exec($query);

        if (!$cnt) {
            return null;
        }
        return $this->lastInsertId('device_device_id_seq');
    }

    public function updateHealthcheckStatus(int $deviceId, bool $healthcheckStatus): void
    {
        $query = sprintf(
            'update device set healthcheck_status = %s, healthcheck_datetime = %s where device_id = %d',
            $healthcheckStatus ? 'true' : 'false',
            $this->quote((new DateTimeImmutable)->format('Y-m-d H:i:s')),
            $deviceId
        );

        $this->exec($query);
    }
}

class NotFoundException extends Exception
{}