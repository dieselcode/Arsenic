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

include '../vendor/autoload.php';

use Arsenic\Test as UT;

UT::suite('This is our test suite', function() {

    UT::setup(function() {
        UT::fixture('foo', ['foo' => 'bar']);
    });

    UT::test('Sample Test Name', function() {
        // value, expected [, use strict]
        UT::equal(1, 0x01, 'Asserting if 1 === 0x01', UT::ASSERT_STRICT);
    });

    UT::test('True test', function() {
        UT::true(false, 'Is true actually true?');
    });

    UT::test('Testing fixture "foo"', function() {
        $foo = UT::fixture('foo')['foo'];
        UT::equal('bar', $foo, 'Is foo == bar?');
    });

    UT::tearDown(function() {
        // UnitTest::clearFixtures();
    });

});

UT::suite('Testing second suite', function() {

    UT::setup(function() {
        UT::fixture('foo', ['foo' => 'bar']);
    });

    UT::test('Test Foo Setting', function() {
        $foo = UT::fixture('foo')['foo'];
        UT::equal('bar', $foo, 'Does bar == bar?');

        UT::fixture('foo', ['foo' => 'nope']);
        $foo = UT::fixture('foo')['foo'];
        UT::equal('bar', $foo, 'Does bar == bar?');

    });

});

UT::run();
