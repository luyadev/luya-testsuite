<?php

namespace luya\testsuite\tests\commands;

use luya\testsuite\Bootstrap;
use luya\testsuite\cases\ConsoleApplicationTestCase;
use luya\testsuite\commands\GenerateFixtureController;
use luya\testsuite\fixtures\ActiveRecordFixture;
use Yii;
use yii\db\ActiveRecord;

class TestModel extends ActiveRecord
{
    public $foo;
    public $bar;

    public static function tableName()
    {
        return 'testtable';
    }
}

class GenerateFixtureControllerTest extends ConsoleApplicationTestCase
{
    public function getConfigArray()
    {
        return [
            'id' => 'command',
            'basePath' => dirname(__DIR__),
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ]
            ]
        ];
    }

    public function testTableGenerate()
    {
        new ActiveRecordFixture([
            'modelClass' => TestModel::class,
        ]);
        
        new ActiveRecordFixture([
            'tableName' => 'hb_test_schema',
            'schema' => [
                'id' => 'pk',
                'name' => 'text',
                'is_active' => 'int(11)',
            ],
            'fixtureData' => [
                1 => [
                    'id' => 1,
                    'name' => 'John Doe\'s',
                    'is_active' => true,
                ],
                2 => [
                    'id' => 2,
                    'name' => 'Jane Doe',
                    'is_active' => 0,
                ],
            ]
        ]);

        $command = new GenerateFixtureController('id', Yii::$app);
        $command->table = 'hb_test_schema';
        $command->mode = GenerateFixtureController::MODE_TABLE;
        $command->data = true;
        $command->actionIndex();

        $command = new GenerateFixtureController('id', Yii::$app);
        $command->mode = GenerateFixtureController::MODE_MODEL;
        $command->model = TestModel::class;
        $command->data = true;
        $command->actionIndex();

        $content = <<<'EOL'
<?php

namespace app\tests\fixtures;

use luya\testsuite\fixtures\NgRestModelFixture;

/**
 * testClass Fixture
 */
class testClass extends NgRestModelFixture
{
    /**
     * {@inheritDoc}
     */
    public $modelClass = 'test';

    /**
     * {@inheritDoc}
     */
    public function getSchema()
    {
        return [
            'id' => 'integer',
            'name' => 'text',
            'is_active' => 'int(11)',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return [
            1 => [
                'id' => '1',
                'name' => 'John Doe\'s',
                'is_active' => '1',
            ],
            2 => [
                'id' => '2',
                'name' => 'Jane Doe',
                'is_active' => '0',
            ],
        ];
    }
}
EOL;

        $schema = $command->getSchema('hb_test_schema');
        $this->assertSame($content, $command->generateClassFile(
            $schema,
            'testClass',
            $command->generateData($schema, 'hb_test_schema', $command->db),
            'test',
            null,
            true
        ));

        $content = <<<'EOL'
<?php

namespace app\tests\fixtures;

use luya\testsuite\fixtures\NgRestModelFixture;

/**
 * testClass Fixture
 */
class testClass extends NgRestModelFixture
{
    /**
     * {@inheritDoc}
     */
    public function getTableName()
    {
        return 'tableName';
    }

    /**
     * {@inheritDoc}
     */
    public function getSchema()
    {
        return [
            'id' => 'integer',
            'name' => 'text',
            'is_active' => 'int(11)',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return [
            1 => [
                'id' => '1',
                'name' => 'John Doe\'s',
                'is_active' => '1',
            ],
            2 => [
                'id' => '2',
                'name' => 'Jane Doe',
                'is_active' => '0',
            ],
        ];
    }
}
EOL;

        $schema = $command->getSchema('hb_test_schema');
        $this->assertSame($content, $command->generateClassFile(
            $schema,
            'testClass',
            $command->generateData($schema, 'hb_test_schema', $command->db),
            null,
            'tableName',
            true
        ));

        $content = <<<'EOL'
<?php

namespace app\tests\fixtures;

use luya\testsuite\fixtures\NgRestModelFixture;

/**
 * testClass Fixture
 */
class testClass extends NgRestModelFixture
{
    /**
     * {@inheritDoc}
     */
    public function getTableName()
    {
        return 'tableName';
    }

    /**
     * {@inheritDoc}
     */
    public function getSchema()
    {
        return [
            'id' => 'integer',
            'name' => 'text',
            'is_active' => 'int(11)',
        ];
    }
}
EOL;

        $schema = $command->getSchema('hb_test_schema');
        $this->assertSame($content, $command->generateClassFile(
            $schema,
            'testClass',
            $command->generateData($schema, 'hb_test_schema', $command->db),
            null,
            'tableName',
            false
        ));
    }

    public function testBootstrap()
    {
        $bootstrap = new Bootstrap();
        $this->assertNull($bootstrap->bootstrap($this->app));
    }
}