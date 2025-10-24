<?php declare(strict_types=1);

/**
 * @author: Jiri Sosolik
 */

namespace UserPlay\Model;

use Nette\Database\Table\ActiveRow;

class UserModel
{
    public const TABLE_USER  = 'user';
    public const TABLE_AUDIT = 'user_audit_log';

    public function __construct(
        private \Nette\Database\Explorer $database,
    ) {
    }

    public function getUserByEmail( string $email ): ?\Nette\Database\Table\ActiveRow
    {
        return $this->database
            ->table(self::TABLE_USER)
            ->where('email = ?', $email)
            ->fetch();
    }

    /**
     * Creates new or updates user together with related audit log
     *
     * @param ActiveRow|null $user
     * @param mixed[] $valuesToStore
     * @return ActiveRow[]|null[]
     */
    public function createUpdate( ?ActiveRow $user, array $valuesToStore ): array {
        if ( $user !== null ) {
            $diff     = [];
            $changes  = [];
            $auditLog = null;
            foreach ( $user->toArray() as $key => $currentValue ) {
                if ( isset( $valuesToStore[$key])
                    && (string) $currentValue !== (string) $valuesToStore[$key]
                ) {
                    $diff[$key]['from'] = $currentValue;
                    $changes[$key]      = $diff[$key]['to'] = $valuesToStore[$key];
                }
            }

            // lets update just in case there are any changes
            if ( \count($changes) > 0 ) {
                $user->update($changes);
                $auditLog = $user->related(self::TABLE_AUDIT)->insert([
                    'change_type' => 'updated',
                    'changes' => \json_encode($diff),
                ]);
            }
        } else {
            $user = $this->database->table(self::TABLE_USER)->insert($valuesToStore);
            if ( ! $user instanceof ActiveRow ) {
                throw new \RuntimeException('User model not created');
            }

            $auditLog = $user->related(self::TABLE_AUDIT)->insert([
                'change_type' => 'created',
                'changes' => null, // dont store clone on creation
            ]);
        }

        if ( $auditLog !== null && ! $auditLog instanceof ActiveRow ) {
            throw new \RuntimeException('Failed to properly create user or its audit log instance');
        }
        return [$user, $auditLog];
    }
}