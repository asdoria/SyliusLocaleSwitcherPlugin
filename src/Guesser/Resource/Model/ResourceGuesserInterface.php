<?php

declare(strict_types=1);

namespace Asdoria\SyliusLocaleSwitcherPlugin\Guesser\Resource\Model;

use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Component\Resource\Model\ResourceInterface;


/**
 * Interface ResourceGuesserInterface
 * @package Asdoria\SyliusLocaleSwitcherPlugin\Guesser\Resource\Model
 *
 * @author  Philippe Vesin <pve.asdoria@gmail.com>
 */
interface ResourceGuesserInterface
{
    /**
     * @param RequestConfiguration $configuration
     *
     * @return ResourceInterface|null
     */
    public function getResource(RequestConfiguration $configuration): ?ResourceInterface;

    /**
     * @param RequestConfiguration $configuration
     *
     * @return bool
     *
     */
    public function isSupport(RequestConfiguration $configuration): bool;
}
