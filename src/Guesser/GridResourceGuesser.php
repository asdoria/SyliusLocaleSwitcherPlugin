<?php

declare(strict_types=1);

namespace Asdoria\SyliusLocaleSwitcherPlugin\Guesser;

use Asdoria\SyliusLocaleSwitcherPlugin\Guesser\Model\ResourceGuesserInterface;
use Sylius\Bundle\ResourceBundle\Controller\ParametersParserInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Provider\GridProviderInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * Class GridResourceGuesser
 *
 * @author Philippe Vesin <pve.asdoria@gmail.com>
 */
class GridResourceGuesser implements ResourceGuesserInterface
{
    public function __construct(
        protected GridProviderInterface $gridProvider,
        protected ParametersParserInterface $parametersParser
    ) {
    }

    /**
     * @param RequestConfiguration $configuration
     *
     * @return ResourceInterface|null
     */
    public function getResource(RequestConfiguration $configuration): ?ResourceInterface {
        $gridDefinition = $this->gridProvider->get($configuration->getGrid());

        if (!$gridDefinition instanceof Grid) return null;

        $class  = $configuration->getMetadata()->getClass('model');
        $args   = $gridDefinition->getDriverConfiguration()['repository']['arguments'] ?? [];
        $values = $this->parametersParser->parseRequestValues($args, $configuration->getRequest());
        foreach ($values as $value) {
            if ($value instanceof $class && $value instanceof ResourceInterface) return $value;
        }

        return null;
    }

    /**
     * @param RequestConfiguration $configuration
     *
     * @return bool
     *
     */
    public function isSupport(RequestConfiguration $configuration): bool {
        return $configuration->hasGrid();
    }
}
