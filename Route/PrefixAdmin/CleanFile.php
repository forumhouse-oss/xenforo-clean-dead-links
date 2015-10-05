<?php

/**
 * Class FH_LinkCleaner_Route_PrefixAdmin_CleanFile
 */
class FH_LinkCleaner_Route_PrefixAdmin_CleanFile
{
    /**
     * Method to be called when attempting to match this rule against a routing path.
     * Should return false if no matching happened or a {@link XenForo_RouteMatch} if
     * some level of matching happened. If no {@link XenForo_RouteMatch::$controllerName}
     * is returned, the {@link XenForo_Router} will continue to the next rule.
     *
     * @param                               $routePath
     * @param \Zend_Controller_Request_Http $request
     * @param \XenForo_Router               $router
     *
     * @internal param \Routing $string path
     * @internal param \Request $Zend_Controller_Request_Http object
     * @internal param \Router $XenForo_Router that routing is done within
     * @return false|XenForo_RouteMatch
     */
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithStringParam($routePath, $request, 'empty');

        return $router->getRouteMatch('FH_LinkCleaner_ControllerAdmin_CleanFile', $action, 'empty');
    }

    /**
     * Method to build a link to the specified page/action with the provided
     * data and params.
     *
     * @see XenForo_Route_BuilderInterface
     *
     * @param       $originalPrefix
     * @param       $outputPrefix
     * @param       $action
     * @param       $extension
     * @param       $data
     * @param array $extraParams
     *
     * @return false|string False if no data is provided, the link otherwise
     */
    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        return XenForo_Link::buildBasicLink($outputPrefix, $action, $extension);
    }
}
