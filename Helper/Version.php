<?php

namespace Easproject\Eucompliance\Helper;

use Composer\InstalledVersions;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Serialize\SerializerInterface;

class Version
{
    /** @var DirectoryList  */
    private DirectoryList $dir;

    /** @var SerializerInterface  */
    private SerializerInterface $serializer;

    /**
     * @param DirectoryList $dir
     * @param SerializerInterface $serializer
     */
    public function __construct(
        DirectoryList           $dir,
        SerializerInterface     $serializer
    ) {
        $this->dir = $dir;
        $this->serializer = $serializer;
    }

    /** Force implement getter for version */
    /**
     * @return array[]
     * @psalm-return list<array{root: array{name: string, version: string, reference: string, pretty_version: string, aliases: string[], dev: bool, install_path: string, type: string}, versions: array<string, array{dev_requirement: bool, pretty_version?: string, version?: string, aliases?: string[], reference?: string, replaced?: string[], provided?: string[], install_path?: string, type?: string}>}>|array
     */
    private function getInstalled()
    {
        $installed = array();
        $vendorDir = $this->dir->getRoot() . '/vendor';
        if (is_file($vendorDir.'/composer/installed.php')) {
            /** @var array{root: array{name: string, pretty_version: string, version: string, reference: string|null, type: string, install_path: string, aliases: string[], dev: bool}, versions: array<string, array{pretty_version?: string, version?: string, reference?: string|null, type?: string, install_path?: string, aliases?: string[], dev_requirement: bool, replaced?: string[], provided?: string[]}>}|array $required */
            $required = require $vendorDir.'/composer/installed.php';
            $installed[] = $required;
        } elseif (is_file($vendorDir.'/composer/installed.json')) {
            $installed = $this->serializer->unserialize(file_get_contents($vendorDir.'/composer/installed.json'));
        }
        return $installed;
    }

    /**
     * @param  string      $packageName
     * @return string|null If the package is being replaced or provided but is not really installed, null will be returned as version, use satisfies or getVersionRanges if you need to know if a given version is present
     */
    public function getVersion($packageName)
    {
        $vendorDir = $this->dir->getRoot() . '/vendor';
        if (is_file($vendorDir.'/composer/installed.php')) {
            foreach ($this->getInstalled() as $installed) {
                if (!isset($installed['versions'][$packageName])) {
                    continue;
                }

                if (!isset($installed['versions'][$packageName]['version'])) {
                    return null;
                }

                return $installed['versions'][$packageName]['version'];
            }
        } elseif (is_file($vendorDir.'/composer/installed.json')) {
            foreach ($this->getInstalled() as $package) {
                if (
                    !isset($package['version']) ||
                    !isset($package['name']) ||
                    $package['name'] !== $packageName
                ) {
                    continue;
                }
                return str_replace('v', '', $package['version']);
            }
        }

        throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
    }

    /**
     * Check if Firebase JWT package has < 6.0 version
     *
     * @return bool|int
     */
    public function isJwtOld()
    {
        try{
            $lesser = version_compare(
                InstalledVersions::getVersion('firebase/php-jwt'),
                '6.0',
                '<'
            );
        } catch (\OutOfBoundsException $e) {
            $lesser = version_compare(
                $this->getVersion('firebase/php-jwt'),
                '6.0',
                '<'
            );
        }
        return $lesser;
    }
}
