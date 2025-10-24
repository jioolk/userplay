<?php declare(strict_types=1);

/**
 * @author: Jiri Sosolik
 */

namespace UserPlay\Presentation;

use Nette\DI\Container;
use UserPlay\Core\Configuration;

class BasePresenter extends \Nette\Application\UI\Presenter
{
    public function __construct(
        protected readonly Configuration $config,
        protected readonly Container $container
    )
    {
        parent::__construct();
    }
}