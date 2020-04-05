( function( d, $, mw ) {
	$( '.sidebar-toggle' ).on( 'click', function( e ){
		e.preventDefault();

		var target =  $( this ).attr( 'data-toggle' );

		if( $( 'body' ).hasClass( target ) ){
			$( 'body' ).removeClass( target );
			mw.cookie.set( 'Calumma_'+target, 'false' );
		}
		else{
			$( 'body' ).addClass( target );
			if ( $( this ).closest( 'aside' ).hasClass( 'active' ) ) {
				$( this ).closest( 'aside' ).removeClass( 'active' );
			}
			mw.cookie.set( 'Calumma_'+target, 'true' );
		}
	});

	$( '.bs-tabs' ).on( 'click', '.active a[data-toggle="tab"]', function( e ){
		if( ( $( d ).width() <= 1000 ) ) {
			var $anchor = $( this );
			var targetId = $anchor.attr( 'href' ).substring( 1 ); //cut off leading '#'
			var $tabsContainer = $anchor.closest( '.bs-tabs' );
			var tabsContainerId = $tabsContainer.attr( 'id' );

			var asideTrigger = $anchor.closest( 'aside' ).attr( 'data-toggle-by' );

			var activeTab = mw.cookie.get( 'CalummaTab_'+tabsContainerId );
			if ( ( activeTab === targetId ) || !activeTab ){
				$( 'body' ).addClass( asideTrigger );
				mw.cookie.set( 'Calumma_' + asideTrigger, 'true' );

				/*close graphicallist*/
				var $graphicalListTrigger = $anchor.closest( 'aside' ).find( '.dynamic-graphical-list-visible a.title' );
				$graphicalListTrigger.trigger( 'click' );
			}
		}
	});

	$( d ).ready( function(e){
		if( ( $( d ).width() <= 1000 ) ) {
			$( 'body' ).addClass( 'navigation-main-collapse' );
			$( 'body' ).addClass( 'sitetools-main-collapse' );

			mw.cookie.set( 'Calumma_navigation-main-collapse', 'true' );
			mw.cookie.set( 'Calumma_sitetools-main-collapse', 'true' );

			mw.cookie.set( 'Calumma_desktop-view', 'false' );
		}
		else {
			mw.cookie.set( 'Calumma_desktop-view', 'true' );
		}
	});

	$( d ).ready( function(e){
		if( $( d ).width() > 721 ) {
			if ( $( 'aside.navigation-main.active' ).length > 0 ) {
				$( 'body.navigation-main-collapse' ).removeClass( 'navigation-main-collapse' );
			}

			if ( $( 'aside.sitetools-main.active' ).length > 0 ) {
				$( 'body.sitetools-main-collapse' ).removeClass( 'sitetools-main-collapse' );
			}
		}
	});
})( document, jQuery, mediaWiki );

