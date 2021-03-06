(function( $ ) {

	$(document).ready(function(){

		destroyModal = function(){
			$('body').removeClass('lasso-modal-open');
			$('#lasso--tour__modal,#lasso--tour__modal ~ #lasso--modal__overlay').remove();
		}

		//$('#lasso--tour__modal input[type="submit"]').live('click', function(e) {
		jQuery(document).on('click', '#lasso--tour__modal input[type="submit"]', function(e){

			e.preventDefault();

			var target = $(this);

			if ( !$('#hide_tour').is(':checked') ) {

				destroyModal()

			} else {
				if (lasso_editor.saveusingrest) {
					var data = {
						action: 		'process_tour_hide',
						nonce: 			$(this).data('nonce')
					}

					$.post( lasso_editor.ajaxurl, data, function(response) {

						if ( true == response.success ) {

							destroyModal();

						}

					}).fail(function(xhr, err) { 
						var responseTitle= $(xhr.responseText).filter('title').get(0);
						alert($(responseTitle).text() + "\n" + EditusFormatAJAXErrorMessage(xhr, err) );
					});
				} else {
					var data = {
						action: 		'process_tour_hide',
						nonce: 			$(this).data('nonce')
					}

					$.post( lasso_editor.ajaxurl, data, function(response) {

						if ( true == response.success ) {

							destroyModal();

						}

					}).fail(function(xhr, err) { 
						var responseTitle= $(xhr.responseText).filter('title').get(0);
						alert($(responseTitle).text() + "\n" + EditusFormatAJAXErrorMessage(xhr, err) );
					});				
				}
			}

		});

	});

})( jQuery );

(function( $ ) {
	jQuery(document).ready(function($){
		if ( $( "#lasso--tour__slides" ).length ) {

			$('body').addClass('lasso-modal-open');

			$('.lasso--loading').remove();
			$('#lasso--tour__slides').hide().fadeIn()

			$('#lasso--tour__slides').unslider({
				dots: true,
				delay:7000
			});
		}
	});
})( jQuery );
