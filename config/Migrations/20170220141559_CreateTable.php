<?php
use Migrations\AbstractMigration;

class CreateTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('attachments', ['id' => false, 'primary_key' => ['id']]);
        $table
        ->addColumn('id', 'integer', [
            'autoIncrement' => true,
        ])
        ->addColumn('file_name', 'string')
        ->addColumn('file_path', 'string',[
            'default' => null,
            'null' => true,
        ])
        ->addColumn('file_type', 'string')
        ->addColumn('file_size', 'biginteger')
        ->addColumn('file_content', 'binary')
        ->addColumn('model', 'string')
        ->addColumn('tag', 'string')
        ->addColumn('foreign_key', 'integer')
        ->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ])
        ->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => true,
        ])
        ->create();
    }
}
