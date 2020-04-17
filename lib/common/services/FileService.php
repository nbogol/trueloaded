<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);


namespace common\services;


class FileService
{

    /**
     * @param array $paths
     * @param \Closure $callback
     * @param int $depth
     * @param string $maskRegExp
     * @return \Generator
     */
    public function getClassesIterator(array $paths, \Closure $callback, int $depth = -1, string $maskRegExp = '/.*\.php$/')
    {
        foreach ($paths as $path) {
            foreach ($this->getFilesIterator($path, $maskRegExp, $depth) as $file) {
                $className = $this->getClassFromPath($file->getPathName());
                $result = $callback($className);
                if ($result) {
                    yield $result;
                }
            }
        }
    }

    /**
     * @param string $path
     * @param string $maskRegExp
     * @param int $depth
     * @return \RegexIterator
     */
    public function getFilesIterator(string $path, string $maskRegExp = '/.*/', int $depth = -1)
    {
        $dir = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $dir->setMaxDepth($depth);
        return new \RegexIterator($dir, $maskRegExp);
    }

    /**
     * @param string $path
     * @return string
     */
    public function getClassFromPath(string $path): string
    {
        return str_replace([dirname(__FILE__, 3) . '/', '/', '.php'], ['', '\\', ''], $path);
    }
}
