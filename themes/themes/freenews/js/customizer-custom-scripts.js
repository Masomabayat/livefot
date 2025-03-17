( function( api ) {

	// Extends our custom "freenews" section.
	api.sectionConstructor['freenews'] = api.Section.extend( {

		// No freenews for this type of section.
		attachEuphoric: function () {},

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );

} )( wp.customize );
