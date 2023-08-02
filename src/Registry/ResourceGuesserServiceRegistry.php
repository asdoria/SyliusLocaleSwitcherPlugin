<?php

declare(strict_types=1);


namespace Asdoria\SyliusLocaleSwitcherPlugin\Registry;

use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Component\Resource\Model\ResourceInterface;
use Asdoria\SyliusLocaleSwitcherPlugin\Guesser\Resource\Model\ResourceGuesserInterface;
/**
 * Class ResourceGuesserServiceRegistry
 * @package Asdoria\SyliusLocaleSwitcherPlugin\Registry
 *
 * @author  Philippe Vesin <pve.asdoria@gmail.com>
 */
class ResourceGuesserServiceRegistry implements ResourceGuesserServiceRegistryInterface
{
    /**
     * @var iterable
     */
    protected iterable $handlers;

    /**
     * SerializerServiceRegistry constructor.
     *
     * @param iterable $handlers
     */
    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return iterator_to_array($this->handlers);
    }

    /**
     * @param RequestConfiguration $configuration
     *
     * @return ResourceInterface|null
     */
    public function guess(RequestConfiguration $configuration): ?ResourceInterface
    {
        foreach ($this->all() as $item) {
            if (!$item instanceof ResourceGuesserInterface) continue;
            if (!$item->isSupport($configuration)) continue;
            $resource = $item->getResource($configuration);
            if ($resource instanceof ResourceInterface) return $resource;
        }
    }
}
