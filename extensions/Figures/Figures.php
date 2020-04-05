<?php

class Figures {

	public static function onParserSetup( Parser &$parser ) {
		$parser->setFunctionHook( 'figure', 'Figures::parseFigureParserExtension' );
		$parser->setFunctionHook( 'xref', 'Figures::parseXrefParserExtension' );
		return true;
	}

	public static function parseFigureParserExtension( $parser ) {
		$options = self::extractOptions( array_slice( func_get_args(), 1 ) );
		$figure_label = $options['label'];
		$figure_content = $options['content'];
		$figure_content = $parser->recursiveTagParse( $figure_content );

		$output = '<figure id="'. str_replace( ' ', '_', $figure_label ) .'" xreflabel="'. $figure_label .'">'. $figure_content .'</figure>';
		return array( $output, 'noparse' => true, 'isHTML' => true );
	}

	public static function parseXrefParserExtension( $parser ) {
		$options = self::extractOptions( array_slice( func_get_args(), 1 ) );
		$figure_label = $options['label'];
		$figure_page = $options['page'];
		$figure_page_link = Title::newFromText( $figure_page )->getFullURL();

		$output = '<a class="xref" href="'. $figure_page_link . '#' . str_replace( ' ', '_', $figure_label ) .'">'. $figure_label .'</a>';
		return array( $output, 'noparse' => true, 'isHTML' => true );
	}

	public static function extractOptions( array $options ) {
		$results = array();

		foreach ( $options as $option ) {
			$pair = explode( '=', $option, 2 );
			if ( count( $pair ) === 2 ) {
				$name = trim( $pair[0] );
				$value = trim( $pair[1] );
				$results[$name] = $value;
			}

			if ( count( $pair ) === 1 ) {
				$name = trim( $pair[0] );
				$results[$name] = true;
			}
		}
		return $results;
	}

}

?>