(function($){

// http://stackoverflow.com/questions/920236/how-can-i-detect-if-a-selector-returns-null
$.fn.exists = function () {
    return this.length !== 0;
}

function wpiaToggleEditMode() {
	$editableItems = $('.wpia-is-editable');
	if( $('body').hasClass('wpia-toggled') ) {
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

// function for expanding targeted widgets in the admin
// differs from generic target script to handle
function expandTargetedWidget() {
	target = window.location.hash;

	if( ! target )
		return;

	//strip "#"
	target = target.replace(/^.*#wpia-/, '');

	$targetedWidget = $('.widget[id$=' + target + ']');

	// Stop if nothing's targeted
	if( !$targetedWidget.exists() )
		return;

	$targetedSidebar = $targetedWidget.parents('.widgets-holder-wrap');

	// Expand sidebar of targeted widget if closed
	if( $targetedSidebar.hasClass('closed') ) {
		// expand sidebar
		$('.sidebar-name-arrow', $targetedSidebar).trigger('click');
	}

	// scroll to the widget
    $('html, body').animate({
        scrollTop: $targetedWidget.offset().top - 50 // -50 accounts for admin bar
    }, 750);

    // expand widget
	$('.widget-action', $targetedWidget).trigger('click');
	// add class to mark as targeted
	$targetedWidget.addClass('wpia-active-widget');
}

function wpiaTargetToggle() {
	// get hash
	var hash = window.location.hash;

	// we're done if there's nothing left to do
	if( hash.search('wpia') == -1 )
		return;

	// strip "wpia-" from has
	id = hash.replace(/^.*#wpia-/, '');

	var $targetedElement = $('#' + id);

	console.log($targetedElement);

	// stop if nothing's targeted
	if( !$targetedElement.exists() )
		return;

	$targetedElement.addClass('wpia-targeted');

	// scroll to the widget
    $('html, body').animate({
        scrollTop: $targetedElement.offset().top - 50 // -50 accounts for admin bar
    }, 750);
}

// on ready
$(document).ready(function (){
	
	var $body = $('body'),
		$wpiaToggleButton = $('#wp-admin-bar-wpia-toggle-edit-mode a'),
		$infobar = $('.wpia-info-bar');

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

// on load
$(window).load(function() {

	if( $('body').hasClass('widgets-php') ) {
		expandTargetedWidget();
	}

	wpiaTargetToggle();

});


})(window.jQuery);