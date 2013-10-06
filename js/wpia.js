jQuery(document).ready(function($){
	
	var $body = $('body'),
		$wpiaToggleButton = $('#wp-admin-bar-wpia-toggle-edit-mode a'),
		$infobar = $('.wpia-info-bar'),
		$editableItems = $('.wpia-is-editable');

	function wpiaToggleEditMode() {
			if( $body.hasClass('wpia-toggled') ) {
				$editableItems.each( function() {
					$(this)
					.wrap('<a class="wpia-edit-link" href="' + $(this).data('wpia-edit-href') + '">')
					.tooltip({
						items: '[data-wpia-edit-tooltip]',
						content: function() {
							return $(this).data('wpia-edit-tooltip');
						},
						tooltipClass: 'wpia-tooltip',
						track: true
					});
				});
			} else {
				$editableItems.each( function() {
					$(this).tooltip('destroy').unwrap();
				});
			}
		}

	// Trigger things when the Edit Mode is clicked
	$wpiaToggleButton.on('click',function(e){

		e.stopPropagation();
		e.preventDefault();

		$body.toggleClass('wpia-toggled');
		$infobar.slideToggle();
		wpiaToggleEditMode();

	});

	// init tooltips
	$('dt,dd', $infobar).tooltip({
		items: '[data-wpia-tooltip]',
		content: function() {
			return $(this).data('wpia-tooltip');
		},
		tooltipClass: 'wpia-tooltip',
		position: {
			my: 'left top',
			at: 'right+10 bottom+10'
		}
	});

});