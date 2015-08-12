function qmn_select_tab(tab, content) {
  jQuery("a.nav-tab-active").toggleClass("nav-tab-active");
  jQuery(".qmn_tab").hide();
  jQuery("#"+content).show();
  jQuery("#mlw_qmn_tab_"+tab).toggleClass("nav-tab-active");
}

jQuery("#qmn_check_all").change( function() {
	jQuery('.qmn_delete_checkbox').prop( 'checked', jQuery('#qmn_check_all').prop('checked') );
});
