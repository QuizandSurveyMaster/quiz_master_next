/**
 * QSM - Quizzes/Surveys Page
 */

var QSMQuizzesSurveys;
(function ($) {
  QSMQuizzesSurveys = {
    load: function() {
      $.each( qsmQuizObject, function( i, val ) {
        QSMQuizzesSurveys.addQuizRow( val );
      });
      $( '#the-list tr' ).filter( ':even' ).addClass( 'alternate' );
    },
    addQuizRow: function( quizData ) {
      var row = $( '<tr class="qsm-quiz-row" data-id="' + quizData.id + '">' +
        '<td class="post-title column-title"><span class="qsm-quiz-name">' + quizData.name + '</span> <a class="qsm-edit-name" href="#">Edit Name</a>' +
          '<div class="row-actions">' +
            '<a class="qsm-action-link" href="admin.php?page=mlw_quiz_options&&quiz_id=' + quizData.id + '">Edit</a> | ' +
            '<a class="qsm-action-link" href="admin.php?page=mlw_quiz_results&&quiz_id' + quizData.id + '">Results</a> | ' +
            '<a class="qsm-action-link qsm-action-link-duplicate" href="#">Duplicate</a> | ' +
            '<a class="qsm-action-link qsm-action-link-delete" href="#">Delete</a>' +
          '</div>' +
        '</td>' +
        '<td><a href="' + quizData.link + '">View Quiz</a>' +
          '<div class="row-actions">' +
            '<a class="qsm-action-link" href="post.php?post=' + quizData.postID + '&action=edit">Edit Post Settings</a>' +
          '</div>' +
        '</td>' +
        '<td>[qsm quiz=' + quizData.id + ']</td>' +
        '<td>[mlw_quizmaster_leaderboard mlw_quiz=' + quizData.id + ']</td>' +
        '<td>' + quizData.views + '</td>' +
        '<td>' + quizData.taken + '</td>' +
        '<td>' + quizData.lastActivity + '</td>' +
        '</tr>'
      );
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
