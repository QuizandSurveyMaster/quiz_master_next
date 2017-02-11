/**
 * QSM - Contact Form
 */

var QSMContact;
(function ($) {
  QSMContact = {
    load : function() {
      $.each( qsmContactObject.contactForm, function( i, val ) {
        QSMContact.addField( val );
      });
    },
    addField : function( fieldArray ) {
      var contactField = $( '<div class="contact-form-field">' +
          '<div class="contact-form-group">' +
            '<select class="contact-form-control wide type-control">' +
              '<option value="none">Select a type...</option>' +
              '<option value="text">Small Open Answer</option>' +
              '<option value="checkbox">Checkbox</option>' +
            '</select>' +
          '</div>' +
          '<div class="contact-form-group">' +
            '<label class="contact-form-label">Label</label>' +
            '<input type="text" class="contact-form-control label-control" value="' + fieldArray.label + '">' +
          '</div>' +
          '<div class="contact-form-group">' +
            '<label class="contact-form-label">Required?</label>' +
            '<input type="checkbox" class="required-control">' +
          '</div>' +
          '<div class="contact-form-group">' +
            '<a href="#" class="delete-field">Delete</a>' +
          '</div>' +
        '</div>'
      );
      if ( true === fieldArray.required || "true" === fieldArray.required ) {
        contactField.find( '.required-control' ).prop( 'checked', true );
      }
      switch ( fieldArray.type ) {
        case 'text':
          contactField.find( '.type-control option[value="text"]').prop( 'selected', true );
          break;
        case 'checkbox':
          contactField.find( '.type-control option[value="checkbox"]').prop( 'selected', true );
          break;
        default:

      }
      $( '.contact-form' ).append( contactField );
    },
    deleteField: function( field ) {
      console.log('inside function' );
      console.log(field);
      field.parents( '.contact-form-field' ).remove();
    },
    newField : function() {
      var fieldArray = {
        label : '',
        type : 'text',
        answers : [],
        required : false
      };
      QSMContact.addField( fieldArray );
    },
    save : function() {
      $( '.contact-message' ).empty();
      var contactFields = $( '.contact-form-field' );
      var contactForm = [];
      var contactEach;
      $.each( contactFields, function( i, val ) {
        contactEach = {
          label: $( this ).find( '.label-control' ).val(),
          type: $( this ).find( '.type-control' ).val(),
          required: $( this ).find( '.required-control' ).prop( 'checked' )
        };
        contactForm.push( contactEach );
      });
      console.log( contactForm );
      var data = {
    		action: 'qsm_save_contact',
    		contact_form: contactForm,
        quiz_id : qsmContactObject.quizID
    	};

    	jQuery.post( ajaxurl, data, function( response ) {
    		QSMContact.saved( JSON.parse( response ) );
    	});
    },
    saved : function( response ) {
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
    $( '.save-contact' ).on( 'click', function() {
      QSMContact.save();
    });
    $( '.contact-form' ).on( 'click', '.delete-field', function( event ) {
      console.log( 'clicked' );
      event.preventDefault();
      QSMContact.deleteField( $( this ) );
    })
    $( '.contact-form' ).sortable({
      containment: "parent",
      cursor: 'move',
      opacity: 0.6
    });
  });
}(jQuery));
