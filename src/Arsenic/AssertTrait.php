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


/**
 * AssertTrait holds all of our custom assertions
 *
 * @package Arsenic
 */
trait AssertTrait
{

    public static function true($input, $description = '', $type = self::ASSERT_NORMAL)
    {
        $result = static::equal($input, true, $description, $type, true);
        $status = static::_addAssertionResult(__FUNCTION__, array($input), $result, $description);

        return new AssertionResponse(__FUNCTION__, $status, $description);
    }

    public static function false($input, $description = '', $type = self::ASSERT_NORMAL)
    {
        $result = static::equal($input, false, $description, $type, true) ? true : false;
        $status = static::_addAssertionResult(__FUNCTION__, array($input), $result, $description);

        return new AssertionException(__FUNCTION__, $status, $description);
    }

    public static function equal($input, $expected, $description = '', $type = self::ASSERT_NORMAL, $viaAssert = false)
    {
        $result = ($type == static::ASSERT_STRICT) ?
            $input === $expected :
            $input == $expected;

        if ($viaAssert) {
            return $result;
        }

        $status = static::_addAssertionResult(__FUNCTION__, array($input, $expected), $result, $description);

        return new AssertionResponse(__FUNCTION__, $status, $description);
    }

    public static function notEqual($input, $expected, $description = '', $type = self::ASSERT_NORMAL)
    {
        $result = ($type == static::ASSERT_STRICT) ?
            $input !== $expected :
            $input != $expected;

        $status = static::_addAssertionResult(__FUNCTION__, array($input, $expected), $result, $description);

        return new AssertionResponse(__FUNCTION__, $status, $description);
    }

    public static function truthy($input, $description = '')
    {
        $result = (bool)$input;
        $status = static::_addAssertionResult(__FUNCTION__, array($input), $result, $description);

        return new AssertionResponse(__FUNCTION__, $status, $description);
    }

    // forced fail
    public static function fail($description = '')
    {
        $status = static::_addAssertionResult(__FUNCTION__, array(), false, $description);

        return new AssertionResponse(__FUNCTION__, $status, $description);
    }

    // forced pass
    public static function pass($description = '')
    {
        $status = static::_addAssertionResult(__FUNCTION__, array(), true, $description);

        return new AssertionResponse(__FUNCTION__, $status, $description);
    }

    public static function throws(callable $callback, $params, $exception = null, $description = '')
    {
        if (is_array($params)) {
            $exception = $exception ? : 'Exception';
        } else {
            $description = $exception;
            $exception   = $params;
            $params      = array();
        }
        try {
            call_user_func_array($callback, $params);
            $result = false;
        } catch (\Exception $e) {
            $result = $e instanceof $exception;
        }

        $status = static::_addAssertionResult(__FUNCTION__, array($callback, $exception), $result, $description);

        return new AssertionResponse(__FUNCTION__, $status, $description);
    }

}


?>