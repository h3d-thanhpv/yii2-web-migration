<?php

use yii\db\Migration;

/**
 * Handles the creation for table `table_test`.
 */
class m160529_025942_create_table_test extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('table_test', [
            'id' => $this->primaryKey(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('table_test');
    }
}
