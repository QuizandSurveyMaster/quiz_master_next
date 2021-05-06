(function($){

    $( '#create-quiz-button' ).on( 'click', function( event ) {
        if( $('#new-quiz-form').find('.quiz_name').val() === ''){
            $('#new-quiz-form').find('.quiz_name').addClass('qsm-required');
            $('.qsm-wizard-wrap[data-show="quiz_settings"]').trigger('click');
            $('#new-quiz-form').find('.quiz_name').focus();
            return;
        }
        event.preventDefault();
        $( '#new-quiz-form' ).submit();
    });

    //Hide/show the wizard quiz options
    $(document).on('change', '#quiz_settings select', function(){
        var value = $(this).val();
        if( value == 0 ){
          jQuery(this).closest('.input-group').next('.input-group').show();
        } else {
          jQuery(this).closest('.input-group').next('.input-group').hide();
        }
    });

    //Show the menus on widget click
    $(document).on('click', '.qsm-new_menu_tab_items li', function(e){
        $('.qsm-new_menu_tab_items li').removeClass('active');
        $(this).addClass('active');
        $('.qsm-new-menu-elements').hide();
        var id = $(this).attr('data-show');
        $('#' + id).show();
        e.preventDefault();
    });

    $(document).on('click', '.qsm-wizard-wrap', function(e){
        $('.qsm-wizard-menu .qsm-wizard-wrap').removeClass('active');
        $(this).addClass('active');
        $('.qsm-new-menu-elements').hide();
        var id = $(this).attr('data-show');
        $('#' + id).fadeIn();
        switch (id) {
          case 'select_themes':
            $('#model-wizard .qsm-popup__footer #prev-theme-button').hide();
            $('#model-wizard .qsm-popup__footer #prev-quiz-button').hide();
            $('#model-wizard .qsm-popup__footer #next-quiz-button').show();
            $('#model-wizard .qsm-popup__footer #create-quiz-button').hide();
            $('#model-wizard .qsm-popup__footer #choose-addons-button').hide();
            break;
          case 'quiz_settings':
            $('#model-wizard .qsm-popup__footer #prev-theme-button').show();
            $('#model-wizard .qsm-popup__footer #prev-quiz-button').hide();
            $('#model-wizard .qsm-popup__footer #next-quiz-button').hide();
            $('#model-wizard .qsm-popup__footer #create-quiz-button').hide();
            $('#model-wizard .qsm-popup__footer #choose-addons-button').show();
            break;
          case 'addons_list':
            $('#model-wizard .qsm-popup__footer #prev-theme-button').hide();
            $('#model-wizard .qsm-popup__footer #prev-quiz-button').show();
            $('#model-wizard .qsm-popup__footer #next-quiz-button').hide();
            $('#model-wizard .qsm-popup__footer #create-quiz-button').show();
            $('#model-wizard .qsm-popup__footer #choose-addons-button').hide();
            break;
          default:
            $('#model-wizard .qsm-popup__footer #prev-theme-button').hide();
            $('#model-wizard .qsm-popup__footer #prev-quiz-button').hide();
            $('#model-wizard .qsm-popup__footer #next-quiz-button').show();
            $('#model-wizard .qsm-popup__footer #create-quiz-button').hide();
            $('#model-wizard .qsm-popup__footer #choose-addons-button').hide();
            break;
        }
        e.preventDefault();
    });
    $(document).on('click', '#model-wizard .qsm-popup__footer #prev-theme-button', function(e){
      $('.qsm-wizard-wrap[data-show="select_themes"]').trigger('click');
      e.preventDefault();
    });
    $(document).on('click', '#model-wizard .qsm-popup__footer #prev-quiz-button', function(e){
      $('.qsm-wizard-wrap[data-show="quiz_settings"]').trigger('click');
      e.preventDefault();
    });
    $(document).on('click', '#model-wizard .qsm-popup__footer #choose-addons-button', function(e){
      $('.qsm-wizard-wrap[data-show="addons_list"]').trigger('click');
      e.preventDefault();
    });
    $(document).on('click', '#model-wizard .qsm-popup__footer #next-quiz-button', function(e){
      $('.qsm-wizard-wrap[data-show="quiz_settings"]').trigger('click');
      e.preventDefault();
    });
    $(document).on('click', '.theme-sub-menu li', function(e){
        e.preventDefault();
        var id = $(this).children('a').attr('data-show');        
        $('.theme-sub-menu li').removeClass('active');        
        $(this).addClass('active');
        $('.theme-wrap').hide();        
        $('#' + id).show();
    });
    $(document).on('click', '#downloaded_theme .theme-wrapper', function(e){
        e.preventDefault();
        $('#downloaded_theme .theme-wrapper').removeClass('active');
        $('#downloaded_theme .theme-wrapper').find('.theme-name').stop().fadeTo('slow',0);
        // $('#downloaded_theme .theme-wrapper').find('.qsm-activate-theme').html('').html('Select Theme');
        $(this).find('input[name="quiz_theme_id"]').prop("checked", true);
        $(this).addClass('active');
        $(this).find('.theme-name').stop().fadeTo('slow',1);
        // $('#downloaded_theme .theme-wrapper.active').find('.qsm-activate-theme').html('').html('Selected Theme');
    });

    $(document).on('mouseover', '#downloaded_theme .theme-wrapper', function(e){
      e.preventDefault();
      if(!$(this).hasClass('active')){
        $(this).find('.theme-name').stop().fadeTo('slow',1);
      }
    });

    $(document).on('mouseout', '#downloaded_theme .theme-wrapper', function(e){
      e.preventDefault();
      if(!$(this).hasClass('active')){
        $(this).find('.theme-name').stop().fadeTo('slow',0);
      }
    });

    $(document).find('#select_themes .theme-actions').remove();

    $(document).find('#select_themes .qsm-opt-desc').remove();

}(jQuery));