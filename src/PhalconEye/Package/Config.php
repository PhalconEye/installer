<?php
/*
  +------------------------------------------------------------------------+
  | PhalconEye CMS                                                         |
  +------------------------------------------------------------------------+
  | Copyright (c) 2013-2016 PhalconEye Team (http://phalconeye.com/)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconeye.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Author: Ivan Vorontsov <lantian.ivan@gmail.com>                        |
  +------------------------------------------------------------------------+
*/

namespace PhalconEye\Package;

/**
 * Packages config.
 *
 * @category  PhalconEye
 * @package   Engine\Composer
 * @author    Ivan Vorontsov <ivan.vorontsov@phalconeye.com>
 * @copyright 2013-2016 PhalconEye Team
 * @license   New BSD License
 * @link      http://phalconeye.com/
 */
class Config
{
    const
        /**
         * Packages config location.
         */
        PACKAGE_CONFIG_PATH = 'config/%/packages.php',

        /**
         * Default phalcon eye environment.
         */
        DEFAULT_ENVIRONMENT = 'development';

    private $_configPath;
    private $_config;

    /**
     * Config constructor.
     *
     * @param string $basePath Base path.
     */
    public function __construct($basePath)
    {
        $this->_configPath = $basePath . sprintf(self::PACKAGE_CONFIG_PATH, $this->_getEnvironment());
        $this->_config = include $this->_configPath;
    }

    /**
     * Add to packages config.
     *
     * @param string $name Package name.
     * @param string $type Package type.
     */
    public function add($name, $type)
    {
        $this->_config[$type][] = $name;
    }

    /**
     * Remove from packages config.
     *
     * @param string $name Package name.
     * @param string $type Package type.
     */
    public function remove($name, $type)
    {
        if (!isset($this->_config[$type])) {
            return;
        }

        if (($key = array_search($name, $this->_config[$type])) !== false) {
            unset($this->_config[$type][$key]);
        }
    }

    /**
     * Save config.
     */
    public function save()
    {
        file_put_contents($this->_configPath, $this->_toString());
    }

    /**
     * Get current PhalconEye environment.
     *
     * @return string Environment name.
     */
    private function _getEnvironment()
    {
        if (defined('APPLICATION_STAGE')) {
            return APPLICATION_STAGE;
        }

        return self::DEFAULT_ENVIRONMENT;
    }

    /**
     * Convert config array to string.
     *
     * @return string Config content
     */
    private function _toString()
    {
        $configText = var_export($this->_config, true);
        $headerText = '<?php' . PHP_EOL;
        return $headerText . $configText . ';';
    }
}