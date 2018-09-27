<?php

/**
 * Class TileServer_Renderer
 */
class TileServer_Renderer
{
    /**
     * Echo's out the requested HTML
     *
     * @param $template
     * @param $data
     */
    public static function renderHtmlTemplate($template, $data)
    {
        header('Content-Type: text/html;charset=UTF-8');

        // Load the data variables into the template context
        extract($data);

        echo include_once 'Templates/' . $template . '.php';
    }
}