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
      
    }
  };
  $(function() {

  });
}(jQuery));
