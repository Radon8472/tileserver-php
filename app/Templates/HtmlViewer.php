<?php
/**
 * @var string $serverTitle
 * @var string $protocol
 * @var string $baseUrl
 * @var array $maps
 */
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $serverTitle ?></title>
    <link rel="stylesheet" type="text/css" href="//cdn.klokantech.com/tileviewer/v1/index.css" />
    <script src="//cdn.klokantech.com/tileviewer/v1/index.js"></script>
</head>
<body>
    <script>
      tileserver({
        index: "<?php echo "$protocol://$baseUrl/index.json" ?>",
        tilejson: "<?php echo "$protocol://$baseUrl/%n.json" ?>",
        tms: "<?php echo "$protocol://$baseUrl/tms" ?>",
        wmts: "<?php echo "$protocol://$baseUrl/wmts" ?>"
      });
    </script>
    <h1>Welcome to <?php echo $serverTitle ?></h1>
    <p>This server distributes maps to desktop, web, and mobile applications.</p>
    <p>The mapping data are available as OpenGIS Web Map Tiling Service (OGC WMTS), OSGEO Tile Map Service (TMS), and popular XYZ urls described with TileJSON metadata.</p>
    <?php if (!isset($maps)) : ?>
        <h3 style="color:darkred;">No maps available yet</h3>
        <p style="color:darkred; font-style: italic;">
            Ready to go - just upload some maps into directory: <?php echo getcwd() ?> on this server.
        </p>
        <p>
            Note: The maps can be a directory with tiles in XYZ format with metadata.json file.<br/>
            You can easily convert existing geodata (GeoTIFF, ECW, MrSID, etc) to this tile structure with
            <a href="http://www.maptiler.com">MapTiler Cluster</a> or open-source projects such as
            <a href="http://www.klokan.cz/projects/gdal2tiles/">GDAL2Tiles</a> or
            <a href="http://www.maptiler.org/">MapTiler</a> or simply upload any maps in MBTiles format made by
            <a href="http://www.tilemill.com/">TileMill</a>. Helpful is also the
            <a href="https://github.com/mapbox/mbutil">mbutil</a> tool. Serving directly from .mbtiles files is supported, but with decreased performance.
        </p>
    <?php else : ?>
        <ul>

        <?php foreach ($maps as $map) : ?>
            <li><?php echo $map['name'] ?></li>
        <?php endforeach; ?>

        </ul>
    <?php endif; ?>
</body>
</html>