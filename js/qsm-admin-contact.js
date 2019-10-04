/**
 * QSM - Contact Form
 */

var QSMContact;
(function ($) {
  QSMContact = {
    load : function() {
        if($.isArray(qsmContactObject.contactForm) && qsmContactObject.contactForm.length > 0){
            $.each( qsmContactObject.contactForm, function( i, val ) {
              QSMContact.addField( val );
            });
        }
    },
    addField : function( fieldArray ) {
      var contactField = $( '<div class="contact-form-field new">' +
		  '<div class="contact-form-group">' +
		  	'<label class="contact-form-label">Field Type</label>' +
            '<select class="contact-form-control wide type-control">' +
              '<option value="none">Select a type...</option>' +
              '<option value="text">Small Open Answer</option>' +
              '<option value="email">Email</option>' +
              '<option value="checkbox">Checkbox</option>' +
            '</select>' +
          '</div>' +
          '<div class="contact-form-group">' +
            '<label class="contact-form-label">Label</label>' +
            '<input type="text" class="contact-form-control label-control" value="' + fieldArray.label + '">' +
          '</div>' +
          '<div class="contact-form-group">' +
            '<label class="contact-form-label">Used For</label>' +
            '<select class="contact-form-control use-control">' +
              '<option value="none"></option>' +
              '<option value="name">Name</option>' +
              '<option value="email">Email</option>' +
              '<option value="comp">Business</option>' +
              '<option value="phone">Phone</option>' +
            '</select>' +
          '</div>' +
          '<div class="contact-form-group">' +
            '<label class="contact-form-label">Required?</label>' +
            '<input type="checkbox" class="required-control">' +
          '</div>' +
          '<div class="contact-form-group">' +
            '<a href="#" class="delete-field">Delete</a> | ' +
            '<a href="#" class="copy-field">Duplicate</a>' +
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
        case 'email':
          contactField.find( '.type-control option[value="email"]').prop( 'selected', true );
          break;
        case 'checkbox':
          contactField.find( '.type-control option[value="checkbox"]').prop( 'selected', true );
          break;
        default:

      }
      switch ( fieldArray.use ) {
        case 'name':
          contactField.find( '.use-control option[value="name"]').prop( 'selected', true );
          break;
        case 'email':
          contactField.find( '.use-control option[value="email"]').prop( 'selected', true );
          break;
        case 'comp':
          contactField.find( '.use-control option[value="comp"]').prop( 'selected', true );
          break;
        case 'phone':
          contactField.find( '.use-control option[value="phone"]').prop( 'selected', true );
          break;
        default:

      }
      $( '.contact-form' ).append( contactField );
      setTimeout( QSMContact.removeNew, 250 );
    },
    removeNew: function() {
      $( '.contact-form-field' ).removeClass( 'new' );
    },
    duplicateField: function( linkClicked ) {
      var field = linkClicked.parents( '.contact-form-field' );
      var fieldArray = {
        label: field.find( '.label-control' ).val(),
        type: field.find( '.type-control' ).val(),
        required: field.find( '.required-control' ).prop( 'checked' ),
        use: field.find( '.use-control' ).val()
      };
      QSMContact.addField( fieldArray );
    },
    deleteField: function( field ) {
      var parent = field.parents( '.contact-form-field' );
      parent.addClass( 'deleting' );
      setTimeout( function() {
        parent.remove();
      }, 250 );
    },
    newField : function() {
      var fieldArray = {
        label : '',
        type : 'text',
        answers : [],
        required : false,
        use: ''
      };
      QSMContact.addField( fieldArray );
    },
    save : function() {
      QSMContact.displayAlert( 'Saving contact fields...', 'info' );
      var contactFields = $( '.contact-form-field' );
      var contactForm = [];
      var contactEach;
      $.each( contactFields, function( i, val ) {
        contactEach = {
          label: $( this ).find( '.label-control' ).val(),
          type: $( this ).find( '.type-control' ).val(),
          required: $( this ).find( '.required-control' ).prop( 'checked' ),
          use: $( this ).find( '.use-control' ).val()
        };
        contactForm.push( contactEach );
      });
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
      if ( response.status ) {
        QSMContact.displayAlert( '<strong>Success</strong> Your contact fields have been saved!', 'success' );
      } else {
        QSMContact.displayAlert( '<strong>Error</strong> There was an error encountered when saving your contact fields. Please try again.', 'error' );
      }
    },
    displayAlert: function( message, type ) {
      QSMContact.clearAlerts();
      $( '.contact-message' ).addClass( 'notice' );
      switch ( type ) {
        case 'info':
          $( '.contact-message' ).addClass( 'notice-info' );
          break;
        case 'error':
          $( '.contact-message' ).addClass( 'notice-error' );
          break;
        case 'success':
          $( '.contact-message' ).addClass( 'notice-success' );
          break;
        default:
      }
      $( '.contact-message' ).append( '<p>' + message + '</p>' );
    },
    clearAlerts: function() {
      $( '.contact-message' ).empty().removeClass().addClass( 'contact-message' );
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
      event.preventDefault();
      QSMContact.deleteField( $( this ) );
    });
    $( '.contact-form' ).on( 'click', '.copy-field', function( event ) {
      event.preventDefault();
      QSMContact.duplicateField( $( this ) );
    });
    $( '.contact-form' ).sortable({
      cursor: 'move',
	  opacity: 60,
	  placeholder: "ui-state-highlight"
    });
  });
}(jQuery));
