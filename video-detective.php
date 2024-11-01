<?php

/*
Plugin Name: Video Detective
Plugin URI: http://www.videodetective.com
Description: Plugin for showing trailers from Video Detective. Simply add [trailer:#published_id] to your post (where #published_id is the number for the trailer).
Version: 1.0
Author: Stephen Tallamy
Author URI: http://twitter.com/StephenTallamy
*/

if (!class_exists('VideoDetective'))
{
    class VideoDetective
    {       
        public $width         = 620;
        public $height        = 350;
        public $bitrate       = 750;
        public $customerId    = 0;
        public $playerId      = 0;
        public $playerVersion = 5.2;

		function __construct()
        {
            //will add options if they don't already exist
            add_option('video_detective_width'          , $this->width          , '', 'yes');
            add_option('video_detective_height'         , $this->height         , '', 'yes');
            add_option('video_detective_bitrate'        , $this->bitrate        , '', 'yes');
            add_option('video_detective_customer_id'    , $this->customerId     , '', 'yes');
            add_option('video_detective_player_id'      , $this->playerId       , '', 'yes');
            add_option('video_detective_player_version' , $this->playerVersion  , '', 'yes');

            $this->width         = get_option('video_detective_width');
            $this->height        = get_option('video_detective_height');
            $this->bitrate       = get_option('video_detective_bitrate');
            $this->customerId    = get_option('video_detective_customer_id');
            $this->playerId      = get_option('video_detective_player_id');
            $this->playerVersion = get_option('video_detective_player_version');

            add_filter("plugin_action_links", array(&$this, "addConfigureLink"), 10, 2);
			add_action("wp_head"            , array(&$this, "addHeaderCode"));
			add_filter("the_content"        , array(&$this, "processContent"), 20);
            add_action('admin_menu'         , array(&$this, "adminMenu"));
		}

		function processContent($content = '')
        {
			// Replace [trailer syntax]
            $pattern = "/(<p>)?\[trailer:(([^]]+))\](<\/p>)?/i";
            $content = preg_replace_callback( $pattern, array(&$this, "insertPlayer"), $content );

			return $content;
		}

		function insertPlayer($matches)
        {
			// Split options
			$trailerId = $matches[3];

            if ($this->customerId)
            {
                $html = <<<HTML
<div id="iva{$trailerId}" class="videoDetective"></div>
<script type="text/javascript">
<!--
    ivaplayer('iva{$trailerId}').setup({
        'flashplayer': 'http://www.videodetective.net/flash/players/?pversion={$this->playerVersion}&playerid={$this->playerId}&sub=HTML5ReportTag',
        'width'      : '{$this->width}',
        'height'     : '{$this->height}',
        'customerid' : '{$this->customerId}',
        'publishedid': '{$trailerId}',
        'playerid'   : '{$this->playerId}',
        'playlistid' : '0',
        'videokbrate': '{$this->bitrate}',
        'fmt'        : '4',
        'sub'        : 'HTML5ReportTag'
    });
//-->
</script>
HTML;
            }
            else
            {
                $html = <<<HTML
<embed src="http://www.videodetective.net/flash/players/movieapi/?publishedid={$trailerId}" flashvars="skin=stylish&autostart=false" bgcolor="#000000" width="{$this->width}" height="{$this->height}" allowfullscreen="true" allowscriptaccess="always"/>
HTML;

            }


			return $html;
		}

		function addHeaderCode()
        {
            if ($this->customerId)
            {
                echo '<script type="text/javascript" src="http://www.videodetective.net/html5/js/ivaplayer-1.1.min.js"></script>';
            }
		}

        function adminMenu()
        {
            add_options_page('Video Detective Options', 'Video Detective', 'manage_options', 'video_detective_menu', array(&$this, "settingsPage"));
        }

        function addConfigureLink($links, $file)
        {
			static $this_plugin;
			if (!$this_plugin)
            {
				$this_plugin = plugin_basename(__FILE__);
			}
			if ($file == $this_plugin)
            {
				$settings_link = '<a href="options-general.php?page=video_detective_menu">' . __('Settings') . '</a>';
				array_unshift($links, $settings_link);
			}
			return $links;
		}

        function settingsPage()
        {
            $nonce = wp_nonce_field('update-options', '_wpnonce', true, false);
            echo <<<HTML
<h2>Video Detective Options</h2>
<form action="options.php" method="POST">
$nonce
<table>
    <tr><td>Customer Id</td><td><input name="video_detective_customer_id" type="text" value="{$this->customerId}"/></td></tr>
    <tr><td>Player Id</td><td><input name="video_detective_player_id" type="text" value="{$this->playerId}"/></td></tr>
    <tr><td>Player Version</td><td><input name="video_detective_player_version" type="text" value="{$this->playerVersion}"/></td></tr>
    <tr><td>Player Width</td><td><input name="video_detective_width" type="text" value="{$this->width}"/></td></tr>
    <tr><td>Player Height</td><td><input name="video_detective_height" type="text" value="{$this->height}"/></td></tr>
    <tr><td>Player Bitrate</td><td><input name="video_detective_bitrate" type="text" value="{$this->bitrate}"/></td></tr>
</table>
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="video_detective_customer_id, video_detective_player_id, video_detective_width, video_detective_height, video_detective_bitrate, video_detective_player_version" />    
<input type="submit" value="Submit"/>
</form>
HTML;

        }
	}
}

$videoDetective = new VideoDetective();

?>