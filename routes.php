<?php

/** @var TileServer_Configuration $config */
$router = new TileServer_Router($config);

/*
 * Eventually this should leverage a composer package once
 * the support for 5.2 can be deprecated away and a modern
 * version of PHP is used.
 */
$router->serve(array(
    '/'                                                 => 'TileServer_Server:getHtml',
    '/maps'                                             => 'TileServer_Server:getInfo',
    '/html'                                             => 'TileServer_Server:getHtml',
    '/:alpha/:number/:number/:number.grid.json'         => 'TileServer_Json:getUTFGrid',
    '/:alpha.json'                                      => 'TileServer_Json:getJson',
    '/:alpha.jsonp'                                     => 'TileServer_Json:getJsonp',
    '/wmts'                                             => 'TileServer_WebMapTileService:get',
    '/wmts/1.0.0/WMTSCapabilities.xml'                  => 'TileServer_WebMapTileService:get',
    '/wmts/:alpha/:number/:number/:alpha'               => 'TileServer_WebMapTileService:getTile',
    '/wmts/:alpha/:alpha/:number/:number/:alpha'        => 'TileServer_WebMapTileService:getTile',
    '/wmts/:alpha/:alpha/:alpha/:number/:number/:alpha' => 'TileServer_WebMapTileService:getTile',
    '/:alpha/:number/:number/:alpha'                    => 'TileServer_WebMapTileService:getTile',
    '/tms'                                              => 'TileServer_TileMapService:getCapabilities',
    '/tms/:alpha'                                       => 'TileServer_TileMapService:getLayerCapabilities',
));
