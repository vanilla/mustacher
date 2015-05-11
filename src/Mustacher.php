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

    public static function generateFile($templatePath, $dataPath, $format = self::FORMAT_MUSTACHE) {
        if (!file_exists($templatePath)) {
            throw new \Exception("File $templatePath does not exist.", 500);
        }
        if (!file_exists($dataPath)) {
            throw new \Exception("File $dataPath does not exist.", 500);
        }

        $template = file_get_contents($templatePath);
        $data = json_decode(file_get_contents($dataPath), true);

        if ($data === false) {
            throw new \Exception(json_last_error_msg(), json_last_error());
        }

        $result = static::generate($template, $data, $format);
        return $result;
    }
}
