wp.domReady(() => {
	/* Hide profile type taxonomy field - managed via ACF */
	wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'taxonomy-panel-profile-type' );
	wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'taxonomy-panel-themes' );
	wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'taxonomy-panel-topic' );
	wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'taxonomy-panel-update-type' );
	wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'taxonomy-panel-library-type' );
	wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'taxonomy-panel-event-type' );
	wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'taxonomy-panel-event-category' );
	wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'taxonomy-panel-location' );
});
