<?php
declare(strict_types=1);

namespace MLAB\PHPITest\Assertions;

use PHPUnit\Framework\Assert;
use MLAB\PHPITest\Constraint\Str;
use MLAB\PHPITest\Constraint\Arr;
use stdClass;

class Assertions extends Assert {

    protected mixed $decoded;

    public function __construct(stdClass|array $decoded)
    {
        if(is_array($decoded)) {
            $this->decoded = $decoded;
        } else {
            $this->decoded = (array) $decoded;
        }
    }

    /**
     * Validate and return the decoded response JSON.
     *
     * @param  string|null  $key
     * @return mixed
     */
    public function json($key = null)
    {
        return data_get($this->decoded, $key);
    }

    /**
     * Assert that the expected value and type exists at the given path in the response.
     *
     * @param  string  $path 
     * @param  mixed  $expect
     * @return $this
     */
    final public function assertPath($path, $expect)
    {
        if ($expect instanceof \Closure) {
            $this->assertTrue($expect($this->json($path)));
        } else {
            $this->assertSame($expect, $this->json($path));
        }

        return $this;
    }

    /**
     * Assert that the given path in the response contains all of the expected values without looking at the order.
     *
     * @param  string  $path
     * @param  array  $expect
     * @return $this
     */
    public function assertPathCanonicalizing($path, $expect)
    {
        $this->assertEqualsCanonicalizing($expect, $this->json($path));

        return $this;
    }

    /**
     * Assert that the response has the exact given JSON.
     *
     * @param  array  $data
     * @return $this
     */
    public function assertExact(array $data)
    {
        $actual = $this->reorderAssocKeys((array) $this->decoded);

        $expected = $this->reorderAssocKeys($data);

        $this->assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            json_encode($actual, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        return $this;
    }

    /**
     * Reorder associative array keys to make it easy to compare arrays.
     *
     * @param  array  $data
     * @return array
     */
    protected function reorderAssocKeys(array $data)
    {
        $data = Arr::dot($data);
        ksort($data);

        $result = [];

        foreach ($data as $key => $value) {
            Arr::set($result, $key, $value);
        }

        return $result;
    }

    /**
     * Assert that the response has the similar JSON as given.
     *
     * @param  array  $data
     * @return $this
     */
    public function assertSimilar(array $data)
    {
        $actual = json_encode(
            Arr::sortRecursive((array) $this->decoded),
            JSON_UNESCAPED_UNICODE
        );

        $this->assertEquals(json_encode(Arr::sortRecursive($data), JSON_UNESCAPED_UNICODE), $actual);

        return $this;
    }

    /**
     * Assert that the response contains the given JSON fragment.
     *
     * @param  array  $data
     * @return $this
     */
    public function assertFragment(array $data)
    {
        $actual = json_encode(
            Arr::sortRecursive((array) $this->decoded),
            JSON_UNESCAPED_UNICODE
        );

        foreach (Arr::sortRecursive($data) as $key => $value) {
            $expected = $this->jsonSearchStrings($key, $value);

            $this->assertTrue(
                Str::contains($actual, $expected),
                'Unable to find JSON fragment: '.PHP_EOL.PHP_EOL.
                '['.json_encode([$key => $value], JSON_UNESCAPED_UNICODE).']'.PHP_EOL.PHP_EOL.
                'within'.PHP_EOL.PHP_EOL.
                "[{$actual}]."
            );
        }

        return $this;
    }

    /**
     * Assert that the response does not contain the given JSON fragment.
     *
     * @param  array  $data
     * @param  bool  $exact
     * @return $this
     */
    public function assertMissing(array $data, $exact = false)
    {
        if ($exact) {
            return $this->assertMissingExact($data);
        }

        $actual = json_encode(
            Arr::sortRecursive((array) $this->decoded),
            JSON_UNESCAPED_UNICODE
        );

        foreach (Arr::sortRecursive($data) as $key => $value) {
            $unexpected = $this->jsonSearchStrings($key, $value);

            $this->assertFalse(
                Str::contains($actual, $unexpected),
                'Found unexpected JSON fragment: '.PHP_EOL.PHP_EOL.
                '['.json_encode([$key => $value], JSON_UNESCAPED_UNICODE).']'.PHP_EOL.PHP_EOL.
                'within'.PHP_EOL.PHP_EOL.
                "[{$actual}]."
            );
        }

        return $this;
    }

    /**
     * Get the strings we need to search for when examining the JSON.
     *
     * @param  string  $key
     * @param  string  $value
     * @return array
     */
    private function jsonSearchStrings($key, $value)
    {
        $needle = Str::substr(json_encode([$key => $value], JSON_UNESCAPED_UNICODE), 1, -1);

        return [
            $needle.']',
            $needle.'}',
            $needle.',',
        ];
    }

    /**
     * Assert that the response does not contain the exact JSON fragment.
     *
     * @param  array  $data
     * @return $this
     */
    public function assertMissingExact(array $data)
    {
        $actual = json_encode(
            Arr::sortRecursive((array) $this->decoded),
            JSON_UNESCAPED_UNICODE
        );

        foreach (Arr::sortRecursive($data) as $key => $value) {
            $unexpected = $this->jsonSearchStrings($key, $value);

            if (! Str::contains($actual, $unexpected)) {
                return $this;
            }
        }

        $this->fail(
            'Found unexpected JSON fragment: '.PHP_EOL.PHP_EOL.
            '['.json_encode($data, JSON_UNESCAPED_UNICODE).']'.PHP_EOL.PHP_EOL.
            'within'.PHP_EOL.PHP_EOL.
            "[{$actual}]."
        );

        return $this;
    }

    /**
     * Assert that the response does not contain the given path.
     *
     * @param  string  $path
     * @return $this
     */
    public function assertMissingPath($path)
    {   
        if(Arr::has($this->json(), $path) === false) {
            $this->isFalse();
        }

        return $this;
    }/**
     * Assert that the response has a given JSON structure.
     *
     * @param  array|null  $structure
     * @param  array|null  $responseData
     * @return $this
     */
    public function assertStructure(array $structure = null, $responseData = null)
    {

        if (is_null($structure)) {
            return $this->assertSimilar($this->decoded);
        }

        if (! is_null($responseData)) {
            return (new self($responseData))->assertStructure($structure);
        }

        foreach ($structure as $key => $value) {
            if (is_array($value) && $key === '*') {
                $this->assertIsArray($this->decoded);

                foreach ($this->decoded as $responseDataItem) {
                    $this->assertStructure($structure['*'], $responseDataItem);
                }
            } elseif (is_array($value) || ($value instanceof \stdClass)) {
                $this->assertArrayHasKey($key, $this->decoded);

                $this->assertStructure($structure[$key], $this->decoded[$key]);
            } else {
                $this->assertArrayHasKey($value, $this->decoded);
            }
        }

        return $this;
    }

}