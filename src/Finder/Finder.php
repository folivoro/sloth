<?php

namespace Sloth\Finder;

use Illuminate\View\FileViewFinder as IlluminateFileViewFinder;

abstract class Finder extends IlluminateFileViewFinder
{
    /**
     * Allowed file extensions.
     *
     * @var array
     */
    protected $extensions = [];

    /**
     * List of found files.
     * $key is the file name or relative path.
     * $value is the file full path.
     *
     * @var array
     */
    protected $files = [];
    /**
     * List of given/registered paths.
     *
     * @var array
     */
    protected $paths = [];

    /**
     * Register multiple file paths.
     *
     * @param array $paths
     *
     * @return $this
     */
    public function addPaths(array $paths)
    {
        foreach ($paths as $index => $path) {
            $this->addPath($index, $path);
        }

        return $this;
    }

    /**
     * Returns the file path.
     *
     * @param string $name The file name or relative path.
     *
     * @throws FinderException
     *
     * @return string
     */
    public function find($name)
    {
        if (isset($this->files[ $name ])) {
            return $this->files[ $name ];
        }

        return $this->files[ $name ] = $this->findInPaths($name, $this->paths);
    }

    /**
     * Return a list of found files.
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Return a list of registered paths.
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Register a path.
     *
     * @param string $key  The file URL if defined or numeric index.
     * @param string $path
     *
     * @return $this
     */
    protected function addPath($key, $path)
    {
        if (! in_array($path, $this->paths)) {
            if (is_numeric($key)) {
                $this->paths[] = $path;
            } else {
                $this->paths[ $key ] = $path;
            }
        }

        return $this;
    }

    /**
     * Look after a file in registered paths.
     *
     * @param string $name  The file name or relative path.
     * @param array  $paths Registered paths.
     *
     * @throws FinderException
     *
     * @return array
     */
    protected function findInPaths($name, array $paths)
    {
        foreach ($paths as $path) {
            foreach ($this->getPossibleFiles($name) as $file) {
                if (file_exists($filePath = $path . $file)) {
                    return $filePath;
                }
            }
        }

        throw new FinderException('File or entity "' . $name . '" not found.');
    }

    /**
     * Returns a list of possible file names.
     *
     * @param string $name The file name or relative path.
     *
     * @return array
     */
    protected function getPossibleFiles($name)
    {
        return array_map(
            function ($extension) use ($name) {
                return str_replace('.', DS, $name) . '.' . $extension;
            },
            $this->extensions
        );
    }
}
