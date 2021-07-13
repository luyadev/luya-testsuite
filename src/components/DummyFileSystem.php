<?php

namespace luya\testsuite\components;

use luya\admin\storage\BaseFileSystemStorage;

/**
 * Dummy File System
 * 
 * ```php
 * 'storage' => [
 *     'class' => 'luya\testsuite\components\DummyFileSystem'
 * ]
 * ```
 * 
 * @author Basil Suter <git@nadar.io>
 * @since 1.2.0
 */
class DummyFileSystem extends BaseFileSystemStorage
{
    /**
     * Return the http path for a given file on the file system.
     *
     * @param string $fileName The name of the file on the filesystem (like: my_example_1234.jpg without path infos), the $fileName is used as identifier on the filesystem.
     * @since 1.2.0
     */
    public function fileHttpPath($fileName)
    {
        return $fileName;
    }
    
    /**
     * Return the absolute http path for a given file on the file system.
     *
     * @param string $fileName The name of the file on the filesystem (like: my_example_1234.jpg without path infos), the $fileName is used as identifier on the filesystem.
     * @since 1.2.0
     */
    public function fileAbsoluteHttpPath($fileName)
    {
        return $fileName;
    }
    /**
     * Returns the path internal server path to the given file on the file system.
     *
     * @param string $fileName The name of the file on the filesystem (like: my_example_1234.jpg without path infos), the $fileName is used as identifier on the filesystem.
     */
    public function fileServerPath($fileName)
    {
        return $fileName;
    }
    
    /**
     * Check if the file exists on the given file system.
     *
     * @param string $fileName The name of the file on the filesystem (like: my_example_1234.jpg without path infos), the $fileName is used as identifier on the filesystem.
     * @since 1.2.0
     */
    public function fileSystemExists($fileName)
    {
        return true;
    }
    
    /**
     * Get the content of the file on the given file system.
     *
     * @param string $fileName The name of the file on the filesystem (like: my_example_1234.jpg without path infos), the $fileName is used as identifier on the filesystem.
     * @since 1.2.0
     */
    public function fileSystemContent($fileName)
    {
        return $fileName;
    }
    
    /**
     * {@inheritDoc}
     */
    public function fileSystemStream($fileName)
    {
        return $fileName;
    }
    
    /**
     * Save the given file source as a new file with the given fileName on the filesystem.
     *
     * @param string $source The absolute file source path and filename, like `/tmp/upload/myfile.jpg`.
     * @param string $fileName The name of the file on the filesystem (like: my_example_1234.jpg without path infos), the $fileName is used as identifier on the filesystem.
     * @return boolean Whether the file has been stored or not.
     */
    public function fileSystemSaveFile($source, $fileName)
    {
        return true;
    }
    
    /**
     * Replace an existing file source with a new one on the filesystem.
     *
     * @param string $fileName The name of the file on the filesystem (like: my_example_1234.jpg without path infos), the $fileName is used as identifier on the filesystem.
     * @param string $newSource The absolute file source path and filename, like `/tmp/upload/myfile.jpg`.
     * @return boolean Whether the file has replaced stored or not.
     */
    public function fileSystemReplaceFile($fileName, $newSource)
    {
        return true;
    }
    
    /**
     * Delete a given file source on the filesystem.
     *
     * @param string $fileName The name of the file on the filesystem (like: my_example_1234.jpg without path infos), the $fileName is used as identifier on the filesystem.
     * @return boolean Whether the file has been deleted or not.
     */
    public function fileSystemDeleteFile($fileName)
    {
        return true;
    }
}
