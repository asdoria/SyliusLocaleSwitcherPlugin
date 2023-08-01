<?php

declare(strict_types=1);

namespace Asdoria\SyliusLocaleSwitcherPlugin\Controller;

use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Bundle\ShopBundle\Controller\LocaleSwitchController as BaseLocaleSwitchController;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Locale\Provider\LocaleProviderInterface;
use Sylius\Component\Resource\Metadata\RegistryInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;
use Twig\Error;
use Asdoria\SyliusLocaleSwitcherPlugin\Registry\ResourceGuesserServiceRegistryInterface;

/**
 * Class LocaleSwitchController
 * @package Asdoria\SyliusLocaleSwitcherPlugin\Controller
 *
 * @author  Philippe Vesin <pve.asdoria@gmail.com>
 */
class LocaleSwitcherController
{
    public function __construct(
        protected EngineInterface|Environment $templatingEngine,
        protected LocaleContextInterface $localeContext,
        protected LocaleProviderInterface $localeProvider,
        protected BaseLocaleSwitchController $baseLocaleSwitchController,
        protected RegistryInterface $metadataRegistry,
        protected RequestStack $requestStack,
        protected RequestConfigurationFactoryInterface $requestConfigurationFactory,
        protected ResourceGuesserServiceRegistryInterface $resourceGuesserRegistry,
    ) {
    }

    /**
     * @param string|null $route
     * @param array       $routeParams
     * @param Request     $request
     *
     * @return Response
     * @throws Error\LoaderError
     * @throws Error\RuntimeError
     * @throws Error\SyntaxError
     */
    public function __invoke(?string $route = null , array $routeParams = [], Request $request): Response
    {
        $mainRequest = $this->requestStack->getMainRequest();
        $alias       = $request->query->get('_alias', null);
        $resource    = $this->getResource($alias, $mainRequest);

        return new Response($this->templatingEngine->render('@SyliusShop/Header/_headerLocales.html.twig', [
            'active'       => $this->localeContext->getLocaleCode(),
            'locales'      => $this->localeProvider->getAvailableLocalesCodes(),
            'resource'     => $resource instanceof ResourceInterface ? $resource : null,
            'alias'        => $alias,
            '_route'       => $route ?: $mainRequest->attributes->get('_route', 'sylius_shop_homepage'),
            '_routeParams' => $routeParams ?: $mainRequest->attributes->get('_route_params', [])
        ]));
    }

    /**
     * @param Request     $request
     * @param string|null $code
     *
     * @return Response
     */
    public function switchAction(Request $request, ?string $code = null): Response
    {
        return $this->baseLocaleSwitchController->switchAction($request, $code);
    }

    /**
     * @param              $alias
     * @param Request|null $mainRequest
     *
     * @return ResourceInterface|null
     */
    protected function getResource($alias, ?Request $mainRequest): ?ResourceInterface
    {
        try {
            if (empty($alias)) return null;

            $metadata      = $this->metadataRegistry->get($alias);
            $configuration = $this->requestConfigurationFactory->create($metadata, $mainRequest);

            return $this->resourceGuesserRegistry->guess($configuration);

        } catch (\Throwable $e) {
            return null;
        }
    }
}
