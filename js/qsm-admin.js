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
        'lastActivity': quizData.lastActivity
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
      $( '#delete_dialog' ).dialog( 'open' );
    },
    editQuizName: function( quiz_id ) {
      $( '#edit_quiz_id' ).val( quiz_id );
      $.each( qsmQuizObject, function( i, val ) {
        if ( val.id == quiz_id ) {
          $( '#edit_quiz_name' ).val( val.name );
        }
      });
      $( '#edit_dialog' ).dialog( 'open' );
    },
    duplicateQuiz: function( quiz_id ) {
      $( '#duplicate_quiz_id' ).val( quiz_id );
      $( '#duplicate_dialog' ).dialog( 'open' );
    }
  };
  $(function() {
    $( '#new_quiz_dialog' ).dialog({
      autoOpen: false,
      buttons: {
        Cancel: function() {
          $( this ).dialog( 'close' );
        }
      }
    });
    $( '#delete_dialog' ).dialog({
      autoOpen: false,
      buttons: {
        Cancel: function() {
          $( this ).dialog( 'close' );
        }
      }
    });
    $( '#edit_dialog' ).dialog({
      autoOpen: false,
      buttons: {
        Cancel: function() {
          $( this ).dialog( 'close' );
        }
      }
    });
    $( '#duplicate_dialog' ).dialog({
      autoOpen: false,
      buttons: {
        Cancel: function() {
          $( this ).dialog( 'close' );
        }
      }
    });
    $( '#new_quiz_button, #new_quiz_button_two' ).on( 'click', function( event ) {
      event.preventDefault();
      $( '#new_quiz_dialog' ).dialog( 'open' );
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
    QSMQuizzesSurveys.load();
  });
}(jQuery));
