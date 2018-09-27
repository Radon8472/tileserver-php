<?php

/**
 * Class TileServerRouter
 */
class TileServer_Router
{
    /**
     * @var TileServer_Configuration
     */
    private $configuration;

    /**
     * TileServerRouter constructor.
     *
     * @param TileServer_Configuration $configuration
     */
    public function __construct($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Processes the routes for the application.
     *
     * @param array $routes
     */
    public function serve($routes) {
        $pathInfo = $this->resolvePathInfo();
        $baseUrls = $this->configuration->getBaseUrls();
        $resolvedHandler = null;
        $regex_matches = array();

        if ($routes) {
            $tokens = array(
                ':string' => '([a-zA-Z]+)',
                ':number' => '([0-9]+)',
                ':alpha' => '([a-zA-Z0-9-_@\.]+)'
            );

            foreach ($routes as $pattern => $handlerName) {
                $pattern = strtr($pattern, $tokens);

                if (!preg_match('#/?' . $pattern . '/?$#', $pathInfo, $matches)) {
                    continue;
                }

                if (!isset($baseUrls)) {
                    $baseUrls[0] = $_SERVER['HTTP_HOST'] . preg_replace('#/?' . $pattern . '/?$#', '', $pathInfo);
                }

                $resolvedHandler = $handlerName;
                $regex_matches = $matches;

                break;
            }
        }

        $serverInstance = null;

        if ($resolvedHandler) {
            if (is_string($resolvedHandler) && strpos($resolvedHandler, ':') !== false) {
                $resolvedClass = explode(':', $resolvedHandler);
                $resolvedMethod = explode(':', $resolvedHandler);
                $serverInstance = new $resolvedClass[0]($regex_matches);

                call_user_func(array($serverInstance, $resolvedMethod[1]));

                return;
            }

            if (is_string($resolvedHandler)) {
                new $resolvedHandler($regex_matches);

                return;
            }

            if (is_callable($resolvedHandler)) {
                $resolvedHandler();
            }

            return;
        }

        if (!isset($baseUrls[0])) {
            $baseUrls[0] = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        if (strpos($_SERVER['REQUEST_URI'], '=') != FALSE) {
            $kvp = explode('=', $_SERVER['REQUEST_URI']);
            $_GET['callback'] = $kvp[1];
            $params[0] = 'index';
            $serverInstance = new Json($params);
            $serverInstance->getJson();
        }

        $serverInstance = new Server;
        $serverInstance->getHtml();
    }

    /**
     * Resolves the path information for the router.
     *
     * @return string
     */
    private function resolvePathInfo()
    {
        if (empty($_SERVER['PATH_INFO'])) {
            return '/';
        }

        if (!empty($_SERVER['ORIG_PATH_INFO']) && strpos($_SERVER['ORIG_PATH_INFO'], 'tileserver.php') === false) {
            $pathInfo = $_SERVER['ORIG_PATH_INFO'];
        }

        if (empty($pathInfo) && !empty($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/tileserver.php') !== false) {
            $pathInfo = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $baseUrls = $this->configuration->getBaseUrls();
            $baseUrls[0] = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?';

            $this->configuration->setBaseUrls($baseUrls);
        }

        if (empty($pathInfo) && !empty($_SERVER['REQUEST_URI'])) {
            $pathInfo = (strpos($_SERVER['REQUEST_URI'], '?') > 0)
                ? strstr($_SERVER['REQUEST_URI'], '?', true)
                : $_SERVER['REQUEST_URI'];
        }

        return empty($pathInfo) ? $_SERVER['PATH_INFO'] : $pathInfo;
    }
}