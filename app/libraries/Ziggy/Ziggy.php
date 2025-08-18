<?php

namespace App\Libraries\Ziggy;

use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use JsonSerializable;
use ReflectionMethod;
use ReflectionProperty;
use Illuminate\Database\Eloquent\Model;

/**
 * Lightweight Ziggy-style helper for exposing named Laravel routes to JavaScript.
 */
class Ziggy implements JsonSerializable
{
    protected array $routes = [];
    protected string $baseUrl;
    protected array $defaults = [];

    public function __construct(?array $routes = null, ?string $baseUrl = null, array $defaults = [])
    {
        $this->baseUrl = $baseUrl ?? rtrim(config('app.url', ''), '/');
        $this->defaults = $defaults;

        if ($routes === null) {
            $this->routes = $this->gatherRoutes();
        } else {
            $this->routes = $this->formatRouteList($routes);
        }
    }

    protected function gatherRoutes(): array
    {
        $collection = collect(app('router')->getRoutes()->getRoutes());
        $routes = [];

        $collection->each(function (Route $route) use (&$routes) {
            $name = $route->getName();
            if (empty($name)) {
                return;
            }

            try {
                $routes[$name] = $this->formatRoute($route);
            } catch (\Throwable $e) {
                // ignore problematic routes
            }
        });

        return $routes;
    }

    protected function formatRouteList(array $routes): array
    {
        $out = [];
        foreach ($routes as $name => $info) {
            $out[$name] = [
                'uri'     => $info['uri'] ?? ($info['path'] ?? '/'),
                'methods' => array_map('strtoupper', $info['methods'] ?? ($info['verbs'] ?? ['GET'])),
                'domain'  => $info['domain'] ?? null,
                'wheres'  => $info['wheres'] ?? [],
            ];
        }
        return $out;
    }

    protected function formatRoute(Route $route): array
    {
        $uri = $route->uri();
        $methods = array_values($route->methods());
        $domain = $route->getDomain();
        $wheres = $route->wheres ?: [];

        if (in_array('HEAD', $methods, true) && in_array('GET', $methods, true)) {
            $methods = array_values(array_diff($methods, ['HEAD']));
        }

        $wheres = $this->normalizeModelWheres($route, $wheres);

        return [
            'uri'     => $uri,
            'methods' => $methods,
            'domain'  => $domain ?: null,
            'wheres'  => $wheres,
        ];
    }

    protected function normalizeModelWheres(Route $route, array $existingWheres = []): array
    {
        $action = $route->getActionName();
        if (! $action || Str::contains($action, '@') === false) {
            return $existingWheres;
        }

        [$controller, $method] = explode('@', $action);

        if (empty($controller) || empty($method)) {
            return $existingWheres;
        }

        try {
            if (! class_exists($controller) || ! method_exists($controller, $method)) {
                return $existingWheres;
            }

            $ref = new ReflectionMethod($controller, $method);
            $params = $ref->getParameters();

            foreach ($params as $param) {
                $paramType = $param->getType();
                if ($paramType === null) {
                    continue;
                }

                $typeName = $paramType->getName();
                if (class_exists($typeName)) {
                    try {
                        if ((new \ReflectionClass($typeName))->isSubclassOf(Model::class) ||
                            $typeName === Model::class) {
                            $paramName = $param->getName();
                            if (! array_key_exists($paramName, $existingWheres)) {
                                $existingWheres[$paramName] = null;
                            }
                        }
                    } catch (\ReflectionException $e) {
                        // ignore
                    }
                }
            }
        } catch (\ReflectionException $e) {
            // ignore
        }

        return $existingWheres;
    }

    public function toArray(): array
    {
        return array_merge([
            'url'    => $this->baseUrl,
            'base'   => $this->baseUrl,
            'routes' => $this->routes,
        ], $this->defaults);
    }

    public function toJson(int $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * JsonSerializable implementation with compatible signature.
     *
     * IMPORTANT: the return type `mixed` ensures compatibility with PHP's JsonSerializable.
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        try {
            return $this->toJson();
        } catch (\Throwable $e) {
            return '{}';
        }
    }

    public function withDefaults(array $defaults): self
    {
        $this->defaults = array_merge($this->defaults, $defaults);
        return $this;
    }

    public function routes(): array
    {
        return $this->routes;
    }
}
