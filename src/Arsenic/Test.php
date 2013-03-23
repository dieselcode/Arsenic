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

    const ASSERT_NORMAL     = false;
    const ASSERT_STRICT     = true;
    const FLAG_NORMAL  = 'normal';
    const FLAG_SKIP    = 'skip';

    private static $_suites         = array();
    private static $_currSuite      = null;

    private static $_currTest       = null;

    private static $_testResults    = array();
    private static $_testRunTime    = null;
    private static $_fixtures       = array();


    private function __construct() {}

    public static function suite($description, \Closure $suite)
    {
        // start the timer...
        static::$_testRunTime = -microtime(true);

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

    public static function test($description, \Closure $callback, $flag = self::FLAG_NORMAL)
    {
        if (!is_null(static::$_currSuite)) {
            $test = array(
                'callback' => $callback,
                'description' => $description,
                'flag' => $flag
            );

            static::$_suites[static::$_currSuite]['tests'][static::_callableHash()] = $test;
        }

        return false;
    }

    public static function run()
    {
        if (!empty(static::$_suites)) {

            foreach (static::$_suites as $keySuite => $suite) {
                echo PHP_EOL . 'Running tests for "' . $suite['description'] . '"' . PHP_EOL;

                if (array_key_exists('setup', $suite)) {
                    $suite['setup']();
                }


                if (!empty($suite['tests'])) {
                    foreach ($suite['tests'] as $keyTest => $test) {
                        echo PHP_EOL . ' - Test: "' . $test['description'] . '"' . PHP_EOL;

                        if ($test['flag'] == self::FLAG_SKIP) {
                            echo '   ------- SKIPPED -------' . PHP_EOL;
                        } else {
                            $test['callback']();
                        }
                    }
                }

                if (array_key_exists('tearDown', $suite)) {
                    $suite['tearDown']();
                }

                echo PHP_EOL . str_repeat('-', 50) . PHP_EOL;
            }

            static::$_testRunTime += microtime(true);
            static::_totalResults();
        }
    }

    private static function _addAssertionResult($func_name, $func_args, $result, $description = '')
    {
        $result = ($result) ? 'pass' : 'fail';
        static::$_testResults[static::$_currSuite][static::$_currTest][] = compact('func_name', 'func_args', 'result', 'description');
        return $result;
    }

    private static function _callableHash()
    {
        return md5(uniqid(__CLASS__ . __METHOD__, true) . time() . spl_object_hash(new \stdClass()));
    }

    private static function _totalResults()
    {
        $assertions = 0;
        $passes     = 0;
        $fails      = 0;

        foreach (static::$_testResults as $keySuite => $suite) {
            foreach ($suite as $keyTest => $test) {
                foreach ($test as $key => $assertion) {
                    $assertions++;
                    if ($assertion['result'] == 'pass') {
                        $passes++;
                    } else {
                        $fails++;
                    }
                }
            }
        }

        $successPercent = number_format(($passes / $assertions) * 100) . '%';

        static::out(sprintf(
            "Totals: %d assertions; %d passed, %d failed - (%s success)%sExecution time: %.4f seconds",
            $assertions, $passes, $fails, $successPercent, PHP_EOL, static::$_testRunTime
        ), true, ($fails === 0) ? 'green' : 'red');

        exit(($fails === 0) ? 0 : 1);
    }

    protected static function out($message)
    {
        echo $message . PHP_EOL;
    }

}

class AssertionResponse
{
    protected $info = array(
        'function'      => null,
        'status'        => null,
        'description'   => null
    );

    public function __construct()
    {
        if (func_num_args() != 3) {
            throw new \InvalidArgumentException('Missing required number of arguments for ' . get_called_class());
        }

        $this->info = array(
            'function'      => func_get_arg(0),
            'status'        => func_get_arg(1),
            'description'   => func_get_arg(2)
        );

        echo $this;
    }

    public function __toString()
    {
        return sprintf('   %s (%s) "%s" of type `assert->%s`',
            $this->info['status'] == 'pass' ? '*' : '!',
            strtoupper($this->info['status']),
            $this->info['description'], $this->info['function']
        ) . PHP_EOL;
    }
}

?>