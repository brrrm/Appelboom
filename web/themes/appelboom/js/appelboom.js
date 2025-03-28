/**
 * @file
 * Appelboom behaviors.
 */
(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.appelboom = {
    attach (context, settings) {

      console.log('It works!');
      $(document).on('click', '#hamburger', function(e){
      	e.preventDefault();
      	$('body').toggleClass('show-nav');
      })

    }
  };

}) (jQuery, Drupal, drupalSettings);