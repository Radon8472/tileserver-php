<?php

/**
 * Class TileServer_Json
 */
class TileServer_Json extends TileServer_Server
{
    /**
     * Callback for JSONP default grid
     * @var string
     */
    private $callback = 'grid';

    /**
     * @param array $params
     */
    public $layer = 'index';

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
     * TileServer_Json constructor.
     *
     * @param TileServer_Configuration $configuration
     * @param array $params
     */
    public function __construct($configuration, $params) {
        parent::__construct($configuration);
        parent::setParams($params);
        if (isset($_GET['callback']) && !empty($_GET['callback'])) {
            $this->callback = $_GET['callback'];
        }
    }

    /**
     * Adds metadata about layer
     * @param array $metadata
     * @return array
     */
    public function metadataTileJson($metadata) {
        $metadata['tilejson'] = '2.0.0';
        $metadata['scheme'] = 'xyz';
        if ($this->isDBLayer($metadata['basename'])) {
            $this->DBconnect($this->config['dataRoot'] . $metadata['basename'] . '.mbtiles');
            $res = $this->db->query('SELECT name FROM sqlite_master WHERE name="grids";');
            if ($res) {
                foreach ($this->config['baseUrls'] as $url) {
                    $grids[] = '' . $this->config['protocol'] . '://' . $url . '/' . $metadata['basename'] . '/{z}/{x}/{y}.grid.json';
                }
                $metadata['grids'] = $grids;
            }
        }
        if (array_key_exists('json', $metadata)) {
            $mjson = json_decode(stripslashes($metadata['json']));
            foreach ($mjson as $key => $value) {
                if ($key != 'Layer'){
                    $metadata[$key] = $value;
                }
            }
            unset($metadata['json']);
        }
        return $metadata;
    }

    /**
     * Creates JSON from array
     * @param string $basename
     * @return string
     */
    private function createJson($basename) {
        $maps = array_merge($this->fileLayer, $this->dbLayer);
        if ($basename == 'index') {
            $output = '[';
            foreach ($maps as $map) {
                $output = $output . json_encode($this->metadataTileJson($map)) . ',';
            }
            if (strlen($output) > 1) {
                $output = substr_replace($output, ']', -1);
            } else {
                $output = $output . ']';
            }
        } else {
            foreach ($maps as $map) {
                if (strpos($map['basename'], $basename) !== false) {
                    $output = json_encode($this->metadataTileJson($map));
                    break;
                }
            }
        }
        if (!isset($output)) {
            echo 'TileServer: unknown map ' . $basename;
            die;
        }
        return stripslashes($output);
    }

    /**
     * Returns JSON with callback
     */
    public function getJson() {
        parent::setDatasets();
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        if ($this->callback !== 'grid') {
            echo $this->callback . '(' . $this->createJson($this->layer) . ');'; die;
        } else {
            echo $this->createJson($this->layer); die;
        }
    }

    /**
     * Returns JSONP with callback
     */
    public function getJsonp() {
        parent::setDatasets();
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/javascript; charset=utf-8');
        echo $this->callback . '(' . $this->createJson($this->layer) . ');';
    }

    /**
     * Returns UTFGrid in JSON format
     */
    public function getUTFGrid() {
        parent::renderUTFGrid($this->layer, $this->z, $this->y, $this->x);
    }

}