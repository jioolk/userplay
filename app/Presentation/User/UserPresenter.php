<?php declare(strict_types=1);

namespace UserPlay\Presentation\User;

use Nette\DI\Container;
use Nette\Utils\DateTime;
use Nette\Utils\Json;
use Nette\Utils\Validators;
use UserPlay\Core\Configuration;
use UserPlay\Model\UserModel;
use UserPlay\Presentation\BasePresenter;

/**
 * @author: Jiri Sosolik
 */
class UserPresenter extends BasePresenter
{
    private UserModel $model;

    public function __construct( UserModel $model, Configuration $config, Container $container )
    {
        parent::__construct($config, $container);
        $this->model = $model;
    }

    public function actionProcess(): void
    {
        // Set JSON response header
        $this->getHttpResponse()->setContentType('application/json');

        // Get JSON input
        $input = $this->getHttpRequest()->getRawBody();
        if ( $input === null ) {
            $this->sendFail('No body payload detected.');
            return; // unnecesary returns - phpstan likes it more this way
        }

        try {
            $data = Json::decode($input, true);
        } catch ( \Nette\Utils\JsonException $e ) {
            $this->sendFail('Invalid JSON data received.');
            return;
        }

        // Validate required fields
        $requiredFields = ['name', 'email', 'dateOfBirth'];
        foreach ( $requiredFields as $field ) {
            if ( empty($data[$field]) ) {
                $this->sendFail("Field '{$field}' is required.");
                return;
            }
        }

        // Validate email format
        if ( ! Validators::isEmail($data['email']) ) { // filter_var($data['email'], FILTER_VALIDATE_EMAIL)
            $this->sendFail('Please provide a valid email address.');
            return;
        }

        // Validate date of birth
        $dateOfBirth = DateTime::createFromFormat('Y-m-d', $data['dateOfBirth']);
        if ( ! $dateOfBirth ) {
            $this->sendFail('Please provide a valid date of birth.');
            return;
        }

        $dateOfBirth->setTime(0,0,0);
        if ( $dateOfBirth->format('Y-m-d') !== $data['dateOfBirth'] ) {
            $this->sendFail('Please provide a valid date of birth.');
            return;
        }

        // Check if user is at least 20 years old, today restricts it to date only
        $now = new DateTime();
        $age = $dateOfBirth->diff($now)->y;

        $userMinAge = $this->config->get('user.min-age');
        $userMaxAge = $this->config->get('user.max-age');

        if ( $dateOfBirth > $now ) {
            $this->sendFail('You are required to be born already.');
        } elseif ( $age < $userMinAge ) {
            $this->sendFail('You must be at least 20 years old to register.');
        } elseif ( $age >= $userMaxAge ) {
            $this->sendFail("Congratulations! Being $age years old is wonderful! As a jubilee older than $userMaxAge years, please contact us directly.");
        }

        // todo: encapsule it into object for smooth traversing around
        $auditDetails = $this->storeIntoDB([
            'email' => $data['email'],
            'name' => $data['name'],
            'date_of_birth' => $dateOfBirth,
        ]);

        $this->sendJson([
            'successful' => true,
            'reply' => "Hello {$data['name']}! Congratulations for being $age years old.",
            'details' => $auditDetails,
        ]);
    }

    /**
     * Stores user detail into database
     *
     * Returns array of additional DB related information provided back via API
     *
     * @param string[]|DateTime[] $data
     * @return string[]|bool[]
     */
    private function storeIntoDB( array $data ): array
    {
        $result = [];

        // not necessary piece, used as we want API response to be more comlex for future logic in UI
        $user                    = $this->model->getUserByEmail((string) $data['email']);
        $result['existing-user'] = $user !== null;

        [$user, $audit] = $this->model->createUpdate($user, $data);

        if ( $audit !== null && $audit->changes !== null ) {
            $result['audit'] = \json_decode($audit->changes);
        }

        return $result;
    }

    /**
     * Shorthand sending failure message
     *
     * @param string $message
     * @return void
     *
     * @throws \Nette\Application\AbortException
     */
    private function sendFail( string $message ): void
    {
        $this->sendJson([
            'successful' => false,
            'reply' => $message,
        ]);
    }
}