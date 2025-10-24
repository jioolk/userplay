<?php declare(strict_types=1);

final class CreateUser extends Phinx\Migration\AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('user');
        $table
            ->addColumn('email', 'string', ['null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('date_of_birth', 'date', ['null' => false])
            ->addIndex(['email'], ['unique' => true])
            ->create();
    }

    public function down(): void
    {
        $this->table('user')->drop()->save();
    }
}
