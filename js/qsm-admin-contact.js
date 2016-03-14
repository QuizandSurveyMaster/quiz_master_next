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
    },
    newField : function() {
      var fieldArray = {
        label : 'Label',
        type : 'text',
        answers : [],
        required : false
      };
      QSMContact.addField( fieldArray );
    }
  };
  $(function() {
    QSMContact.load();
    $( '.add-contact-field' ).on( 'click', function() {
      QSMContact.newField();
    });
  });
}(jQuery));
