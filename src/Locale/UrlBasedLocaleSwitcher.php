<?php
declare(strict_types=1);

namespace Asdoria\SyliusLocaleSwitcherPlugin\Locale;

use Sylius\Bundle\ShopBundle\Locale\LocaleSwitcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class UrlBasedLocaleSwitcher
 * @package Asdoria\SyliusLocaleSwitcherPlugin\Locale
 *
 * @author  Philippe Vesin <pve.asdoria@gmail.com>
 */
class UrlBasedLocaleSwitcher implements LocaleSwitcherInterface
{
    /** @var UrlGeneratorInterface */
    private UrlGeneratorInterface $urlGenerator;

    /** @var LocaleSwitcherInterface $inner */
    protected LocaleSwitcherInterface $inner;

    /**
     * @param LocaleSwitcherInterface $inner
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(LocaleSwitcherInterface $inner, UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->inner = $inner;
    }

    /**
     * @param Request $request
     * @param string  $localeCode
     *
     * @return RedirectResponse
     */
    public function handle(Request $request, string $localeCode): RedirectResponse
    {
        $redirect = $request->query->get('_redirect', null);
        if(empty($redirect)) {
            return $this->inner->handle($request,$localeCode);
        }

        return new RedirectResponse($redirect);
    }
}
