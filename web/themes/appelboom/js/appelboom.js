/**
 * @file
 * Appelboom behaviors.
 */
(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.appelboom = {
    attach (context, settings) {

      $(document).on('click', '#hamburger', function(e){
      	e.preventDefault();
      	$('body').toggleClass('show-nav');
      });

      $('.homepage-header-image .field--name-field-featured-image:not(.excelatored)').each(function(){
      		excelator($(this));
  		});

    }
  };

  var excelator = function($el){
  	$el.addClass('excelatored');

  	// add rows with divs
  	let width = $el.width();
  	let height = $el.height();
  	let cellWidth = 100;
  	let cellHeight = 30;
  	let cellColumnCount = Math.floor(width / cellWidth);
  	let cellRowCount = Math.floor(height / cellHeight);
  	let Cells = $('<span class="cells" />');

  	for (let j = 0; j <= cellRowCount; j++){
	  	for (let i = 0; i <= cellColumnCount; i++) {
	  		Cells.append($('<span class="cell" />'));
	  	}
	}
  	// CSS: div on hover: change border-color
  	// CSS: idle animation??

  	Cells.appendTo($el);
  	$('body').on('mouseenter', '.cell', function(){
  		$(this).addClass('hover');
  	});
  	$('body').on('mouseleave', '.cell', function(){
  		$(this).removeClass('hover');
  	});
  }

}) (jQuery, Drupal, drupalSettings);