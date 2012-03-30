<?php
/**
 * File contaning the ezcTestRunner class.
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
PHP_CodeCoverage::getInstance()->filter()->addFileToBlacklist( __FILE__, 'PHPUNIT' );

/**
 * Test runner to run the Zeta Components test suite(s).
 *
 * @package UnitTest
 * @version //autogentag//
 */
class ezcTestRunner extends PHPUnit_TextUI_Command
{
    const SUITE_FILENAME = 'tests/suite.php';

    protected $arguments = array(
      'backupStaticAttributes'      => false,
      'convertErrorsToExceptions'   => true,
      'convertNoticesToExceptions'  => false,
      'convertWarningsToExceptions' => false,
      'listGroups'                  => false,
      'loader'                      => null,
      'useDefaultConfiguration'     => true
    );

    protected $release = 'trunk';

    public function __construct()
    {
        $this->longOptions['dsn=']     = 'handleDsn';
        $this->longOptions['release='] = 'handleRelease';
    }

    protected function handleCustomTestSuite()
    {
        $directory = getcwd();
        $packages  = $this->options[1];

        if ( isset( $this->arguments['coverageClover']  ) ||
             isset( $this->arguments['reportDirectory'] ) )
        {
            $fillWhitelist = true;
        }
        else
        {
            $fillWhitelist = false;
        }

        $this->arguments['test'] = new PHPUnit_Framework_TestSuite;
        $this->arguments['test']->setName( 'Zeta Components' );

        if ( empty( $packages ) )
        {
            $packages = $this->getPackages( $directory );
        }

        foreach ( $packages as $package )
        {
            $added      = false;
            $slashCount = substr_count( $package, DIRECTORY_SEPARATOR );

            if ( ( $this->release == 'trunk'  && $slashCount !== 0 ) ||
                 ( $this->release == 'stable' && $slashCount > 1 ) )
            {
                if ( file_exists( $package ) )
                {
                    PHPUnit_Util_Class::collectStart();
                    require_once( $package );
                    $class = PHPUnit_Util_Class::collectEnd();

                    if ( !empty( $class ) )
                    {
                        $this->arguments['test']->addTest( call_user_func( array( array_pop( $class ), 'suite' ) ) );
                        $added   = true;
                        $package = substr($package, 0, strpos($package, DIRECTORY_SEPARATOR));
                    }
                    else
                    {
                        die( "\n Cannot load: $package. \n" );
                    }
                }
            }
            else 
            {
                $suite = $this->getTestSuite( $directory, $package );

                if ( !is_null( $suite ) )
                {
                    $this->arguments['test']->addTest( $suite );
                    $added = true;
                }
            }

            if ( $fillWhitelist && $added )
            {
                foreach ( glob( $directory . '/' . $package . '/src/*_autoload.php' ) as $autoloadFile )
                {
                    foreach ( include $autoloadFile as $className => $fileName )
                    {
                        if ( strpos($fileName, 'xmlwritersubstitute.php') === false )
                        {
                            PHPUnit_Util_Filter::addFileToWhitelist(
                              $directory . '/' . str_replace( $package, $package . '/src', $fileName )
                            );
                        }
                    }
                }
            }
        }

        if ( isset( $this->arguments['colors'] ) && $this->arguments['colors'] === true )
        {
            $colors = true;
        }
        else
        {
            $colors = false;
        }

        if ( isset( $this->arguments['debug'] ) && $this->arguments['debug'] === true )
        {
            $debug = true;
        }
        else
        {
            $debug = false;
        }

        if ( isset( $this->arguments['verbose'] ) && $this->arguments['verbose'] === true )
        {
            $verbose = true;
        }
        else
        {
            $verbose = false;
        }

        $this->arguments['printer'] = new ezcTestPrinter( NULL, $verbose, $colors, $debug );
    }

    protected function getPackages( $directory )
    {
        $packages = array();

        if ( is_dir( $directory ) )
        {
            $entries = glob( $this->release == 'trunk' ? "$directory/*" : "$directory/*/*" );

            foreach ( $entries as $entry )
            {
                if ( $this->isPackageDir( $entry ) )
                {
                    $packages[] = str_replace( $directory . '/', '', $entry );
                }
            }
        }

        return $packages;
    }

    protected function isPackageDir( $directory )
    {
        if ( !is_dir( $directory ) || !file_exists( $directory . '/tests/suite.php' ) )
        {
            return false;
        }

        return true;
    }

    protected function getTestSuite( $directory, $package )
    {
        $suitePath = implode( '/', array( $directory, '..', $this->release, $package, self::SUITE_FILENAME ) );
        if ( file_exists( $suitePath ) )
        {
            require_once( $suitePath );

            if ( $this->release == 'stable' )
            {
                $package = substr( $package, 0, strpos( $package, '/' ) );
            }

            $className = 'ezc'. $package . 'Suite';
            $suite     = call_user_func( array( $className, 'suite' ) );

            return $suite;
        }

        return null;
    }

    protected function handleDsn( $value )
    {
        try
        {
            $ts       = ezcTestSettings::getInstance();
            $settings = ezcDbFactory::parseDSN( $value );

            $ts->db->value = $value;
        
            try
            {
                $ts->setDatabaseSettings( $settings );
                $db = ezcDbFactory::create( $settings );
                ezcDbInstance::set( $db );
            }
            catch ( ezcDbException $e )
            {
                die( $e->getMessage() );
            }
        }
        catch ( Exception $e )
        {
            die( "Database initialization error: {$e->getMessage()}\n" );
        }
    }

    protected function handleRelease( $value )
    {
        $this->release = $value;
    }
}
?>
