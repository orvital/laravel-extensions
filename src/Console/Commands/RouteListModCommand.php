<?php

namespace Orvital\Extensions\Console\Commands;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;

class RouteListModCommand extends Command
{
    protected $name = 'route:list-mod';

    protected $description = 'List all registered routes (modified)';

    /**
     * The router instance.
     */
    protected Router $router;

    /**
     * The table headers for the command.
     */
    protected array $headers = ['Domain', 'Method', 'URI', 'Name', 'Action', 'Middleware'];

    /**
     * The verb colors for the command.
     */
    protected array $verbColors = [
        'ANY' => 'red',
        'GET' => 'blue',
        'HEAD' => '#6C7280',
        'OPTIONS' => '#6C7280',
        'POST' => 'yellow',
        'PUT' => 'yellow',
        'PATCH' => 'yellow',
        'DELETE' => 'red',
    ];

    /**
     * Create a new route command instance.
     *
     * @return void
     */
    public function __construct(Router $router)
    {
        parent::__construct();

        $this->router = $router;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->router->flushMiddlewareGroups();

        $routes = collect($this->router->getRoutes());

        if ($routes->isEmpty()) {
            return $this->components->error("Your application doesn't have any routes.");
        }

        $routes = $this->compileRoutes($routes);

        if ($routes->isEmpty()) {
            return $this->components->error("Your application doesn't have any routes matching the given criteria.");
        }

        $this->option('json')
            ? $this->forJson($routes)
            : $this->forTable($routes);
    }

    /**
     * Compile the routes into a displayable format.
     */
    protected function compileRoutes(Collection $routes): Collection
    {
        // Filter and transform
        $routes = $routes->map(function (Route $route) {
            return $this->shouldIncludeRoute($route) ? $this->getRouteInformation($route) : null;
        })->filter();

        // Sort
        $sort = $this->option('sort') ?? 'uri';
        $descending = (bool) $this->option('reverse');
        $routes = $routes->sortBy($sort, SORT_REGULAR, $descending);

        // Remove unnecessary columns
        $cols = $this->getColumns();
        $routes = $routes->map(function ($route) use ($cols) {
            return Arr::only($route, $cols);
        });

        return $routes;
    }

    /**
     * Filter the route by URI and / or name.
     */
    protected function shouldIncludeRoute(Route $route): bool
    {
        if (($this->option('name') && ! Str::contains((string) $route->getName(), $this->option('name'))) ||
            ($this->option('uri') && ! Str::contains($route->uri(), $this->option('uri'))) ||
            ($this->option('method') && ! Str::contains(implode('|', $route->methods()), strtoupper($this->option('method')))) ||
            ($this->option('domain') && ! Str::contains((string) $route->domain(), $this->option('domain')))) {
            return false;
        }

        if ($this->option('except-uri')) {
            foreach (explode(',', $this->option('except-uri')) as $uri) {
                if (str_contains($route->uri(), $uri)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get the route information for a given route.
     */
    protected function getRouteInformation(Route $route): array
    {
        return [
            'domain' => $route->domain(),
            'method' => $route->methods() === Router::$verbs ? 'ANY' : implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => ltrim($route->getActionName(), '\\'),
            'middleware' => $this->getMiddleware($route),
        ];
    }

    /**
     * Get the middleware for the route.
     */
    protected function getMiddleware(Route $route): array
    {
        $middlewareMap = array_flip($this->router->getMiddleware());

        $middlewares = $this->router->gatherRouteMiddleware($route);

        $middlewares = collect($middlewares)->map(function ($middleware) use ($middlewareMap) {
            $middleware = $middleware instanceof Closure ? 'Closure' : $middleware;

            // show short middlewares
            if (true) {
                $key = Str::before($middleware, ':');

                if (Arr::exists($middlewareMap, $key)) {
                    $middleware = Str::replace($key, $middlewareMap[$key], $middleware);
                }
            }

            return $middleware;
        })->all();

        return $middlewares;
    }

    /**
     * Get the table headers for the visible columns.
     */
    protected function getHeaders(): array
    {
        return Arr::only($this->headers, array_keys($this->getColumns()));
    }

    /**
     * Get the column names to show (lowercase table headers).
     */
    protected function getColumns(): array
    {
        return array_map('strtolower', $this->headers);
    }

    /**
     * Parse the column list.
     */
    protected function parseColumns(array $columns): array
    {
        $results = [];

        foreach ($columns as $column) {
            if (str_contains($column, ',')) {
                $results = array_merge($results, explode(',', $column));
            } else {
                $results[] = $column;
            }
        }

        return array_map('strtolower', $results);
    }

    protected function forJson(Collection $routes): void
    {
        $this->line($routes->values()->toJson());
    }

    /**
     * Convert the given routes to table.
     */
    protected function forTable(Collection $routes): void
    {
        $table = new Table($this->output);

        $headers = ['Method', 'URI', 'Name', 'Middleware'];
        $table->setHeaders($headers);

        $rows = $routes->map(function ($route) {
            $method = $this->formatMethod($route);
            $uri = $this->formatUri($route);

            return [
                'method' => $method,
                'uri' => $uri,
                'name' => $route['name'],
                'middleware' => implode(', ', $route['middleware']),
                // 'action' => $route['action'],
            ];
        })->toArray();

        $table->setRows($rows);

        $table->render();
    }

    protected function formatMethod(array $route): ?string
    {
        $method = Str::of($route['method'])->explode('|')->map(function ($method) {
            return sprintf('<fg=%s>%s</>', $this->verbColors[$method] ?? 'default', $method);
        })->implode('<fg=#6C7280>|</>');

        return $method;
    }

    protected function formatUri(array $route): ?string
    {
        $uri = $route['uri'];

        if ($route['domain']) {
            $uri = $route['domain'].'/'.ltrim($uri, '/');
        }

        $uri = preg_replace('#({[^}]+})#', '<fg=yellow>$1</>', $uri);

        return $uri;
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['json', null, InputOption::VALUE_NONE, 'Output the route list as JSON'],
            ['domain', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by domain'],
            ['method', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by method'],
            ['name', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by name'],
            ['uri', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by uri'],
            ['except-uri', null, InputOption::VALUE_OPTIONAL, 'Reject the routes by uri'],
            ['sort', null, InputOption::VALUE_OPTIONAL, 'The column (domain, method, uri, name, action, middleware) to sort by in descending order', 'uri'],
            ['reverse', 'r', InputOption::VALUE_NONE, 'Reverse the sort order of the routes'],
        ];
    }
}
