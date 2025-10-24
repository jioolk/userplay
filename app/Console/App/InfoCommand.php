<?php declare(strict_types=1);

/**
 * @author: Jiri Sosolik
 */

namespace UserPlay\Console\App;

use Nette\Utils\Strings;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;
use UserPlay\Console\BaseCommand;
use UserPlay\Core\Configuration;

#[AsCommand(name: 'app:info')]
class InfoCommand extends BaseCommand
{
    private Configuration $config;

    private const KEY_LIMIT   = 'key-column-limit';
    private const VALUE_LIMIT = 'value-column-limit';
    private const SORT        = 'sort';

    public function __construct( Configuration $config )
    {
        parent::__construct();
        $this
            ->setDescription('Debugging tool to print out content of configuration as understood by app/Nette')
            ->addOption(self::KEY_LIMIT, null, InputOption::VALUE_REQUIRED, 'Character limit in key column', 80)
            ->addOption(self::VALUE_LIMIT, null, InputOption::VALUE_REQUIRED, 'Character limit in value column', 80)
            ->addOption(self::SORT, null, InputOption::VALUE_REQUIRED, 'Shall we sort result naturally?', 1);

        $this->config = $config;
    }

    /**
     * @see parent::validateInput()
     */
    protected function validateInput(): array
    {
        return $this->autoValidateInput([
            self::KEY_LIMIT => 'numericint:32..256',
            self::SORT => 'numericint:0..1',
        ]);
    }

    /**
     * @see parent::performCommand()
     */
    protected function performCommand(): int
    {
        $params = $this->config->getAllAsMap();

        $keyLimit   = (int) $this->input->getOption(self::KEY_LIMIT);
        $valueLimit = (int) $this->input->getOption(self::VALUE_LIMIT);
        $flatParams = $this->flattenParams($params, $keyLimit, $valueLimit);

        if ( (int) $this->input->getOption(self::SORT) === 1 ) {
            // natural sort by value of first column, which is the name
            \array_multisort(\array_column($flatParams, 'name'), SORT_NATURAL, $flatParams);
        }

        $table = new Table($this->output);
        $table
            ->setHeaders(['Name', 'Value'])
            ->setRows($flatParams);

        $table->render();
        return 0;
    }

    /**
     * Recursively walks thru array of anytype params and makes single two-dimensional array with nice values for table
     *
     * @param mixed[]|mixed $params
     * @param int $keyLimit
     * @param int $valueLimit
     * @param string|null $name
     * @param string[][] $result
     * @return string[][]
     */
    private function flattenParams( mixed $params, int $keyLimit, int $valueLimit, ?string $name = null, array &$result = [] ): array
    {
        if ( \is_array($params) ) {
            foreach ( $params as $key => $param ) {
                $paramName = $name !== null ? "$name.$key" : $key;
                $this->flattenParams($param, $keyLimit, $valueLimit, $paramName, $result);
            }
        } else {
            $result[] = [
                'name' => Strings::substring((string) $name, $keyLimit * -1), // needs to be negative number, we want ltrim to better see long keys
                'value' => Strings::truncate((string) $params, $valueLimit, ' <comment>...</comment>'),
            ];
        }

        return $result;
    }
}