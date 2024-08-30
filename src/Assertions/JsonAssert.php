<?php

namespace MLAB\PHPITest\Assertions;

use MLAB\PHPITest\Entity\Json;

final class JsonAssert {

    private readonly Json $json;
    private readonly Assertions $assertions;

    public function __construct(Json $json)
    {
        $this->json = $json;
        $this->assertions = new Assertions($json->data);
    }

    /**
     * Assert that the expected value and type exists at the given path in the response.
     *
     * @param  string  $path
     * @param  mixed  $expect
     * @return $this
     */
    public function assertJsonPath($path, $expect)
    {
        $this->assertions->assertPath($path, $expect);

        return $this;
    }

    /**
     * Assert that the given path in the response contains all of the expected values without looking at the order.
     *
     * @param  string  $path
     * @param  array  $expect
     * @return $this
     */
    public function assertJsonPathCanonicalizing($path, array $expect)
    {
        $this->assertions->assertPathCanonicalizing($path, $expect);

        return $this;
    }

    /**
     * Assert that the response has the exact given JSON.
     *
     * @param  array  $data
     * @return $this
     */
    public function assertExactJson(array $data)
    {
        $this->assertions->assertExact($data);

        return $this;
    }

    /**
     * Assert that the response has the similar JSON as given.
     *
     * @param  array  $data
     * @return $this
     */
    public function assertSimilarJson(array $data)
    {
        $this->assertions->assertSimilar($data);

        return $this;
    }

    /**
     * Assert that the response contains the given JSON fragment.
     *
     * @param  array  $data
     * @return $this
     */
    public function assertJsonFragment(array $data)
    {
        $this->assertions->assertFragment($data);

        return $this;
    }

    /**
     * Assert that the response does not contain the given JSON fragment.
     *
     * @param  array  $data
     * @param  bool  $exact
     * @return $this
     */
    public function assertJsonMissing(array $data, $exact = false)
    {
        $this->assertions->assertMissing($data, $exact);

        return $this;
    }

    /**
     * Assert that the response does not contain the exact JSON fragment.
     *
     * @param  array  $data
     * @return $this
     */
    public function assertJsonMissingExact(array $data)
    {
        $this->assertions->assertMissingExact($data);

        return $this;
    }

    /**
     * Assert that the response does not contain the given path.
     *
     * @param  string  $path
     * @return $this
     */
    public function assertJsonMissingPath(string $path)
    {
        $this->assertions->assertMissingPath($path);

        return $this;
    }

    /**
     * Assert that the response has a given JSON structure.
     *
     * @param  array|null  $structure
     * @param  array|null  $responseData
     * @return $this
     */
    public function assertJsonStructure(array $structure = null, $responseData = null)
    {
        $structure = extractIndices($structure);
        $this->assertions->assertStructure($structure, $responseData);
        return $this;
    }

    /**
     * Assert that the given key is a JSON array.
     *
     * @param  string|null  $key
     * @return $this
     */
    public function assertJsonIsArray($key = null)
    {
        $data = $this->json->json($key);

        $encodedData = json_encode($data);

        $this->assertions->assertTrue(
            is_array($data)
                && str_starts_with($encodedData, '[')
                && str_ends_with($encodedData, ']')
        );

        return $this;
    }

    /**
     * Assert that the given key is a JSON object.
     *
     * @param  string|null  $key
     * @return $this
     */
    public function assertJsonIsObject($key = null)
    {
        $data = $this->json->json($key);

        $encodedData = json_encode($data);

        $this->assertions->assertTrue(
            is_array($data)
                && str_starts_with($encodedData, '{')
                && str_ends_with($encodedData, '}')
        );

        return $this;
    }

    /**
     * Asserts that the JSON response from the specified file path is equal to the expected JSON response.
     *
     * @param string $filePath The file path of the JSON response to compare.
     * @param array $keysToRemove The keys to remove from the JSON response.
     * @return self
     */
    public function assertJsonIsEqualJsonFile(string $filePath, array $keysToRemove = [])
    {
        $responseData = remove_keys_from_object(array_to_object($this->json->data), $keysToRemove);
        $this->assertions->assertJsonStringEqualsJsonFile($filePath, json_encode($responseData));

        return $this;
    }

    /**
     * Asserts that the given JSON object is equal to the expected JSON object.
     *
     * @param object|array $json The JSON object to compare.
     * @param array $keysToRemove An optional array of keys to remove from the JSON object before comparison.
     * @return void
     */
    public function assertJsonIsEqualToJson(object|array $json, array $keysToRemove = [])
    {
        if(is_array($json)) {
            $json = array_to_object($json);
        }

        $responseData = remove_keys_from_object($json, $keysToRemove);
        $jsonData = remove_keys_from_object(array_to_object($this->json->data), $keysToRemove);
        $this->assertions->assertJsonStringEqualsJsonString(json_encode($responseData), json_encode($jsonData));

        return $this;
    }

}