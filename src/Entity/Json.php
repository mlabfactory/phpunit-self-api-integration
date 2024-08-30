<?php
declare(strict_types=1);

namespace MLAB\PHPITest\Entity;

use JsonException;

final class Json {

    public readonly \stdClass|array $data;

    public function __construct(\stdClass|array $data)
    {
        $this->data = $data;
    }

    /**
     * Validate and return the decoded response JSON.
     *
     * @param  string|null  $key
     * @return mixed
     */
    public function json($key = null)
    {
         $this->decodeResponseJson();
         return $this->extractFromKey($key, false);
    }

    /**
     * Validate and return the decoded response JSON.
     *
     * @return self
     *
     * @throws \Throwable
     */
    public function decodeResponseJson(): self
    {
        $decodedResponse = $this->data;

        if (is_null($decodedResponse) || $decodedResponse === false) {
            throw new JsonException('Invalid JSON was returned from the route.');
        }

        $this->data = (array) $decodedResponse;

        return $this;
    }

   
    /**
     * Retrieve the response body as an array.
     *
     * @param string|null $key The key to retrieve from the response array.
     * @return array The response body as an array.
     */
    public function array($key = null)
    {
         $this->decodeResponseJson();
        return (array) $this->extractFromKey($key, true);
    }

    private function extractFromKey($key, bool $toArray = false)
    {
        $data = $this->data;

        if (is_null($key)) {
            return $data;
        }

        if (array_key_exists($key, (array) $data)) {
            return $toArray ? (array) $data[$key] : $data[$key];
        }

        $keys = explode('.', $key);
        $temp = $data;
        foreach ($keys as $k) {
            if (is_array($temp) && array_key_exists($k, $temp)) {
                $temp = $temp[$k];
            } else {
                throw new JsonException("The key [{$key}] was not found in the response.");
            }
        }

        return $toArray ? (array) $temp : $temp;

    }

}
