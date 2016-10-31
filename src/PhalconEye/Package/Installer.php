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

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

/**
 * Composer installer.
 *
 * @category  PhalconEye
 * @package   Engine\Composer
 * @author    Ivan Vorontsov <ivan.vorontsov@phalconeye.com>
 * @copyright 2013-2016 PhalconEye Team
 * @license   New BSD License
 * @link      http://phalconeye.com/
 */
class Installer extends LibraryInstaller
{
    const
        /**
         * Module package.
         */
        PACKAGE_TYPE_MODULE = 'phalconeye-module',

        /**
         * Plugin package.
         */
        PACKAGE_TYPE_PLUGIN = 'phalconeye-plugin',

        /**
         * Theme package.
         */
        PACKAGE_TYPE_THEME = 'phalconeye-theme',

        /**
         * Widget package.
         */
        PACKAGE_TYPE_WIDGET = 'phalconeye-widget';

    /**
     *  Package locations.
     */
    private $packages = [
        self::PACKAGE_TYPE_MODULE => 'app/modules/',
        self::PACKAGE_TYPE_PLUGIN => 'app/plugins/',
        self::PACKAGE_TYPE_WIDGET => 'app/widgets/',
        self::PACKAGE_TYPE_THEME => 'public/themes/'
    ];

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        $extra = $package->getExtra();
        if (empty($extra['name'])) {
            throw new \InvalidArgumentException('Package extra data is missing. Extra property "name" is required.');
        }

        $type = $package->getType();
        $name = $extra['name'];
        if ($type != self::PACKAGE_TYPE_THEME) {
            $name = ucfirst($name);
        }

        return $this->packages[$type] . '/' . $name;
    }

    /**
     * {@inheritDoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);

        $extra = $package->getExtra();
        if (empty($extra['name'])) {
            throw new \InvalidArgumentException('Package extra data is missing. Extra property "name" is required.');
        }

        $config = new Config();
        $config->add($extra['name'], $package->getType());
        $config->save();
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

        $extra = $package->getExtra();

        if (!empty($extra['name'])) {
            $config = new Config();
            $config->remove($extra['name'], $package->getType());
            $config->save();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return isset($this->packages[$packageType]);
    }
}