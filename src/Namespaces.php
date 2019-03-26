<?php
namespace TwigExtensionWidget;

final class Namespaces implements NamespacesInterface
{
    private $coll;

    public function __connstruct()
    {
        $this->coll = [];
    }

    public function push(string $path, string $as = null)
    {
        $bc = explode("\\", $path);
        if ($as === null) {
            $as = $bc[array_key_last($bc)];
        }
        $this->coll[$as] = $path;
    }

    public function resolved($class)
    {
        $resolved = false;
        if ($class[0] === "\\") {
            $resolved = $class;
        } elseif (strstr($class, "\\") === false) {
            $resolved = $this->hasAlias($class)? $this->coll[$class]: false;
        } else {
            $classPath = explode("\\", $class);
            $fsOfClassPath = array_shift($classPath);
            foreach ($this->coll as $alias => $path) {
                if ($alias === $fsOfClassPath) {
                    $resolved = $path . (!empty($classPath)? "\\" . implode("\\", $classPath): "");
                    break;
                }
            }
        }
        return $resolved;
    }

    public function hasAlias($name)
    {
        return isset($this->coll[$name]);
    }
}
