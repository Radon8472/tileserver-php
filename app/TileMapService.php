<?php

/**
 * Class TileServer_TileMapService
 */
class TileServer_TileMapService extends TileServer_Server
{
    /**
     * @param array $params
     */
    public $layer;

    /**
     * @var integer
     */
    public $z;

    /**
     * @var integer
     */
    public $y;

    /**
     * @var integer
     */
    public $x;

    /**
     * @var string
     */
    public $ext;

    /**
     * TileServer_TileMapService constructor.
     * @param TileServer_Configuration $configuration
     * @param array $params
     */
    public function __construct($configuration, $params) {
        parent::__construct($configuration);
        parent::setParams($params);
    }

    /**
     * Returns getCapabilities metadata request
     */
    public function getCapabilities() {
        parent::setDatasets();
        $maps = array_merge($this->fileLayer, $this->dbLayer);
        header('Content-type: application/xml');
        echo'<TileMapService version="1.0.0"><TileMaps>';
        foreach ($maps as $m) {
            $basename = $m['basename'];
            $title = (array_key_exists('name', $m) ) ? $m['name'] : $basename;
            $profile = $m['profile'];
            if ($profile == 'geodetic') {
                $srs = 'EPSG:4326';
            } else {
                $srs = 'EPSG:3857';
            }
            $url = $this->config['protocol'] . '://' . $this->config['baseUrls'][0]
                . '/tms/' . $basename;
            echo '<TileMap title="' . $title . '" srs="' . $srs
                . '" type="InvertedTMS" ' . 'profile="global-' . $profile
                . '" href="' . $url . '" />';
        }
        echo '</TileMaps></TileMapService>';
    }

    /**
     * Prints metadata about layer
     */
    public function getLayerCapabilities() {
        parent::setDatasets();
        $maps = array_merge($this->fileLayer, $this->dbLayer);
        foreach ($maps as $map) {
            if (strpos($map['basename'], $this->layer) !== false) {
                $m = $map;
                break;
            }
        }
        $title = (array_key_exists('name', $m)) ? $m['name'] : $m['basename'];
        $description = (array_key_exists('description', $m)) ? $m['description'] : "";
        $bounds = $m['bounds'];
        if ($m['profile'] == 'geodetic') {
            $srs = 'EPSG:4326';
            $initRes = 0.703125;
        } elseif ($m['profile'] == 'custom') {
            $srs = $m['crs'];
            $bounds = $m['extent'];
            if(isset($m['tile_matrix'][0]['pixel_size'][0])){
                $initRes = $m['tile_matrix'][0]['pixel_size'][0];
            }else{
                $initRes = 1;
            }
        } else {
            $srs = 'EPSG:3857';
            $bounds = array(-20037508.34,-20037508.34,20037508.34,20037508.34);
            $initRes = 156543.03392804062;
        }
        $mime = ($m['format'] == 'jpg') ? 'image/jpeg' : 'image/png';
        header("Content-type: application/xml");
        $serviceUrl = $this->config['protocol'] . '://' . $this->config['baseUrls'][0] . '/' . $m['basename'];
        echo '<TileMap version="1.0.0" tilemapservice="' . $serviceUrl . '" type="InvertedTMS">
  <Title>' . htmlspecialchars($title) . '</Title>
  <Abstract>' . htmlspecialchars($description) . '</Abstract>
  <SRS>' . $srs . '</SRS>
  <BoundingBox minx="' . $bounds[0] . '" miny="' . $bounds[1] . '" maxx="' . $bounds[2] . '" maxy="' . $bounds[3] . '" />
  <Origin x="' . $bounds[0] . '" y="' . $bounds[1] . '"/>
  <TileFormat width="256" height="256" mime-type="' . $mime . '" extension="' . $m['format'] . '"/>
  <TileSets profile="global-' . $m['profile'] . '">';
        for ($zoom = $m['minzoom']; $zoom < $m['maxzoom'] + 1; $zoom++) {
            $res = $initRes / pow(2, $zoom);
            $url = $this->config['protocol'] . '://' . $this->config['baseUrls'][0] . '/' . $m['basename'] . '/' . $zoom;
            echo '<TileSet href="' . $url . '" units-per-pixel="' . $res . '" order="' . $zoom . '" />';
        }
        echo'</TileSets></TileMap>';
    }

    /**
     * Process getTile request
     */
    public function getTile() {
        parent::renderTile($this->layer, $this->z, $this->y, $this->x, $this->ext);
    }
}