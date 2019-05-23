<?php

namespace Frosh\Performance\Subscriber;

use Fig\Link\Link;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\WebLink\HttpHeaderSerializer;

class PushSubscriber implements EventSubscriberInterface
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if ($event->getResponse()->headers->get('content-type') !== null) {
            return;
        }

        // We will push only once
        if ($event->getRequest()->headers->get('Cookie') !== null) {
            return;
        }

        if (!$this->systemConfigService->get('FroshPlatformPerformance.config.http2Push')) {
            return;
        }

        $serializer = new HttpHeaderSerializer();
        $basePath = $event->getRequest()->getBasePath();

        $linkJs = new Link('preload', $basePath . '/js/main.bundle.js');
        $linkJs = $linkJs->withAttribute('as', 'script');
        $linkCss = new Link('preload', $basePath . '/css/main.bundle.css', ['as' => 'style']);
        $linkCss = $linkCss->withAttribute('as', 'style');

        $event->getResponse()->headers->set('Link', $serializer->serialize([$linkCss, $linkJs]));
    }
}
