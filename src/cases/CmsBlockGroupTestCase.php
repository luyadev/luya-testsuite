<?php
namespace luya\testsuite\cases;

use luya\cms\base\BlockGroup;
use yii\base\InvalidConfigException;

/**
 * Class CmsBlockGroupTestCase
 *
 * Testing a cms block group for the admin.
 *
 * Example usage:
 * ```php
 * namespace cmstests\src\frontend\blockgroups;
 *
 * use luya\testsuite\cases\CmsBlockGroupTestCase
 *
 * class ProjectGroupTest extends CmsBlockGroupTestCase
 * {
 *      public $blockGroupClass = 'luya\cms\frontend\blockgroup\ProjectGroup';
 *
 *      public $blockGroupIdentifier = 'project-group';
 * }
 * ```
 *
 * @package luya\testsuite\cases
 * @author Alexander Schmid <alex.schmid@stud.unibas.ch>
 * @since 1.0.11
 */
abstract class CmsBlockGroupTestCase extends WebApplicationTestCase
{
    /**
     * @var string The path to the block group tested.
     */
    public $blockGroupClass;

    /**
     * @var \luya\cms\base\BlockGroup;
     */
    public $blockGroup;

    /**
     * @var string The used identifier
     */
    public $blockGroupIdentifier;

    /**
     * Provide configurtion array.
     *
     * This method can be overwritten if adjustments are needed.
     * In most cases this will not be needed, therefore a default config is provided
     */
    public function getConfigArray()
    {
        return [
            'id' => 'blockGroupTest',
            'basePath' => dirname(__DIR__),
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSetup()
    {
        parent::afterSetup();

        if (!$this->blockGroupClass) {
            throw new InvalidConfigException("The 'blockGroupClass' property can not be empty.");
        }
        if (!$this->blockGroupIdentifier) {
            throw new InvalidConfigException("The 'blockGroupIdentifier' property can not be empty.");
        }

        $class = $this->blockGroupClass;
        $this->blockGroup = new $class();
    }

    /**
     * Test if this block group is an instance of \luya\cms\base\BlockGroup
     */
    public function testIsBlockGroup()
    {
        $this->assertTrue($this->blockGroup instanceof BlockGroup);
        return true;
    }

    /**
     * Tests if the identifier is correct
     */
    public function testIdentifier()
    {
        $this->assertNotEmpty($this->blockGroup->identifier());
        $this->assertSame($this->blockGroupIdentifier, $this->blockGroup->identifier());

        return true;
    }

    /**
     * Test if there is a label
     */
    public function testLabel()
    {
        $this->assertNotEmpty($this->blockGroup->label());

        return true;
    }

    /**
     * Tests if the position is ok
     */
    public function testGetPostition()
    {
        $this->assertTrue(0 <= $this->blockGroup->getPosition());
        $this->assertTrue(100 >= $this->blockGroup->getPosition());

        return true;
    }
}
