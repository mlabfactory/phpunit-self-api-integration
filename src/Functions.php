<?php

if (!function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param  mixed  $target
     * @param  string|array|int|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    function data_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        foreach ($key as $segment) {
            if (is_null($segment)) {
                return $target;
            }

            if ($segment === '*') {
                if (is_a($target, 'Collection')) {
                    $target = $target->all();  //@phpstan-ignore-line */
                } elseif (!is_iterable($target)) {
                    return $default;
                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = data_get($item, $key);
                }

                return in_array('*', $key) ? array_merge(...$result) : $result;
            }

            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = $target[$segment];
            } elseif (is_object($target) && property_exists($target, $segment)) {
                $target = $target->{$segment};
            } else {
                return $default;
            }
        }

        return $target;
    }
}

if (!function_exists('remove_keys_from_object')) {
    /**
     * Remove a key from an object recursively.
     *
     * @param  object  $object
     * @param  array  $keys
     * @return object
     */
    function remove_keys_from_object(object $object, array $keys)
    {
        if (is_object($object)) {
            foreach ($object as $property => $value) {
                if (is_object($value)) {
                    $object->{$property} = remove_keys_from_object($value, $keys);
                } else {
                    foreach ($keys as $key) {
                        if ($property === $key) {
                            unset($object->{$property});
                        }
                    }
                }
            }
        }

        return $object;
    }
}

if(!function_exists('extractIndices')) {
    /**
     * Extract indices from a multidimensional array.
     *
     * @param array $data
     * @return array
     */
    function extractIndices(array $data)
    {
        $indices = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $indices[$key] = extractIndices($value);
            } else {
                $indices[] = $key;
            }
        }
        
        return $indices;
    }
}