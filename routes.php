<?php

/** @var TileServer_Configuration $config */
$router = new TileServer_Router($config);

/*
 * Eventually this should leverage a composer package once
 * the support for 5.2 can be deprecated away and a modern
 * version of PHP is used.
 */
$router->serve(array(
    '/'                                                 => 'Server:getHtml',
    '/maps'                                             => 'Server:getInfo',
    '/html'                                             => 'Server:getHtml',
    '/:alpha/:number/:number/:number.grid.json'         => 'Json:getUTFGrid',
    '/:alpha.json'                                      => 'Json:getJson',
    '/:alpha.jsonp'                                     => 'Json:getJsonp',
    '/wmts'                                             => 'Wmts:get',
    '/wmts/1.0.0/WMTSCapabilities.xml'                  => 'Wmts:get',
    '/wmts/:alpha/:number/:number/:alpha'               => 'Wmts:getTile',
    '/wmts/:alpha/:alpha/:number/:number/:alpha'        => 'Wmts:getTile',
    '/wmts/:alpha/:alpha/:alpha/:number/:number/:alpha' => 'Wmts:getTile',
    '/:alpha/:number/:number/:alpha'                    => 'Wmts:getTile',
    '/tms'                                              => 'Tms:getCapabilities',
    '/tms/:alpha'                                       => 'Tms:getLayerCapabilities',
));
