
var QSMAdmin;
(function ($) {

  QSMAdmin = {
    selectTab: function( tab ) {
      $( '.qsm-tab' ).removeClass( 'nav-tab-active' );
      $( '.qsm-tab-content' ).hide();
      tab.addClass( 'nav-tab-active' );
      tabID = tab.data( 'tab' );
      $( '.tab-' + tabID ).show();
    }
  };
  $(function() {
    $( '.qsm-tab' ).on( 'click', function( event ) {
      event.preventDefault();
      QSMAdmin.selectTab( $( this ) );
    });
  });
}(jQuery));

jQuery("#qmn_check_all").change( function() {
	jQuery('.qmn_delete_checkbox').prop( 'checked', jQuery('#qmn_check_all').prop('checked') );
});
