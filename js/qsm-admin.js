/**
 * QSM - Quizzes/Surveys Page
 */

var QSMQuizzesSurveys;
(function ($) {
	QSMQuizzesSurveys = {
		load: function() {
			if ( 0 !== qsmQuizObject.length ) {
				$.each( qsmQuizObject, function( i, val ) {
					QSMQuizzesSurveys.addQuizRow( val );
				});
				$( '#the-list tr' ).filter( ':even' ).addClass( 'alternate' );
			} else {
				var template = wp.template( 'no-quiz' );
				$( '.qsm-quizzes-page-content' ).hide();
				$( '#new_quiz_button' ).parent().after( template() );
			}
		},
    addQuizRow: function( quizData ) {
      var template = wp.template( 'quiz-row' );
      var values = {
        'id': quizData.id,
        'name': quizData.name,
        'link': quizData.link,
        'postID': quizData.postID,
        'views': quizData.views,
        'taken': quizData.taken,
        'lastActivity': quizData.lastActivity,
        'lastActivityDateTime': quizData.lastActivityDateTime,
        'post_status' : quizData.post_status != 'publish' ? '— ' + quizData.post_status : ''
      };
      var row = $( template( values ) );
      $( '#the-list' ).append( row );
    },
    searchQuizzes: function( query ) {
      $( ".qsm-quiz-row" ).each(function() {
        if ( -1 === $( this ).find( '.qsm-quiz-name' ).text().toLowerCase().indexOf( query.toLowerCase() ) ) {
          $( this ).hide();
        } else {
          $( this ).show();
        }
      });
    },
    deleteQuiz: function( quiz_id ) {
      $( '#delete_quiz_id' ).val( quiz_id );
      $.each( qsmQuizObject, function( i, val ) {
        if ( val.id == quiz_id ) {
          $( '#delete_quiz_name' ).val( val.name );
        }
      });
      MicroModal.show( 'modal-5' );
    },
    editQuizName: function( quiz_id ) {
      $( '#edit_quiz_id' ).val( quiz_id );
      $.each( qsmQuizObject, function( i, val ) {
        if ( val.id == quiz_id ) {
          $( '#edit_quiz_name' ).val( val.name );
        }
      });
      MicroModal.show( 'modal-3' );
    },
    duplicateQuiz: function( quiz_id ) {
      $( '#duplicate_quiz_id' ).val( quiz_id );
      MicroModal.show( 'modal-4' );
    },
    /**
     * Opens the popup to reset quiz stats
     *
     * @param int The ID of the quiz
     */
    openResetPopup: function( quiz_id ) {
      quiz_id = parseInt( quiz_id );
      $( '#reset_quiz_id' ).val( quiz_id );
      MicroModal.show( 'modal-1' );
    },
  };
  $(function() {
    $( '#new_quiz_button, #new_quiz_button_two' ).on( 'click', function( event ) {
      event.preventDefault();
      MicroModal.show( 'modal-2' );
    });
    $( '#show_import_export_popup' ).on( 'click', function( event ) {
        event.preventDefault();
        MicroModal.show( 'modal-export-import' );
    });
    $( '#quiz_search' ).keyup( function() {
      QSMQuizzesSurveys.searchQuizzes( $( this ).val() );
    });
    $( '#the-list' ).on( 'click', '.qsm-action-link-delete', function( event ) {
      event.preventDefault();
      QSMQuizzesSurveys.deleteQuiz( $( this ).parents( '.qsm-quiz-row' ).data( 'id' ) );
    });
    $( '#the-list' ).on( 'click', '.qsm-action-link-duplicate', function( event ) {
      event.preventDefault();
      QSMQuizzesSurveys.duplicateQuiz( $( this ).parents( '.qsm-quiz-row' ).data( 'id' ) );
    });
    $( '#the-list' ).on( 'click', '.qsm-edit-name', function( event ) {
      event.preventDefault();
      QSMQuizzesSurveys.editQuizName( $( this ).parents( '.qsm-quiz-row' ).data( 'id' ) );
    });
    $( '#the-list' ).on( 'click', '.qsm-action-link-reset', function( event ) {
      event.preventDefault();
      QSMQuizzesSurveys.openResetPopup( $( this ).parents( '.qsm-quiz-row' ).data( 'id' ) );
    });
    $( '#reset-stats-button' ).on( 'click', function( event ) {
      event.preventDefault();
      $( '#reset_quiz_form' ).submit();
    });
    $( '#create-quiz-button' ).on( 'click', function( event ) {
      event.preventDefault();
      $( '#new-quiz-form' ).submit();
    });
    $( '#duplicate-quiz-button' ).on( 'click', function( event ) {
      event.preventDefault();
      $( '#duplicate-quiz-form' ).submit();
    });
    $( '#delete-quiz-button' ).on( 'click', function( event ) {
      event.preventDefault();
      $( '#delete-quiz-form' ).submit();
    });
    QSMQuizzesSurveys.load();
    $(document).on('click','.sc-opener',function(){ 
        var $this = $(this);
        var shortcode_text = $this.next('.sc-content').text();
        $('#sc-shortcode-model-text').val(shortcode_text);
        MicroModal.show( 'modal-6' );        
    });
    $(document).on('click','#sc-copy-shortcode', function(){
        
        var copyText = document.getElementById("sc-shortcode-model-text");
        
        copyText.select();
        /* Copy the text inside the text field */
        document.execCommand("copy");
        
    });
  });
}(jQuery));
