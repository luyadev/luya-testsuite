<?php

namespace luya\testsuite\cases;

use yii\base\InvalidConfigException;

/**
 * Cms module block TestCase.
 *
 * Testing a CMS Block for the CMS Layout. Example usage:
 *
 * ```php
 * class TextBlockTest extends WebApplicationTestCase
 * {
 *     public $blockClass = 'luya\generic\blocks\TextBlock';
 *
 *     public function getConfigArray()
 *     {
 *         return [
 *             'id' => 'testTextBlock',
 *             'basePath' => dirname(__DIR__),
 *         ];
 *     }
 *
 *     public function testAdminAndFrontendRender()
 *     {
 *         $this->assertSame('<p>Removes spaces and br from frontend view.</p>', $this->renderFrontendNoSpace());
 *         $this->assertSame('<p>Admin View</p>', $this->renderAdminNoSpace());
 *     }
 *
 *     public function testFrontendWithVars()
 *     {
 *         $this->block->setVarValues([
 *              'text' => 'Hello World',
 *         ]);
 *
 *         $this->assertSame('<p>Hello World', $this->renderFrontendNoSpace());
 *     }
 * }
 * ```
 *
 * On order to test an image object from storage you could use
 *
 * ```php
 * $this->block->setVarValues(['image' => (object) ['source' => 'image.jpg', 'caption' => 'image caption']]);
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.6
 */
abstract class CmsBlockTestCase extends WebApplicationTestCase
{
    /**
     * @var string The path to the block to be tested.
     */
    public $blockClass;
    
    /**
     * @var \luya\cms\base\PhpBlock
     */
    public $block;
    
    /**
     * @inheritdoc
     */
    public function afterSetup()
    {
        parent::afterSetup();
        
        if (!$this->blockClass) {
            throw new InvalidConfigException("The 'blockClass' property can not be empty.");
        }
        
        $class = $this->blockClass;
        $this->block = new $class();
    }
    
    /**
     * Renders the frontend view of a block.
     *
     * @return string
     */
    public function renderFrontend()
    {
        $icon = $this->block->icon();
        if (empty($icon)) {
            $icon = 'fa';
        }
        $this->assertNotEmpty($this->block->blockGroup());
        $this->assertNotEmpty($this->block->name());
        $this->assertTrue(is_string($icon));
        $this->assertTrue(is_array($this->block->config()));
        $this->assertTrue(is_array($this->block->extraVars()));
        $this->assertFalse(is_array($this->block->renderAdmin()));
        $this->assertNotNull($this->block->getFieldHelp());
        return $this->block->renderFrontend();
    }
    
    /**
     * Renders the admin view of a blockl.
     *
     * @return string
     */
    public function renderAdmin()
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem());
        $temp = $twig->createTemplate($this->block->renderAdmin());
        return $temp->render([
            'cfgs' => $this->block->getCfgValues(),
            'vars' => $this->block->getVarValues(),
            'extras' => $this->block->getExtraVarValues(),
        ]);
    }
    
    /**
     * Render the admin view, but auto remove all spaces.
     *
     * @return mixed
     */
    public function renderAdminNoSpace()
    {
        return $this->trimContent($this->renderAdmin());
    }
    
    /**
     * Render the frontend view, but auto remove all spaces.
     *
     * @return string
     */
    public function renderFrontendNoSpace()
    {
        return $this->trimContent($this->renderFrontend());
    }
}
