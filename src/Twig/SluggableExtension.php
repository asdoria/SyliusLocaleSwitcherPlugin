<?php

declare(strict_types=1);

namespace Asdoria\SyliusLocaleSwitcherPlugin\Twig;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Sylius\Component\Resource\Model\SlugAwareInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslationInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class SluggableExtension
 * @package Asdoria\SyliusLocaleSwitcherPlugin\Twig
 *
 * @author  Philippe Vesin <pve.asdoria@gmail.com>
 */
class SluggableExtension extends AbstractExtension
{
    /**
     * SluggableExtension constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(protected EntityManager $entityManager) {
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('getSlugByLocale', [$this, 'getSlugByLocale'])
        ];
    }

    /**
     * @param SlugAwareInterface $slugAware
     * @param string             $localCode
     *
     * @return string|null
     */
    public function getSlugByLocale(SlugAwareInterface $slugAware, string $localCode) : ?string {
        if (!$slugAware instanceof TranslatableInterface) {
            return $slugAware->getSlug();
        }

        $translation =  $slugAware->getTranslation();
        if (!$translation instanceof TranslationInterface) {
            return  $slugAware->getSlug();
        }

        $repo = $this->entityManager->getRepository(get_class($translation));

        if (!$repo instanceof EntityRepository) {
            return  $slugAware->getSlug();
        }

        $resource = $repo
            ->findOneBy(['translatable' => $slugAware, 'locale' => $localCode]);

        return $resource instanceof TranslationInterface ? $resource->getSlug() :  $slugAware->getSlug();
    }
}
