<?php
declare(strict_types=1);

namespace Asdoria\SyliusLocaleSwitcherPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class AsdoriaSyliusLocaleSwitcherPlugin
 * @package Asdoria\SyliusLocaleSwitcherPlugin
 *
 * @author  Philippe Vesin <pve.asdoria@gmail.com>
 */
final class AsdoriaSyliusLocaleSwitcherPlugin extends Bundle
{
    use SyliusPluginTrait;

    /**
     * @return array
     */
    public function getSupportedDrivers(): array
    {
        return [
            SyliusResourceBundle::DRIVER_DOCTRINE_ORM,
        ];
    }
}
