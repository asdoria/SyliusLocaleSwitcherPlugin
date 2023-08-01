<?php

declare(strict_types=1);

namespace Asdoria\SyliusLocaleSwitcherPlugin\Guesser;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Asdoria\SyliusLocaleSwitcherPlugin\Guesser\Model\ResourceGuesserInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

/**
 * Class RepositoryMethodResourceGuesser
 * @package Asdoria\SyliusLocaleSwitcherPlugin\Guesser
 *
 * @author  Philippe Vesin <pve.asdoria@gmail.com>
 */
class RepositoryMethodResourceGuesser implements ResourceGuesserInterface
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    ) {}


    /**
     * @param RequestConfiguration $configuration
     *
     * @return ResourceInterface|null
     */
    public function getResource(RequestConfiguration $configuration): ?ResourceInterface {
        /** @var RepositoryInterface $repository */
        $class      = $configuration->getMetadata()->getClass('model');
        $repository = $this->entityManager->getRepository($class);
        try {
            $method   = $configuration->getRepositoryMethod();
            $args     = $configuration->getRepositoryArguments();
            return $repository->$method(...$args);
        } catch (\Throwable $exception) {
            return null;
        }
    }
    /**
     * @param RequestConfiguration $configuration
     *
     * @return bool
     *
     */
    public function isSupport(RequestConfiguration $configuration): bool {

        /** @var RepositoryInterface $repository */
        $class      = $configuration->getMetadata()->getClass('model');
        $repository = $this->entityManager->getRepository($class);
        return $repository instanceof RepositoryInterface;
    }
}
