<?php

declare(strict_types=1);

namespace Asdoria\SyliusLocaleSwitcherPlugin\Guesser\Resource;


use Asdoria\SyliusLocaleSwitcherPlugin\Guesser\Resource\Model\ResourceGuesserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

/**
 * Class SlugResourceGuesser
 * @package Asdoria\SyliusLocaleSwitcherPlugin\Guesser\Resource
 *
 * @author  Philippe Vesin <pve.asdoria@gmail.com>
 */
class SlugResourceGuesser implements ResourceGuesserInterface
{
    protected string $method = 'findOneBySlug';

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected LocaleContextInterface $localeContext,
        protected ChannelContextInterface $channelContext
    ) {}


    /**
     * @param RequestConfiguration $configuration
     *
     * @return ResourceInterface|null
     * @throws \ReflectionException
     */
    public function getResource(RequestConfiguration $configuration): ?ResourceInterface {
        /** @var RepositoryInterface $repository */
        $class      = $configuration->getMetadata()->getClass('model');
        $repository = $this->entityManager->getRepository($class);
        $reflection = new \ReflectionMethod($repository, $this->method);
        $parameters = $reflection->getParameters();
        $args = [$configuration->getRequest()->attributes->get('slug'), $this->localeContext->getLocaleCode()];
        if (in_array('channel', array_column($parameters, 'name'))) {
            $args[] = $this->channelContext->getChannel();
        }

        $method = $this->method;

        return $repository->$method(...$args);

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
        return $repository instanceof RepositoryInterface && method_exists($repository, $this->method);
    }
}
