<?php

class FilterSpecialPages {

	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		if( $out->getTitle()->isSpecial( "Specialpages") ){
			$out->addModules("ext.filterspecialpages");
		}
		return true;
	}

}
