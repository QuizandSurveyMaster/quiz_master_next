/**
 * QSM Question Tab
 */

var QSMQuestion;
(function ($) {
	QSMQuestion = {
		addNewPage: function() {
			var template = _.template( jQuery( '#page-tmpl' ).html() );
			$( '.questions' ).append( template() );
			$( '.page' ).sortable({
				opacity: 70,
				cursor: 'move',
				placeholder: "ui-state-highlight",
				connectWith: '.page'
			});
			setTimeout( QSMQuestion.removeNew, 250 );
		},
		createNewQuestion: function( page ) {
			var template = _.template( jQuery( '#question-tmpl' ).html() );
			page.append( template( { type : 'Large Open Answer', category : 'Math', question: 'Some random question' } ) );
			setTimeout( QSMQuestion.removeNew, 250 );
		},
		editQuestion: function( $question ) {
			var template = _.template( jQuery( '#edit-question-tmpl' ).html() );
			$question.append( template() )

		},
		removeNew: function() {
			$( '.page-new' ).removeClass( 'page-new' );
			$( '.question-new' ).removeClass( 'question-new' );
		}
	}

	$(function() {
		$( '.new-page-button' ).on( 'click', function( event ) {
			event.preventDefault();
			QSMQuestion.addNewPage();
		});

		$( '.questions' ).on( 'click', '.new-question-button', function( event ) {
			event.preventDefault();
			QSMQuestion.createNewQuestion( $( this ).parent() );
		});

			$( '.questions' ).on( 'click', '.question', function( event ) {
			event.preventDefault();
			QSMQuestion.editQuestion( $( this ) );
		});

		$( '.questions' ).sortable({
			opacity: 70,
			cursor: 'move',
			placeholder: "ui-state-highlight"
		});
		$( '.page' ).sortable({
			opacity: 70,
			cursor: 'move',
			placeholder: "ui-state-highlight",
			connectWith: '.page'
		});
	});
}(jQuery));
