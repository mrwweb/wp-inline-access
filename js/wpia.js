jQuery(document).ready(function($){
	
	var $body = $('body'),
		$wpiaToggleButton = $('#wp-admin-bar-wpia-toggle-edit-mode a'),
		$infobar = $('.wpia-info-bar');

	// Trigger things when the Edit Mode is clicked
	$wpiaToggleButton.on('click',function(e){

		e.stopPropagation();
		e.preventDefault();

		$body.toggleClass('wpia-toggled');
		$infobar.slideToggle();

	});

	// init tooltips
	$('dt', $infobar).tooltip({
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