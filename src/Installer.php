<?php
/*
  +------------------------------------------------------------------------+
  | PhalconEye CMS                                                         |
  +------------------------------------------------------------------------+
  | Copyright (c) 2013-2014 PhalconEye Team (http://phalconeye.com/)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconeye.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Author: Ivan Vorontsov <ivan.vorontsov@phalconeye.com>                 |
  +------------------------------------------------------------------------+
*/

namespace PhalconEye;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

/**
 * Composer installer.
 *
 * @category  PhalconEye
 * @package   Engine\Composer
 * @author    Ivan Vorontsov <ivan.vorontsov@phalconeye.com>
 * @copyright 2013-2014 PhalconEye Team
 * @license   New BSD License
 * @link      http://phalconeye.com/
 */
class Installer extends LibraryInstaller
{
    const
        /**
         * Module package.
         */
        PACKAGE_TYPE_MODULE = 'module',

        /**
         * Plugin package.
         */
        PACKAGE_TYPE_PLUGIN = 'plugin',

        /**
         * Theme package.
         */
        PACKAGE_TYPE_THEME = 'theme',

        /**
         * Widget package.
         */
        PACKAGE_TYPE_WIDGET = 'widget',

        /**
         * Library package.
         */
        PACKAGE_TYPE_LIBRARY = 'library';

    /**
     * Get package locations array.
     *
     * @return array
     */
    public function getPackageLocations()
    {
        return [
            self::PACKAGE_TYPE_MODULE => 'app/modules/',
            self::PACKAGE_TYPE_PLUGIN => 'app/plugins/',
            self::PACKAGE_TYPE_WIDGET => 'app/widgets/',
            self::PACKAGE_TYPE_LIBRARY => 'app/libraries/',
            self::PACKAGE_TYPE_THEME => 'public/themes/'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        $type = $package->getType();
        $locations = $this->getPackageLocations();
        $extra = $package->getExtra();

        // Correct package type.
        if (empty($locations[$type])) {
            $type = self::PACKAGE_TYPE_LIBRARY;
        }

        if ($type == self::PACKAGE_TYPE_LIBRARY) {
            $name = $package->getPrettyName();
            if (strpos($name, '/') !== false) {
                $name = end(explode('/', $name));
            }
        } else {
            if (empty($extra['name'])) {
                throw new \InvalidArgumentException('Package extra data is missing. Extra property "name" is required.');
            }
            $name = ucfirst($extra['name']);
        }

        return $locations[$type] . '/' . $name;
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        if (!$repo->hasPackage($package)) {
            throw new \InvalidArgumentException('Package is not installed: ' . $package);
        }

        $repo->removePackage($package);
        $installPath = $this->getInstallPath($package);
        $this->io->write(sprintf('Deleting %s - %s', $installPath, $this->filesystem->removeDirectory($installPath) ? '<comment>deleted</comment>' : '<error>not deleted</error>'));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return true;
    }
}