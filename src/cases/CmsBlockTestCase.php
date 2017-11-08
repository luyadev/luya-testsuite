<?php

namespace luya\testsuite\cases;

use yii\base\InvalidConfigException;

abstract class CmsBlockTestCase extends WebApplicationTestCase
{
    public $blockClass;
    
    /**
     * @var \luya\cms\base\PhpBlock
     */
    public $block;
    
    public function afterSetup()
    {
        parent::afterSetup();
        
        if (!$this->blockClass) {
            throw new InvalidConfigException("The 'blockClass' property can not be empty.");
        }
        
        $class = $this->blockClass;
        $this->block = new $class();
    }
    
    public function renderFrontend()
    {
        $this->assertNotEmpty($this->block->blockGroup());
        $this->assertNotEmpty($this->block->name());
        $this->assertNotEmpty($this->block->icon());
        $this->assertTrue(is_array($this->block->config()));
        $this->assertTrue(is_array($this->block->extraVars()));
        $this->assertFalse(is_array($this->block->renderAdmin()));
        $this->assertNotNull($this->block->getFieldHelp());
        return $this->block->renderFrontend();
    }
    
    public function renderAdmin()
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem());
        $temp = $twig->createTemplate($this->block->renderAdmin());
        return $temp->render(['cfgs' => $this->block->getCfgValues(), 'vars' => $this->block->getVarValues()]);
    }
    
    public function renderAdminNoSpace()
    {
        $text = trim(preg_replace('/\s+/', ' ', $this->renderAdmin()));
        
        return str_replace(['> ', ' <'], ['>', '<'], $text);
    }
    
    public function renderFrontendNoSpace()
    {
        $text = trim(preg_replace('/\s+/', ' ', $this->renderFrontend()));
        
        return str_replace(['> ', ' <'], ['>', '<'], $text);
    }
}
