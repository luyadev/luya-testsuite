<?php

namespace luya\testsuite\fixtures;

/**
 * NgRestModelFixture.
 *
 * See {{ActiveRecordFixture}} for usage and documentation.
 *
 * @property array $schema
 * @property array $primaryKey
 * @property array $data
 * @property \luya\admin\ngrest\base\NgRestModel $newModel
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.10
 */
class NgRestModelFixture extends ActiveRecordFixture
{
    /**
     * Create instance of the model class.
     *
     * @return \luya\admin\ngrest\base\NgRestModel
     */
    public function getNewModel()
    {
        return parent::getNewModel();
    }
}
