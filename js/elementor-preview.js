"use strict";
var qsm_init_once = false;

jQuery(window).on('elementor/frontend/init', function() {
    elementorFrontend.hooks.addAction('frontend/element_ready/shortcode.default', function($scope, $){
        if (!qsm_init_once) {
            console.log('Initializing QSM quizzes');
            qmnDoInit(); 
            qsm_init_once = true; // mark as initialized
        }
    });
});
