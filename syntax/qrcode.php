<?php
/*
 */

require_once(dirname(dirname(__FILE__))."/phpqrcode/phpqrcode.php");

/**
 * Plugin qrcode
 *
 * @license    GNU
 * @author     Michael Braun <michael-dev@fami-braun.de>
 */

if (!defined('DOKU_INC'))
define('DOKU_INC', realpath(dirname( __FILE__ ).'/../../../').'/');
if (!defined('DOKU_PLUGIN'))
define('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');
require_once (DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */

class syntax_plugin_qrcode_qrcode extends DokuWiki_Syntax_Plugin
{

    /**
     * return some info
     */
    function getInfo()
    {
        return array (
        'author'=>'Michael Braun',
        'email'=>'michael-dev@fami-braun.de',
        'date'=>'2016-12-28',
        'name'=>'qrcode',
        'desc'=>'QR-code Plugin <qrcode>text####newline</qrcode>',
        'url'=>'',
        );
    }

    /**
     * What kind of syntax are we?
     */
    function getType()
    {
        return 'substition';
    }

    /**
     * What about paragraphs? (optional)
     */
    function getPType()
    {
        return 'inline';
    }

    /**
     * Where to sort in?
     */
    function getSort()
    {
        return 999;
    }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode)
    {
      $this->Lexer->addEntryPattern('<qrcode>',$mode,'plugin_qrcode_qrcode');
        #$this->Lexer->addSpecialPattern('~~QRCODE>.*~~', $mode, 'plugin_qrcode_qrcode');
    }
    function postConnect() { $this->Lexer->addExitPattern('</qrcode>','plugin_qrcode_qrcode'); }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, & $handler)
    {
        global $conf;
        switch ($state) {
          case DOKU_LEXER_ENTER :
            $this->state = "";
            return false;
          case DOKU_LEXER_UNMATCHED :
            $this->state .= $match;
            return false;
          case DOKU_LEXER_EXIT :
            $txt = $this->state;
            $this->state = "";
            $txt = str_replace("####","\n",$txt);
            ob_start();
            QRcode::png($txt,false,QR_ECLEVEL_L,1,1);
            $img = ob_get_contents();
            ob_end_clean();
            return Array("img" => $img, "txt" => $txt);
        }
        return false;
    }


    /**
     * Create output
     */
    function render($mode, & $renderer, $data)
    {

        if ($mode == 'xhtml' && $data !== false)
        {
            $renderer->doc .= '<img src="data:image/png;base64,'.base64_encode($data["img"]).'" style="valign:top;" alt="'.htmlspecialchars($data["txt"]).'" />';
            return true;
        }
        if ($mode == 'odt' && $data !== false)
        {
            if (!($tmp = io_mktmpdir())) return false;
            $path = $tmp.'/qr-'.md5($data["img"]).".png";
            file_put_contents($path, $data["img"]);
            #$renderer->_odtAddImage($path, /* $width = */ NULL, /* $height = */ NULL, /* $align = */ NULL, /* $title = */ $data["txt"], /* $style = */ NULL, /* $returnonly = */ false);
            $renderer->_odtAddImage($path);
            unlink($path);
            if ($tmp) io_rmdir($tmp, true);
            return true;
        }
        return false;
    }
} // Class


//Setup VIM: ex: et ts=4 enc=utf-8 :
