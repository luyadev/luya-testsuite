<?php
/**
 * @var \yii\db\TableSchema $schema
 * @var string $className
 * @var string $modelClass
 * @var array $data
 */
echo "<?php\n";
?>

namespace app\tests;

use luya\testsuite\fixtures\NgRestModelFixture;

class <?= $className; ?> extends NgRestModelFixture
{
    public $modelClass = '<?= $modelClass; ?>';

    public function getSchema()
    {
        return [
<?php foreach ($schema->columnNames as $column): ?>
            '<?= $column; ?>' => '<?= $schema->getColumn($column)->dbType; ?>',
<?php endforeach; ?>
        ];
    }

    public function getData()
    {
        return [
<?php foreach ($data as $index => $items): ?>
            <?= $index; ?> => [
<?php foreach ($items as $key => $item): ?>
                '<?= $key; ?>' => <?= is_numeric($item) ? $item : "'{$item}'" ?>,
<?php endforeach; ?>
            ],
<?php endforeach; ?>
        ];
    }
}