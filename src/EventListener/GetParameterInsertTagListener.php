<?php

declare(strict_types=1);

namespace Guycollegmbh\ApartmentsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsHook('replaceInsertTags')]
class GetParameterInsertTagListener
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function __invoke(string $tag): string|false
    {
        [$name, $param] = explode('::', $tag, 2) + [1 => ''];

        if ('whg_get' !== $name) {
            return false;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return '';
        }

        return StringUtil::specialchars($request->query->get($param, ''));
    }
}
