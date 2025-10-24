<?php declare(strict_types=1);

/**
 * @author: Jiri Sosolik
 */

namespace UserPlay\Core;

use Nette\DI\Container;
use Nette\SmartObject;

/**
 * Lighter version of container just for params
 */
class Configuration
{
    use SmartObject;

    /** @var mixed[] */
    protected $configuration;

    protected Container $container;

    public function __construct( Container $container )
    {
        $this->container = $container;
    }

    /**
     * Returns value of config parameter, throws exception if not found
     *
     * @param string $name
     * @return mixed
     */
    public function get( string $name )
    {
        return $this->optional($name) ?? throw new \Exception("Parameter $name not defined but required! Check your configuration.");
    }

    /**
     * Returns value of config, fallbacks to default if not set
     *
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public function optional( string $name, mixed $default = null )
    {
        $this->lazyLoad();
        return $this->configuration[$name] ?? $default;
    }

    /**
     * @return mixed[] string indexed flat array
     */
    public function getAllAsMap(): array {
        $this->lazyLoad();
        return $this->configuration;
    }

    /**
     * Do not load/flatten all the params if noone asks for them anyway
     *
     * @return void
     */
    private function lazyLoad(): void {
        // be lazy
        if ( $this->configuration === null ) {
            $rawParams           = $this->container->getParameters();
            $this->configuration = $this->flattenParams($rawParams);
        }
    }

    /**
     * Flattens all the params for nice single string path
     *
     * @param mixed[]|mixed $params
     * @param string|null $name
     * @param mixed[] $result
     * @return string[][]
     */
    private function flattenParams( mixed $params, ?string $name = null, array &$result = [] ): array
    {
        if ( \is_array($params) ) {
            foreach ( $params as $key => $param ) {
                $paramName = $name !== null ? "$name.$key" : $key;
                $this->flattenParams($param, $paramName, $result);
            }
        } else {
            $result[$name] = $params;
        }

        return $result;
    }
}