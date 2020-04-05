<?php
# Stream MP3 with DewPlayer little flash ;-)
# 
# http://www.estvideo.com/dew/index/2005/12/03/603-dewplayer-reloaded
# If you don't understand french, scroll to "Télécharger l'animation Shockwave Flash"
# and choose "dewplayer 1.2 reloaded" or higher, (ignore the "soviet edition")
#
# About the licence of the DewPlayer, here is what its author says :
# the dewlicence : you can use it as you want, no need to link to author's site,
# principles of Creative Commons Attribution-ShareAlike License France.
# 
# Tag :
#   <mp3>uploaded filename.mp3 or URL</mp3>
#   <mp3>uploaded filename.mp3 or URL|download</mp3> adds an icon with link to download the mp3
# Requires :
#   dewplayer.swf in the $wgScriptPath/extensions directory
#   download.gif in the images ($wgUploadPath) directory : an image of your choice
# 
# To activate the extension :
# - include it at the end of your LocalSettings.php : include("extensions/mp3.php");
# - add .mp3 in the extension list if you want to allow mp3 uploads :
#   $wgFileExtensions = array('png','gif',...,'mp3');
# 
# Enjoy !

$wgExtensionFunctions[] = 'wfMp3';
$wgExtensionCredits['parserhook'][] = array(
        'name' => 'mp3',
        'description' => '[http://www.estvideo.com/dew/index/2005/12/03/603-dewplayer-reloaded ' .
                        'Flash dewplayer] is light and streams your mp3',
        'author' => 'Sylvain Machefert',
        'url' => 'http://meta.wikimedia.org/wiki/Mp3'
);

function wfMp3() {
        global $wgParser;
        $wgParser->setHook('mp3', 'renderMp3');
}

# The callback function for converting the input text to HTML output
function renderMp3($input) {
        global $wgScriptPath, $wgUploadPath;
        //$input = "filename.mp3"
        $arr = explode('|', trim($input));
        $addDLlink = isset($arr[1]) && ($arr[1] == 'download');
        $input = $arr[0];
        $img = Image::newFromName($input);
        $mp3 = '';
        $bgcolor = 'FFF8DC'; //You can change it, of course :-)
        
        //The parameters for object and embed
        # File uploaded or external link ?
        if (!$img->exists()) {
                //Must be http://... URL
                if (substr($input,0,7) == 'http://')
                        $mp3 = $input;
        } else
                $mp3 = $img->getURL();
        if ($mp3 == '')
                return '<div class="noprint">Fichier manquant : '.$input.'<br />'
                        .'Missing ressource: '.$input.'</div>';
        unset($img);
        
        $output = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" '
        . 'codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" '
        . 'width="150" height="20" id="dewplayer" align="middle">'
        . '<param name="allowScriptAccess" value="sameDomain" />'
        . '<param name="movie" value="'.$wgScriptPath.'/extensions/dewplayer.swf?son='.$mp3.'&bgcolor='.$bgcolor.'" />'
        . '<param name="quality" value="high" />'
        . '<param name="bgcolor" value="FFF8DC" />'
        . '<embed src="'.$wgScriptPath.'/extensions/dewplayer.swf?son='.$mp3
                        .'&bgcolor='.$bgcolor.'" quality="high" bgcolor="FFF8DC" width="150" height="20" '
                        .'name="dewplayer" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" '
                        .'pluginspage="http://www.macromedia.com/go/getflashplayer">'
                .'</embed>'
        .'</object>';
        if ($addDLlink) {
                $output .= '<a href="'.$mp3.'" title="Download">'
                        .'<img src="'.$wgUploadPath.'/download.gif" alt="Download" />'
                        .'</a>';
        }
        return $output;
}
?>
