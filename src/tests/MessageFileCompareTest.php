<?php

namespace luya\testsuite\tests;

use luya\testsuite\cases\BaseTestSuite;

/**
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.1
 */
abstract class MessageFileCompareTest extends BaseTestSuite
{
    /**
     * @return string `en` Which is the default language folder in `getMessagesFolder()`
     */
    abstract public function getMasterLanguage();
    
    /**
     * @return string `@admin/messages`
     */
    abstract public function getMessagesFolder();
    
    public function testMessageFilesCompare()
    {
        $this->compare(Yii::getAlias($this->getMessagesFolder()), $this->getMasterLanguage());
    }
    
    public function compare($folder, $masterLang)
    {
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
    }
}