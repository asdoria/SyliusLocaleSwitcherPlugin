<?php
declare(strict_types=1);

namespace Asdoria\SyliusLocaleSwitcherPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * Class AsdoriaSyliusLocaleSwitcherExtension
 * @package Asdoria\SyliusLocaleSwitcherPlugin\DependencyInjection
 *
 * @author  Philippe Vesin <pve.asdoria@gmail.com>
 */
class AsdoriaSyliusLocaleSwitcherExtension extends Extension
{

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     *
     * @return void
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));


        $loader->load('services.yaml');

    }
}
