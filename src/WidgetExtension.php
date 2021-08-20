<?php
namespace TwigExtensionWidget;

use Twig;
use Core\Memoize;
use Core\Stash;
use Core\Media\Chunk\ContextInterface;
use Core\Media\Chunk\WidgetInterface;

final class WidgetExtension extends Twig\Extension\AbstractExtension
{
    private $ctx;

    private $memoize;

    private $stash;

    private $ns;

    public function __construct(
        ContextInterface $ctx,
        Memoize\MemoizeInterface $memoize = null,
        Stash\StashInterface $stash = null,
        NamespacesInterface $ns = null
    ) {
        $this->ctx = $ctx;
        $this->memoize = $memoize === null? new Memoize\MemoryMemoize(): $memoize;
        $this->stash = $stash === null? new Stash\MemoryStash(): $stash;
        $this->ns = $ns === null? new Namespaces(): $ns;
    }

    /**
     * @return Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new Twig\TwigFunction(
                'widget',
                function (Twig\Environment $env, $context, string $class, array $option = null) {
                    $class = $this->ns->resolved($class);
                    $stash = $this->memoize->getOrSet(hash('md5', "stash@" . $class), function () use ($class) {
                        return $this->stash->create();
                    });
                    echo (function (WidgetInterface $widget) {
                        return $widget->rendered();
                    }) (new $class($option ?? [], $this->ctx, $stash));
                },
                [
                    'needs_context' => true,
                    'needs_environment' => true
                ]
            ),
            new Twig\TwigFunction(
                'use',
                function (Twig\Environment $env, $context, $namespaces) {
                    if (!is_array($namespaces)) {
                        $namespaces = [$namespaces];
                    }
                    foreach ($namespaces as $path) {
                        if (preg_match("/^([\S]+)\s+as\s+([\S+]+)$/i", trim($path), $m)) {
                            $path = $m[1];
                            $alias = $m[2];
                        } else {
                            $alias = null;
                        }
                        $this->ns->push($path, $alias);
                    }
                },
                [
                    'needs_context' => true,
                    'needs_environment' => true
                ]
            )
        ];
    }
}
