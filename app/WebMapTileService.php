<?php

/**
 * Class TileServer_WebMapTileService
 */
class TileServer_WebMapTileService extends TileServer_Server
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
     * TileServer_WebMapTileService constructor.
     *
     * @param TileServer_Configuration $configuration
     * @param array $params
     */
    public function __construct($configuration, $params) {
        parent::__construct($configuration);

        if (isset($params)) {
            parent::setParams($params);
        }
    }

    /**
     * Tests request from url and call method
     */
    public function get() {
        $request = $this->getGlobal('Request');
        if ($request !== FALSE && $request == 'gettile') {
            $this->getTile();
        } else {
            parent::setDatasets();
            $this->getCapabilities();
        }
    }

    /**
     * Validates tilematrixset, calculates missing params
     * @param Object $tileMatrix
     * @return Object
     */
    public function parseTileMatrix($layer, $tileMatrix)
    {
        //process projection
        if(isset($layer['proj4'])){
            preg_match_all("/([^+= ]+)=([^= ]+)/", $layer['proj4'], $res);
            $proj4 = array_combine($res[1], $res[2]);
        }

        for($i = 0; $i < count($tileMatrix); $i++){

            if(!isset($tileMatrix[$i]['id'])){
                $tileMatrix[$i]['id'] =  (string) $i;
            }
            if (!isset($tileMatrix[$i]['extent']) && isset($layer['extent'])) {
                $tileMatrix[$i]['extent'] = $layer['extent'];
            }
            if (!isset($tileMatrix[$i]['matrix_size'])) {
                $tileExtent = $this->tilesOfExtent(
                    $tileMatrix[$i]['extent'],
                    $tileMatrix[$i]['origin'],
                    $tileMatrix[$i]['pixel_size'],
                    $tileMatrix[$i]['tile_size']
                );
                $tileMatrix[$i]['matrix_size'] = array(
                    $tileExtent[2] + 1,
                    $tileExtent[1] + 1
                );
            }
            if(!isset($tileMatrix[$i]['origin']) && isset($tileMatrix[$i]['extent'])){
                $tileMatrix[$i]['origin'] = array(
                    $tileMatrix[$i]['extent'][0], $tileMatrix[$i]['extent'][3]
                );
            }
            // Origins of geographic coordinate systems are setting in opposite order
            if (isset($proj4) && $proj4['proj'] === 'longlat') {
                $tileMatrix[$i]['origin'] = array_reverse($tileMatrix[$i]['origin']);
            }
            if(!isset($tileMatrix[$i]['scale_denominator'])){
                $tileMatrix[$i]['scale_denominator'] = count($tileMatrix) - $i;
            }
            if(!isset($tileMatrix[$i]['tile_size'])){
                $tileSize = 256 * (int) $layer['scale'];
                $tileMatrix[$i]['tile_size'] = array($tileSize, $tileSize);
            }
        }

        return $tileMatrix;
    }

    /**
     * Calculates corners of tilematrix
     * @param array $extent
     * @param array $origin
     * @param array $pixel_size
     * @param array $tile_size
     * @return array
     */
    public function tilesOfExtent($extent, $origin, $pixel_size, $tile_size)
    {
        $tiles = array(
            $this->minsample($extent[0] - $origin[0], $pixel_size[0] * $tile_size[0]),
            $this->minsample($extent[1] - $origin[1], $pixel_size[1] * $tile_size[1]),
            $this->maxsample($extent[2] - $origin[0], $pixel_size[0] * $tile_size[0]),
            $this->maxsample($extent[3] - $origin[1], $pixel_size[1] * $tile_size[1]),
        );
        return $tiles;
    }

    /**
     * Default TileMetrixSet for Pseudo Mercator projection 3857
     * @param ?number $maxZoom
     * @return string TileMatrixSet xml
     */
    public function getMercatorTileMatrixSet($maxZoom = 18){
        $denominatorBase = 559082264.0287178;
        $extent = array(-20037508.34,-20037508.34,20037508.34,20037508.34);
        $tileMatrixSet = array();

        for($i = 0; $i <= $maxZoom; $i++){
            $matrixSize = pow(2, $i);
            $tileMatrixSet[] = array(
                'extent' => $extent,
                'id' => (string) $i,
                'matrix_size' => array($matrixSize, $matrixSize),
                'origin' => array($extent[0], $extent[3]),
                'scale_denominator' => $denominatorBase / pow(2, $i),
                'tile_size' => array(256, 256)
            );
        }

        return $this->getTileMatrixSet('GoogleMapsCompatible', $tileMatrixSet, 'EPSG:3857');
    }

    /**
     * Default TileMetrixSet for WGS84 projection 4326
     * @return string Xml
     */
    public function getWGS84TileMatrixSet(){
        $extent = array(-180.000000, -90.000000, 180.000000, 90.000000);
        $scaleDenominators = array(279541132.01435887813568115234, 139770566.00717943906784057617,
            69885283.00358971953392028809, 34942641.50179485976696014404, 17471320.75089742988348007202,
            8735660.37544871494174003601, 4367830.18772435747087001801, 2183915.09386217873543500900,
            1091957.54693108936771750450, 545978.77346554468385875225, 272989.38673277234192937613,
            136494.69336638617096468806, 68247.34668319308548234403, 34123.67334159654274117202,
            17061.83667079825318069197, 8530.91833539912659034599, 4265.45916769956329517299,
            2132.72958384978574031265);
        $tileMatrixSet = array();

        for($i = 0; $i <= 17; $i++){
            $matrixSize = pow(2, $i);
            $tileMatrixSet[] = array(
                'extent' => $extent,
                'id' => (string) $i,
                'matrix_size' => array($matrixSize * 2, $matrixSize),
                'origin' => array($extent[3], $extent[0]),
                'scale_denominator' => $scaleDenominators[$i],
                'tile_size' => array(256, 256)
            );
        }

        return $this->getTileMatrixSet('WGS84', $tileMatrixSet, 'EPSG:4326');
    }

    /**
     * Prints WMTS TileMatrixSet
     * @param string $name
     * @param array $tileMatrixSet Array of levels
     * @param string $crs Code of crs eg: EPSG:3857
     * @return string TileMatrixSet xml
     */
    public function getTileMatrixSet($name, $tileMatrixSet, $crs = 'EPSG:3857'){
        $srs = explode(':', $crs);
        $TileMatrixSet = '<TileMatrixSet>
      <ows:Title>' . $name . '</ows:Title>
      <ows:Abstract>' . $name . ' '. $crs .'</ows:Abstract>
      <ows:Identifier>' . $name . '</ows:Identifier>
      <ows:SupportedCRS>urn:ogc:def:crs:'.$srs[0].'::'.$srs[1].'</ows:SupportedCRS>';
        // <WellKnownScaleSet>urn:ogc:def:wkss:OGC:1.0:GoogleMapsCompatible</WellKnownScaleSet>;
        foreach($tileMatrixSet as $level){
            $TileMatrixSet .= '
      <TileMatrix>
        <ows:Identifier>' . $level['id'] . '</ows:Identifier>
        <ScaleDenominator>' .  $level['scale_denominator'] . '</ScaleDenominator>
        <TopLeftCorner>'.  $level['origin'][0] . ' ' .  $level['origin'][1] .'</TopLeftCorner>
        <TileWidth>' .  $level['tile_size'][0] . '</TileWidth>
        <TileHeight>' .  $level['tile_size'][1] . '</TileHeight>
        <MatrixWidth>' . $level['matrix_size'][0] . '</MatrixWidth>
        <MatrixHeight>' .  $level['matrix_size'][1] . '</MatrixHeight>
      </TileMatrix>';
        }
        $TileMatrixSet .= '</TileMatrixSet>';

        return $TileMatrixSet;
    }

    /**
     * Returns tilesets getCapabilities
     */
    public function getCapabilities() {

        $layers = array_merge($this->fileLayer, $this->dbLayer);

        //if TileMatrixSet is provided validate it
        for($i = 0; $i < count($layers); $i++){
            if($layers[$i]['profile'] == 'custom'){
                $layers[$i]['tile_matrix'] = $this->parseTileMatrix(
                    $layers[$i],
                    $layers[$i]['tile_matrix']
                );
            }
        }

        header('Content-type: application/xml');
        echo '<?xml version="1.0" encoding="UTF-8" ?>
<Capabilities xmlns="http://www.opengis.net/wmts/1.0" xmlns:ows="http://www.opengis.net/ows/1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:gml="http://www.opengis.net/gml" xsi:schemaLocation="http://www.opengis.net/wmts/1.0 http://schemas.opengis.net/wmts/1.0/wmtsGetCapabilities_response.xsd" version="1.0.0">
  <!-- Service Identification -->
  <ows:ServiceIdentification>
    <ows:Title>tileserverphp</ows:Title>
    <ows:ServiceType>OGC WMTS</ows:ServiceType>
    <ows:ServiceTypeVersion>1.0.0</ows:ServiceTypeVersion>
  </ows:ServiceIdentification>
  <!-- Operations Metadata -->
  <ows:OperationsMetadata>
    <ows:Operation name="GetCapabilities">
      <ows:DCP>
        <ows:HTTP>
          <ows:Get xlink:href="' . $this->config['protocol'] . '://' . $this->config['baseUrls'][0] . '/wmts/1.0.0/WMTSCapabilities.xml">
            <ows:Constraint name="GetEncoding">
              <ows:AllowedValues>
                <ows:Value>RESTful</ows:Value>
              </ows:AllowedValues>
            </ows:Constraint>
          </ows:Get>
          <!-- add KVP binding in 10.1 -->
          <ows:Get xlink:href="' . $this->config['protocol'] . '://' . $this->config['baseUrls'][0] . '/wmts?">
            <ows:Constraint name="GetEncoding">
              <ows:AllowedValues>
                <ows:Value>KVP</ows:Value>
              </ows:AllowedValues>
            </ows:Constraint>
          </ows:Get>
        </ows:HTTP>
      </ows:DCP>
    </ows:Operation>
    <ows:Operation name="GetTile">
      <ows:DCP>
        <ows:HTTP>
          <ows:Get xlink:href="' . $this->config['protocol'] . '://' . $this->config['baseUrls'][0] . '/wmts/">
            <ows:Constraint name="GetEncoding">
              <ows:AllowedValues>
                <ows:Value>RESTful</ows:Value>
              </ows:AllowedValues>
            </ows:Constraint>
          </ows:Get>
          <ows:Get xlink:href="' . $this->config['protocol'] . '://' . $this->config['baseUrls'][0] . '/wmts?">
            <ows:Constraint name="GetEncoding">
              <ows:AllowedValues>
                <ows:Value>KVP</ows:Value>
              </ows:AllowedValues>
            </ows:Constraint>
          </ows:Get>
        </ows:HTTP>
      </ows:DCP>
    </ows:Operation>
  </ows:OperationsMetadata>
  <Contents>';

        $customtileMatrixSets = '';
        $maxMercatorZoom = 18;

        //layers
        foreach ($layers as $m) {

            $basename = $m['basename'];
            $title = (array_key_exists('name', $m)) ? $m['name'] : $basename;
            $profile = $m['profile'];
            $bounds = $m['bounds'];
            $format = $m['format'] == 'hybrid' ? 'jpgpng' : $m['format'];
            $mime = ($format == 'jpg') ? 'image/jpeg' : 'image/' . $format;

            if ($profile == 'geodetic') {
                $tileMatrixSet = 'WGS84';
            }elseif ($m['profile'] == 'custom') {
                $crs = explode(':', $m['crs']);
                $tileMatrixSet = 'custom' . $crs[1] . $m['basename'];
                $customtileMatrixSets .= $this->getTileMatrixSet(
                    $tileMatrixSet,
                    $m['tile_matrix'],
                    $m['crs']
                );
            } else {
                $tileMatrixSet = 'GoogleMapsCompatible';
                $maxMercatorZoom = max($maxMercatorZoom, $m['maxzoom']);
            }

            $wmtsHost = substr($m['tiles'][0], 0, strpos($m['tiles'][0], $m['basename']));
            $resourceUrlTemplate = $wmtsHost . $basename
                . '/{TileMatrix}/{TileCol}/{TileRow}';
            if(strlen($format) <= 4){
                $resourceUrlTemplate .= '.' . $format;
            }

            echo'
    <Layer>
      <ows:Title>' . $title . '</ows:Title>
      <ows:Identifier>' . $basename . '</ows:Identifier>
      <ows:WGS84BoundingBox crs="urn:ogc:def:crs:OGC:2:84">
        <ows:LowerCorner>' . $bounds[0] . ' ' . $bounds[1] . '</ows:LowerCorner>
        <ows:UpperCorner>' . $bounds[2] . ' ' . $bounds[3] . '</ows:UpperCorner>
      </ows:WGS84BoundingBox>
      <Style isDefault="true">
        <ows:Identifier>default</ows:Identifier>
      </Style>
      <Format>' . $mime . '</Format>
      <TileMatrixSetLink>
        <TileMatrixSet>' . $tileMatrixSet . '</TileMatrixSet>
      </TileMatrixSetLink>
      <ResourceURL format="' . $mime . '" resourceType="tile" template="' . $resourceUrlTemplate . '"/>
    </Layer>';
        }

        // Print custom TileMatrixSets
        if (strlen($customtileMatrixSets) > 0) {
            echo $customtileMatrixSets;
        }

        // Print PseudoMercator TileMatrixSet
        echo $this->getMercatorTileMatrixSet($maxMercatorZoom);

        // Print WGS84 TileMatrixSet
        echo $this->getWGS84TileMatrixSet();

        echo '</Contents>
  <ServiceMetadataURL xlink:href="' . $this->config['protocol'] . '://' . $this->config['baseUrls'][0] . '/wmts/1.0.0/WMTSCapabilities.xml"/>
</Capabilities>';
    }

    /**
     * Returns tile via WMTS specification
     */
    public function getTile() {
        $request = $this->getGlobal('Request');
        if ($request) {
            if (strpos('/', $_GET['Format']) !== FALSE) {
                $format = explode('/', $_GET['Format']);
                $format = $format[1];
            } else {
                $format = $this->getGlobal('Format');
            }
            parent::renderTile(
                $this->getGlobal('Layer'),
                $this->getGlobal('TileMatrix'),
                $this->getGlobal('TileRow'),
                $this->getGlobal('TileCol'),
                $format
            );
        } else {
            parent::renderTile($this->layer, $this->z, $this->y, $this->x, $this->ext);
        }
    }

    /**
     * @param $x
     * @param $f
     *
     * @return float
     */
    private function minsample($x, $f)
    {
        return $f > 0 ? floor($x / $f) : ceil(($x / $f) - 1);
    }

    /**
     * @param $x
     * @param $f
     *
     * @return float
     */
    private function maxsample($x, $f)
    {
        return $f < 0 ? floor($x / $f) : ceil(($x / $f) - 1);
    }
}