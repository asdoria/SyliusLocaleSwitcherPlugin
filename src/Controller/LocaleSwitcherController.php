<?php

declare(strict_types=1);

namespace Asdoria\SyliusLocaleSwitcherPlugin\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\SingleResourceProviderInterface;
use Sylius\Bundle\ShopBundle\Controller\LocaleSwitchController as BaseLocaleSwitchController;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Grid\Provider\GridProviderInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Locale\Provider\LocaleProviderInterface;
use Sylius\Component\Resource\Metadata\RegistryInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;
use Twig\Error;

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
        protected SingleResourceProviderInterface $singleResourceProvider,
        protected EntityManagerInterface $entityManager,
        protected ChannelContextInterface $channelContext,
        protected GridProviderInterface $gridProvider
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
    public function renderAction(?string $route = null , array $routeParams = [], Request $request): Response
    {
        $mainRequest = $this->requestStack->getMainRequest();
        $alias       = $request->query->get('_alias', null);
        $resource    = $this->getResource($alias, $mainRequest);
        if(empty($route)) {
            $route       = $mainRequest->attributes->get('_route', 'sylius_shop_homepage');
        }

        if(empty($routeParams)) {
            $routeParams = $mainRequest->attributes->get('_route_params', []);
        }

        return new Response($this->templatingEngine->render('@SyliusShop/Header/_headerLocales.html.twig', [
            'active'       => $this->localeContext->getLocaleCode(),
            'locales'      => $this->localeProvider->getAvailableLocalesCodes(),
            'resource'     => $resource instanceof ResourceInterface ? $resource : null,
            'alias'        => $alias,
            '_route'       => $route,
            '_routeParams' => $routeParams
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
            $resource = null;
            if (empty($alias)) {
                return null;
            }

            $metadata      = $this->metadataRegistry->get($alias);
            $configuration = $this->requestConfigurationFactory->create($metadata, $mainRequest);
            /** @var RepositoryInterface $repository */
            $class      = $metadata->getClass('model');
            $repository = $this->entityManager->getRepository($class);
            if(!$repository instanceof RepositoryInterface) return null;
            //if ($configuration->getParameters()->has('repository') && !$mainRequest->attributes->has('slug')) {
            //    return $this->singleResourceProvider->get($configuration, $repository);
            //}

            $grid = $configuration->getGrid();

            $method = 'findOneBySlug';
            if (method_exists($repository, $method)) {
                $reflection = new \ReflectionMethod($repository, $method);
                $parameters = $reflection->getParameters();
                $args = [$mainRequest->attributes->get('slug'), $this->localeContext->getLocaleCode()];
                if (in_array('channel', array_column($parameters, 'name'))) {
                    $args[] = $this->channelContext->getChannel();
                }
                $resource = $repository->$method(...$args);
            }

            if (empty($resource)) {
                $method   = $configuration->getRepositoryMethod();
                $args     = $configuration->getRepositoryArguments();
                $resource = $repository->$method(...$args);
            }

            if (empty($resource)) {
                $resource = $this->findCustomPageResourceCms($repository, $configuration, $alias, $mainRequest);
            }

            return  is_iterable($resource) ? current($resource) : $resource;

        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @param RepositoryInterface  $repository
     * @param RequestConfiguration $configuration
     * @param string               $alias
     * @param Request|null         $mainRequest
     *
     * @return ResourceInterface|null
     */
    protected function findCustomPageResourceCms(
        RepositoryInterface  $repository,
        RequestConfiguration $configuration,
        string               $alias,
        ?Request             $mainRequest): ?ResourceInterface
    {
        if (!$alias == 'asdoria_cms_plugin.page') return null;

        $args    = $configuration->getRepositoryArguments();
        $method  = 'findOneEnabledBySlug';
        $slugs   = explode('/', $mainRequest->attributes->get('slug'));
        $args[0] = array_pop($slugs);

        return $repository->$method(...$args);
    }
}
