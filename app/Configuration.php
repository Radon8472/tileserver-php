<?php

/**
 * Class TileServerConfiguration
 */
class TileServer_Configuration
{
    /**
     * @var string
     */
    private $dataRoot;

    /**
     * @var string
     */
    private $serverTitle;

    /**
     * @var array
     */
    private $baseUrls;

    /**
     * @var mixed
     */
    private $template;

    /**
     * @var string
     */
    private $protocol;

    /**
     * @var array
     */
    private $availableFormats;

    /**
     * @return string
     */
    public function getDataRoot()
    {
        return $this->dataRoot;
    }

    /**
     * @param string $dataRoot
     *
     * @return TileServer_Configuration
     */
    public function setDataRoot($dataRoot)
    {
        $this->dataRoot = $dataRoot;

        return $this;
    }

    /**
     * Detect if we have a custom data root set.
     *
     * @return bool
     */
    public function hasDataRoot()
    {
        return $this->dataRoot != '' && substr($this->dataRoot, -1) != '/';
    }

    /**
     * @return array|false|string
     */
    public function getServerTitle()
    {
        return $this->serverTitle;
    }

    /**
     * @param string $serverTitle
     *
     * @return TileServer_Configuration
     */
    public function setServerTitle($serverTitle)
    {
        if ($serverTitle === false) {
            $serverTitle = 'Maps hosted with TileServer-php v2.0';
        }

        $this->serverTitle = $serverTitle;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasServerTitle()
    {
        return $this->serverTitle !== false;
    }

    /**
     * @return array
     */
    public function getBaseUrls()
    {
        return $this->baseUrls;
    }

    /**
     * @param array|string $baseUrls
     *
     * @return TileServer_Configuration
     */
    public function setBaseUrls($baseUrls)
    {
        if ($baseUrls === false) {
            return $this;
        }

        if (!is_array($baseUrls)) {
            $baseUrls = explode(',', $baseUrls);
        }

        $this->baseUrls = $baseUrls;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param mixed $template
     *
     * @return TileServer_Configuration
     */
    public function setTemplate($template)
    {
        if ($template === false) {
            return $this;
        }

        $this->template = $template;

        return $this;
    }

    public function hasTemplate()
    {
        return $this->template !== null;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @param string $protocol
     *
     * @return TileServer_Configuration
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;

        return $this;
    }

    /**
     * @return array
     */
    public function getAvailableFormats()
    {
        return $this->availableFormats;
    }

    /**
     * @param array $availableFormats
     *
     * @return TileServer_Configuration
     */
    public function setAvailableFormats($availableFormats)
    {
        $this->availableFormats = $availableFormats;

        return $this;
    }
}