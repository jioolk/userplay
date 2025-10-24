<?php declare(strict_types=1);

final class CreateUserAuditLog extends Phinx\Migration\AbstractMigration
{

    public function up(): void
    {
        $table = $this->table('user_audit_log');
        $table->addColumn('user_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('changes', 'string', ['null' => true])
            ->addColumn('change_type', 'enum', ['values' => ['created','updated','deleted']])
            ->addColumn('changed_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('user_id', 'user', 'id', ['delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'])
            ->create();
    }

    public function down(): void
    {
        $this->table('user_audit_log')->drop()->save();
    }
}
