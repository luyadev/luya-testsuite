<?php

namespace luya\testsuite\traits;

use Yii;
use yii\helpers\FileHelper;

/**
 * Migration File Compare.
 *
 * Checks migrations files for missing keys based on a default language.
 *
 * Example
 *
 * ```php
 * use MessageFileCompareTrait;
 *
 * public function compare()
 * {
 *     $this->compareMessages(Yii::getAlias('@admin/messages'), 'en');
 * }
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.1
 */
trait MessageFileCompareTrait
{
    /**
     * Compare migration files inside the language folder against the main language defined with $masterLang.
     *
     * The compareMessages() methods assumes that you are providing a folder which has languages, where the languages contain the translations
     * files itself. The $masterLang is the main message file to compare to others with.
     *
     * @param string $folder `en`
     * @param string $masterLang `/admin/src/message` Path to the message file folders.
     */
    public function compareMessages($folder, $masterLang)
    {
        $folder = Yii::getAlias($folder);
        $folders = [];
        
        foreach (scandir($folder) as $item) {
            if (is_dir($folder . DIRECTORY_SEPARATOR . $item) && $item !== '..' && $item !== '.') {
                $folders[$item] = $folder . DIRECTORY_SEPARATOR . $item;
            }
        }
        
        $master = $folders[$masterLang];
        unset($folders[$masterLang]);
        $masterFiles = FileHelper::findFiles($master);
        
        foreach ($folders as $dir) {
            foreach (FileHelper::findFiles($dir) as $file) {
                foreach ($masterFiles as $mf) {
                    if (basename($file) == basename($mf)) {
                        $materArray = include($mf);
                    }
                }
                
                $compareArray = include($file);
                
                foreach ($materArray as $key => $value) {
                    $this->assertArrayHasKey($key, $compareArray, "The language key '{$key}' does not exists in the language file '{$file}'.");
                }
            }
        }
        
        return true;
    }
}
