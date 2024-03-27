<?php
namespace MLAB\PHPITest\Constraint;

/**
 * Utils for strings manager
 */
final class Str
{

    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string  $haystack
     * @param  string  $needles
     * @param  bool  $ignoreCase
     * @return bool
     */
    public static function contains($haystack, $needles, $ignoreCase = false)
    {
        if ($ignoreCase) {
            $haystack = mb_strtolower($haystack);
        }

        if (! is_iterable($needles)) {
            $needles = (array) $needles;
        }

        foreach ($needles as $needle) {
            if ($ignoreCase) {
                $needle = mb_strtolower($needle);
            }

            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }    
    
    /**
    * Returns the portion of the string specified by the start and length parameters.
    *
    * @param  string  $string
    * @param  int  $start
    * @param  int|null  $length
    * @param  string  $encoding
    * @return string
    */
   public static function substr($string, $start, $length = null, $encoding = 'UTF-8')
   {
       return mb_substr($string, $start, $length, $encoding);
   }
}
