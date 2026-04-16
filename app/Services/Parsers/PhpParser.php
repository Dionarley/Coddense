<?php

namespace App\Services\Parsers;

use PhpParser\Error;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;

class PhpParser implements LanguageParserInterface
{
    public function getSupportedExtensions(): array
    {
        return ['php'];
    }

    public function getLanguageName(): string
    {
        return 'PHP';
    }

    public function parseFile(string $filePath): array
    {
        $code = file_get_contents($filePath);
        $parser = (new ParserFactory)->createForNewestSupportedVersion();

        try {
            $ast = $parser->parse($code);
            $nodeFinder = new NodeFinder;

            $entities = [];
            $namespace = $this->getNamespace($ast);

            $classes = $nodeFinder->findInstanceOf($ast, Class_::class);
            foreach ($classes as $class) {
                $entities[] = $this->extractClassData($class, $namespace, $filePath);
            }

            $traits = $nodeFinder->findInstanceOf($ast, Trait_::class);
            foreach ($traits as $trait) {
                $entities[] = $this->extractTraitData($trait, $namespace, $filePath);
            }

            $interfaces = $nodeFinder->findInstanceOf($ast, Interface_::class);
            foreach ($interfaces as $interface) {
                $entities[] = $this->extractInterfaceData($interface, $namespace, $filePath);
            }

            $functions = $nodeFinder->findInstanceOf($ast, Function_::class);
            foreach ($functions as $function) {
                $entities[] = $this->extractFunctionData($function, $filePath);
            }

            return $entities;
        } catch (Error $error) {
            return [];
        }
    }

    private function getNamespace($ast): ?string
    {
        $nodeFinder = new NodeFinder;
        $ns = $nodeFinder->findFirstInstanceOf($ast, Namespace_::class);

        return $ns ? (string) $ns->name : null;
    }

    private function extractClassData(Class_ $class, ?string $namespace, string $filePath): array
    {
        return [
            'type' => 'class',
            'name' => (string) $class->name,
            'namespace' => $namespace,
            'file_path' => $filePath,
            'details' => [
                'extends' => $class->extends ? $this->nodeToString($class->extends) : null,
                'implements' => $class->implements ? array_map(fn ($i) => $this->nodeToString($i), $class->implements) : [],
                'methods' => $this->extractMethods($class),
                'properties' => $this->extractProperties($class),
            ],
        ];
    }

    private function extractTraitData(Trait_ $trait, ?string $namespace, string $filePath): array
    {
        return [
            'type' => 'trait',
            'name' => (string) $trait->name,
            'namespace' => $namespace,
            'file_path' => $filePath,
            'details' => [
                'methods' => $this->extractMethods($trait),
                'properties' => $this->extractProperties($trait),
            ],
        ];
    }

    private function extractInterfaceData(Interface_ $interface, ?string $namespace, string $filePath): array
    {
        return [
            'type' => 'interface',
            'name' => (string) $interface->name,
            'namespace' => $namespace,
            'file_path' => $filePath,
            'details' => [
                'extends' => $interface->extends ? array_map(fn ($e) => $this->nodeToString($e), $interface->extends) : [],
                'methods' => $this->extractMethods($interface),
            ],
        ];
    }

    private function extractFunctionData(Function_ $function, string $filePath): array
    {
        return [
            'type' => 'function',
            'name' => (string) $function->name,
            'namespace' => null,
            'file_path' => $filePath,
            'details' => [
                'params' => $this->extractParams($function),
            ],
        ];
    }

    private function nodeToString($node): string
    {
        if ($node === null) {
            return '';
        }

        if (is_string($node)) {
            return $node;
        }

        if (method_exists($node, '__toString')) {
            return (string) $node;
        }

        return get_class($node);
    }

    private function extractMethods($node): array
    {
        $methods = [];
        foreach ($node->getMethods() as $method) {
            $returnType = $method->getReturnType();
            $methods[] = [
                'name' => $method->name->name,
                'visibility' => $this->getVisibility($method),
                'params' => $this->extractParams($method),
                'returnType' => $returnType ? $this->nodeToString($returnType) : null,
            ];
        }

        return $methods;
    }

    private function extractProperties($node): array
    {
        $properties = [];
        foreach ($node->getProperties() as $property) {
            $properties[] = [
                'name' => $property->props[0]->name->name,
                'visibility' => $this->getVisibility($property),
                'type' => $property->type ? $this->nodeToString($property->type) : null,
            ];
        }

        return $properties;
    }

    private function extractParams($node): array
    {
        $params = [];
        foreach ($node->getParams() as $param) {
            $params[] = [
                'name' => $param->var->name,
                'type' => $param->type ? $this->nodeToString($param->type) : null,
                'default' => $param->default ? 'default' : null,
            ];
        }

        return $params;
    }

    private function getVisibility($node): string
    {
        if ($node->isPrivate()) {
            return 'private';
        }
        if ($node->isProtected()) {
            return 'protected';
        }

        return 'public';
    }
}
