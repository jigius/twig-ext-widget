<?php
namespace TwigExtensionWidget;

interface NamespacesInterface
{
    public function push(string $path, string $as = null);

    public function resolved($class);

    public function hasAlias($name);
}
