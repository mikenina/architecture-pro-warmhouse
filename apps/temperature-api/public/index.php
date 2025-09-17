<?php
$router = new Api($_SERVER, $_GET);
$router->run();

class Api
{
    private ?string $location = null;
    private ?string $sensorId = null;

    public function __construct(
        private readonly array $server,
        private readonly array $get,
    ) {}

    public function run(): void
    {
        $this->parseRequest();

        if (null === $this->location && null === $this->sensorId) {
            http_response_code(400);
            return;
        }

        $temperatureValue = random_int(10, 29) + random_int(0, 100)/100;

        $location = $this->location ?? $this->getLocationBySensorId($this->sensorId);
        $sensorId = $this->sensorId ?? $this->getSensorIdByLocation($this->location);
        $response = [
            'value' => $temperatureValue,
            'unit' => "°C",
            'timestamp' => (new DateTimeImmutable())->format('c'),
            'location' => $location,
            'status' => 'active',
            'sensor_id' => $sensorId,
            'sensor_type' => 'temperature',
            'description'  => sprintf('Temperature sensor in %s', $location),
        ];
        header('Content-type: application/json');
        echo json_encode($response);
    }

    private function getLocationBySensorId(string $sensorId): string
    {
        return match ($sensorId) {
            '1' => 'LivingRoom',
            '2' => 'Bedroom',
            '3' => 'Kitchen',
            default => 'Unknown',
        };
    }

    private function getSensorIdByLocation(string $location): string
    {
        return match ($location) {
            'Living Room' => '1',
            'Bedroom' => '2',
            'Kitchen' => '3',
            default => '0',
        };
    }

    private function parseRequest(): void
    {
        $location = '';
        if (!empty($this->get['location'])) {
            $location = (string) $this->get['location'];
        }

        if (!empty($location)) {
            $this->location = $location;
        }

        $pathInfo = $_SERVER['PATH_INFO'] ?: $this->preparePathInfo();
        preg_match('/^(\/temperature\/*)(\d*)$/', $pathInfo, $matches);
        $this->sensorId = $matches[2] ?: null;
    }

    private function preparePathInfo(): string {
        if (null === ($requestUri = $this->server['REQUEST_URI'])) {
            return '/';
        }

        if (false !== $pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }
        if ('' !== $requestUri && '/' !== $requestUri[0]) {
            $requestUri = '/' . $requestUri;
        }

        return rawurldecode($requestUri);
    }
}