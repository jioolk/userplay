<?php declare(strict_types=1);

/**
 * @author: Jiri Sosolik
 */

namespace UserPlay\Console;

use Nette\Utils\Validators;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Simple base FORCING users to fill in validation
 */
abstract class BaseCommand extends Command
{
    protected InputInterface $input;
    protected OutputInterface $output;

    /**
     * Final method enforces disallowed override, validation of input needs to be implemented in any case.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    final protected function execute( InputInterface $input, OutputInterface $output ): int
    {
        $this->input  = $input;
        $this->output = $output;

        $vRules = $this->validateInput();
        if ( \count($vRules) > 0 ) {
            foreach ( $vRules as $ruleResult ) {
                $this->output->writeln("<error>{$ruleResult}</error>");
            }

            return 16;
        }

        return $this->performCommand();
    }

    /**
     * Typical validation shorthand
     *
     * Example: array['limit'] => 'int:1..10'
     *
     * @param mixed[] $validationMap key: name of option to validate, value: validation rules
     * @return string[]
     */
    protected function autoValidateInput( array $validationMap ): array {
        $result = [];
        foreach ( $validationMap as $name => $validation ) {
            if ( Validators::is($this->input->getOption($name), $validation) ) {
                continue;
            }

            $result[] = "Option $name does not conform to rule $validation";
        }

        return $result;
    }

    /**
     * Needs to validate all the input values and return empty array on success.
     *
     * Values in returned array are detected failures
     *
     * @return string[]
     */
    protected abstract function validateInput(): array;

    /**
     * Main execution method, equal to execute()
     *
     * @return int
     */
    protected abstract function performCommand(): int;
}