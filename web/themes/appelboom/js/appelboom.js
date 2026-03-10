/**
 * @file
 * Appelboom behaviors.
 */
(function ($, Drupal, drupalSettings) {

	'use strict';

	Drupal.behaviors.appelboom = {
		attach (context, settings) {

			const hamburger = once('navProcessed', '#hamburger', context);
			hamburger.forEach(function(el){

				$(el).on('click', function(e){
					e.preventDefault();
					$('body').toggleClass('show-nav');
				});
			});

			$('.homepage-header-image .field--name-field-featured-image:not(.excelatored)').each(function(){
				excelator($(this));
			});
			$('#block-appelboom-featuredimageforappelboom .field--name-field-featured-image:not(.excelatored)').each(function(){
				excelator($(this));
			});

			const headerOnce = once('headerProcessed', '#page-header', context);
			headerOnce.forEach(function(el){
				$(window).on('scroll', function(e){
					if($(window).scrollTop() > $('#page-header').height()){
						$('#page-header').addClass('minify');
					}else{
						$('#page-header').removeClass('minify');
					}
				})
			});

			const solliciterenLink = once('solliciterenProcessed', 'p.solliciteren',context );
			solliciterenLink.forEach((el) =>{
				const title = $('h1').first().text();
				let link = $('<a href="/form/open-sollicitatie?vacature='+title+'" class="webform-dialog webform-dialog-wide button">Direct solliciteren</a>');
				$(el).html(link);
			});

		}
	};

	var excelator = function($el){
		$el.addClass('excelatored');

		// add rows with divs
		let width = $el.width();
		let height = $el.height();
		let cellWidth = 80;
		let cellHeight = 24;
		let cellColumnCount = Math.floor(width / (cellWidth - 1));
		let cellRowCount = Math.floor(height / (cellHeight) );
		let Cells = $('<span class="cells" />');

		for (let j = 0; j <= cellRowCount; j++){
		  	for (let i = 0; i <= cellColumnCount; i++) {
		  		Cells.append($('<span class="cell" />'));
		  	}
		}

		Cells.appendTo($el);
	}

}) (jQuery, Drupal, drupalSettings);