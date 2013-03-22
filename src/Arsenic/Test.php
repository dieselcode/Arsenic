<?php
/**
 * Arsenic Unit Testing
 * Copyright (c) 2013, Andrew Heebner, All rights reserved.
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3.0 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.
 */

namespace Arsenic;

class Test
{

    use AssertTrait;

    const ASSERT_NORMAL = false;
    const ASSERT_STRICT = true;

    private static $_suites         = array();
    private static $_currSuite      = null;

    private static $_currTest       = null;

    private static $_testResults    = array();
    private static $_fixtures       = array();


    private function __construct() {}

    public static function suite($description, \Closure $suite)
    {
        static::$_currSuite = static::_callableHash($suite);

        static::$_suites[static::$_currSuite] = array(
            'tests'       => array(),
            'description' => $description
        );

        $suite();
    }

    public static function setup(\Closure $callback)
    {
        static::$_suites[static::$_currSuite]['setup'] = $callback;
    }

    public static function tearDown(\Closure $callback)
    {
        static::$_suites[static::$_currSuite]['tearDown'] = $callback;
    }

    public static function fixture($key, $value = null)
    {
        if (isset($value)) {
            static::$_fixtures[$key] = $value;
        } else {
            return (array_key_exists($key, static::$_fixtures)) ? static::$_fixtures[$key] : null;
        }

        return false;
    }

    public static function test($description, \Closure $callback)
    {
        if (!is_null(static::$_currSuite)) {
            $test = array(
                'callback' => $callback,
                'description' => $description
            );

            static::$_suites[static::$_currSuite]['tests'][] = $test;
        }

        return false;
    }

    public static function run()
    {
        if (!empty(static::$_suites)) {
            foreach (static::$_suites as $suite) {
                echo 'Running tests for "' . $suite['description'] . '"' . PHP_EOL;

                if (array_key_exists('setup', $suite)) {
                    $suite['setup']();
                }


                if (!empty($suite['tests'])) {
                    foreach ($suite['tests'] as $test) {
                        echo ' - Test: "' . $test['description'] . '"' . PHP_EOL;
                        $test['callback']();
                    }
                }

                if (array_key_exists('tearDown', $suite)) {
                    $suite['tearDown']();
                }
            }
        }

    }

    private static function _addAssertionResult($func_name, $func_args, $result, $description = '')
    {
        $result = ($result) ? 'pass' : 'fail';
        static::$_testResults[static::$_currSuite][static::$_currTest][] = compact('func_name', 'func_args', 'result', 'description');
        return $result;
    }

    private static function _callableHash(callable $callable)
    {
        return md5(uniqid('Arsenic', true) . time());
    }

}

/**
 * Custom exception wrappers for internal use
 */
class TestException extends \Exception {}
class AssertionException extends \Exception {}

?>