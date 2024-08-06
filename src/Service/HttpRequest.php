<?php

namespace MLAB\PHPITest\Service;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use MLAB\PHPITest\Assertions\HttpAssert;

class HttpRequest
{

    private array $headers = [];
    private ResponseInterface $response;
    private int $statusCode;
    private CookieJar $cookies;
    private string $testDomain;

    private array $options; //Guzzle HTTP Request options https://docs.guzzlephp.org/en/stable/quickstart.html#making-a-request
    const DOMAIN_URL = "http://localhost";

    /**
     * HttpRequest constructor.
     *
     * @param array $options An array of options for the HttpRequest.
     */
    public function __construct($options = [], string $testDomain = self::DOMAIN_URL)
    {
        $this->options = $options;
        $this->testDomain = $testDomain;
        $this->setCookies([]);
    }

    /**
     *  invoke a request
     * @param string $method
     * @param string $uri
     * @param array $data
     * 
     * @return HttpAssert
     */
    private function invoke(string $method, string $uri, array $data = []): HttpAssert
    {
        $jar = $this->cookies;

        try {
            $client = new Client(
                $this->options
            );

            $this->response = $client->request($method, $this->testDomain . $uri, [
                'body' => json_encode($data),
            ]);

            $this->statusCode = $this->response->getStatusCode();
            $this->headers = $this->response->getHeaders();

            return new HttpAssert(new HttpResponse($this));
        } catch (\Throwable $e) {

            $this->statusCode = $e->getCode();
            return new HttpAssert(new HttpResponse($this));
        }
    }

    

    /**
     * send GET request
     * @param string $uri
     * 
     * @return HttpAssert
     */
    public function get(string $uri): HttpAssert
    {
        return $this->invoke("GET", $uri, []);
    }

    /**
     * send POST request
     * @param string $uri
     * @param array $data
     * 
     * @return HttpAssert
     */
    public function post(string $uri, array $data): HttpAssert
    {
        return $this->invoke("POST", $uri, $data);
    }

    /**
     * send PUT request
     * @param string $uri
     * @param array $data to send
     * 
     * @return HttpAssert
     */
    public function put(string $uri, array $data): HttpAssert
    {
        return $this->invoke("PUT", $uri, $data);
    }

    /**
     * send DELETE request
     * @param string $uri
     * 
     * @return HttpAssert
     */
    public function delete(string $uri): HttpAssert
    {
        return $this->invoke("DELETE", $uri, []);
    }

    /**
     * send OPTION request
     * @param string $uri
     * 
     * @return HttpAssert
     */
    public function option(string $uri): HttpAssert
    {
        return $this->invoke("OPTION", $uri, []);
    }

    /**
     * send PATCH request
     * @param string $uri
     * @param array $data
     * 
     * @return HttpAssert
     */
    public function patch(string $uri, array $data): HttpAssert
    {
        return $this->invoke("PATCH", $uri, $data);
    }

    /**
     * Sends an HTTP request.
     *
     * @param string $method The HTTP method (e.g., GET, POST, PUT, DELETE).
     * @param string $uri The URI to send the request to.
     * @param array $data The data to send with the request (optional).
     *
     * @return HttpAssert The HTTP response object.
     */
    public function request(string $method, string $uri, array $data = []): HttpAssert
    {
        return $this->invoke($method, $uri, $data);
    }

    /**
     * Get the value of statusCode
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the value of content
     *
     * @return string
     */
    public function getContent(): StreamInterface
    {
        return $this->response->getBody();
    }

    /**
     *  GuzzleHttp\Promise\PromiseInterface that is fulfilled with a
     *  Psr7\Http\Message\ResponseInterface on success.
     */
    public function isSuccessful(): bool
    {
        if (!$this->response instanceof ResponseInterface) {
            return false;
        }

        return true;
    }

    /**
     * Get the value of headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get the value of headers
     *
     * @return array
     */
    public function getStatusCodeRedirect(): string
    {
        return $this->headers[\GuzzleHttp\RedirectMiddleware::STATUS_HISTORY_HEADER];
    }

    /**
     * Get the value of cookies
     *
     * @return GuzzleHttp\Cookie\CookieJar\null
     */
    public function getCookies(string $name): \GuzzleHttp\Cookie\SetCookie|null
    {
        return $this->cookies->getCookieByName($name);
    }

    /**
     * Set the value of cookies
     *
     * @param array $cookies
     *
     * @return self
     */
    public function setCookies(array $cookies): self
    {
        $this->cookies = CookieJar::fromArray(
            $cookies,
            self::DOMAIN_URL
        );

        return $this;
    }
}
