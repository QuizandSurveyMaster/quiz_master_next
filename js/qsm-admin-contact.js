/**
 * QSM - Contact Form
 */

var QSMContact;
(function ($) {
  QSMContact = {
    contactForm : qsmContactObject.contactForm,
    load : function() {
      $.each( QSMContact.contactForm, function( i, val ) {
        QSMContact.addField( val );
      });
    },
    addField : function( fieldArray ) {
      var required = '';
      if ( fieldArray.required ) {
        required = '*';
      }
      $( '.contact_form' ).append(
        '<div class="contact-form-row">' +
          '<div class="contact-form-group">' +
            '<label>' +
              fieldArray.label +
              required +
            '</label>' +
            '<div class="contact-form-group-field">' +
              '<input type="text">' +
            '</div>' +
          '</div>' +
        '</div>'
      );
      QSMContact.contactForm.push( fieldArray );
    },
    newField : function() {
      var fieldArray = {
        label : 'Label',
        type : 'text',
        answers : [],
        required : false
      };
      QSMContact.addField( fieldArray );
    },
    save : function() {
      $( '.contact-message' ).empty();
      var data = {
    		action: 'qsm_save_contact',
    		contact_form: QSMContact.contactForm,
        quiz_id : qsmContactObject.quizID
    	};

    	jQuery.post( ajaxurl, data, function( response ) {
    		QSMContact.saved( JSON.parse( response ) );
    	});
    },
    saved : function() {
      $( '.contact-message' ).removeClass( 'updated' ).removeClass( 'error' );
      if ( response.status ) {
        $( '.contact-message' ).addClass( 'updated' );
        $( '.contact-message' ).append( '<p><strong>Success</strong> Your rules have been saved!</p>' );
      } else {
        $( '.contact-message' ).addClass( 'error' );
        $( '.contact-message' ).append( '<p><strong>Error</strong> There was an error encountered when saving your rules. Please try again.</p>' );
      }
    }
  };
  $(function() {
    QSMContact.load();
    $( '.add-contact-field' ).on( 'click', function() {
      QSMContact.newField();
    });
  });
}(jQuery));
