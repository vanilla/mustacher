<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2014 Vanilla Forums Inc.
 * @license MIT
 */

namespace Mustacher;

class Mustacher {
    const FORMAT_MUSTACHE = 'mustache';
    const FORMAT_MESSAGE = 'message';

    public static function formatString($format, $data) {
        $fmt = function($match) use ($data) {
            $match = $match[1];
            if ($match === '{') {
                return $match;
            }

            $parts = explode(',', $match);
            $field = trim($parts[0]);

            if (isset($data[$field])) {
                return $data[$field];
            } else {
                return '';
            }
        };

        $result = preg_replace_callback('/{([^\s][^}]+[^\s]?)}/', $fmt, $format);
        return $result;
    }

    public static function generate($template, $data, $format = self::FORMAT_MUSTACHE) {
        if (!$format) {
            $format = self::FORMAT_MUSTACHE;
        }
        switch ($format) {
            case self::FORMAT_MUSTACHE:
                $m = new \Mustache_Engine();
                $result = $m->render($template, $data);
                break;
            case self::FORMAT_MESSAGE:
                $result = static::formatString($template, $data);
                break;
            default:
                throw new \Exception("Invalid format $format.", 500);
        }

        return $result;
    }

    public static function generateFile($templatePath, $data, $format = self::FORMAT_MUSTACHE) {
        if (!file_exists($templatePath)) {
            throw new \Exception("File $templatePath does not exist.", 500);
        }
        $template = file_get_contents($templatePath);
        $result = static::generate($template, $data, $format);
        return $result;
    }

    /**
     * Merge a JSON file with a JSON string.
     *
     * @param string $jsonPath The path to a JSON file or empty.
     * @param string $jsonString A JSON formatted string. It can omit the opening and closing braces.
     * @return array Returns the array of data or null on error.
     * @throws \Exception Throws an exception when there is an error with the input parameters.
     */
    public static function mergeData($jsonPath, $jsonString) {
        if (!$jsonPath && !$jsonString) {
            throw new \Exception("You must provide either a path to a input file or a JSON string.", 400);
        }

        $result = [];

        // Add the input file to the result.
        if ($jsonPath) {
            if (!file_exists($jsonPath)) {
                throw new \Exception("The input file does not exist.", 400);
            }

            $data = json_decode(file_get_contents($jsonPath), true);
            if ($data === null) {
                throw new \Exception('There is an error in your input file: '.json_last_error_msg(), json_last_error());
            }

            $result = array_replace($result, $data);
        }

        // Add the JSON string to the result.
        $jsonString = trim($jsonString);
        if ($jsonString) {
            if (substr($jsonString, 0, 1) !== '{') {
                $jsonString = '{'.$jsonString;
            }
            if (substr($jsonString, -1) !== '}') {
                $jsonString .= '}';
            }

            $data = json_decode($jsonString, true);
            if ($data === null) {
                throw new \Exception('There is an error in your JSON string: '.json_last_error_msg(), json_last_error());
            }

            $result = array_replace($result, $data);
        }

        return $result;
    }
}
