<?php
declare(strict_types=1);

namespace Asdoria\SyliusLocaleSwitcherPlugin\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * Class AsdoriaSyliusLocaleSwitcherExtension
 * @package Asdoria\SyliusLocaleSwitcherPlugin\DependencyInjection
 *
 * @author  Philippe Vesin <pve.asdoria@gmail.com>
 */
class AsdoriaSyliusLocaleSwitcherExtension extends Extension implements PrependExtensionInterface, ExtensionInterface
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

        $configuration = new Configuration($container);
        $config        = $this->processConfiguration($configuration, $configs);

        $loader  = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $aliases = $config['aliases'] ?? [];

        $container->setParameter('asdoria_sylius_locale_switcher_plugin.aliases', $aliases);

        $loader->load('services.yaml');
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('twig', [
            'globals' => [
                'locale_switcher_aliases' => '%asdoria_sylius_locale_switcher_plugin.aliases%',
            ],
        ]);
    }
}
