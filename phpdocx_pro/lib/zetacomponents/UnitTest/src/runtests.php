#!/usr/bin/env php
<?php
/**
 * File contaning the execution script for the Zeta Components test runner.
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @package UnitTest
 * @version //autogentag//
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */
require_once 'PHPUnit/Autoload.php';

$version = PHPUnit_Runner_Version::id();

if ( version_compare( $version, '3.5.0' ) == -1 && $version !== '@package_version@' )
{
    die( "PHPUnit 3.5.0 (or later) is required to run this test suite.\n" );
}

require_once 'PHP/CodeCoverage.php';
PHP_CodeCoverage::getInstance()->filter()->addFileToBlacklist( __FILE__, 'PHPUNIT' );

require_once 'bootstrap.php';

$runner = new ezcTestRunner;
$runner->run($_SERVER['argv']);
?>
