<?php
/**
 * @var \yii\db\TableSchema $schema
 * @var string $className
 * @var string $modelClass
 * @var array $data
 * @var string $tableName
 */
echo "<?php\n";
?>

namespace app\tests\fixtures;

use luya\testsuite\fixtures\NgRestModelFixture;

/**
 * <?= $className; ?> Fixture
 */
class <?= $className; ?> extends NgRestModelFixture
{
<?php if ($modelClass): ?>
    /**
     * {@inheritDoc}
     */
    public $modelClass = '<?= $modelClass; ?>';
<?php elseif ($tableName): ?>
    /**
     * {@inheritDoc}
     */
    public function getTableName()
    {
        return '<?= $tableName; ?>';
    }
<?php endif; ?>

    /**
     * {@inheritDoc}
     */
    public function getSchema()
    {
        return [
<?php foreach ($schema->columnNames as $column): ?>
            '<?= $column; ?>' => '<?= $schema->getColumn($column)->dbType; ?>',
<?php endforeach; ?>
        ];
    }
<?php if ($addData): ?>

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return [
<?php foreach ($data as $index => $items): ?>
            <?= is_integer($index) ? $index : "'{$index}'"; ?> => [
<?php foreach ($items as $key => $item): ?>
                '<?= $key; ?>' => <?= is_integer($item) ? $item : "'".str_replace("'", "\\'", $item)."'" ?>,
<?php endforeach; ?>
            ],
<?php endforeach; ?>
        ];
    }
<?php endif; ?>
}