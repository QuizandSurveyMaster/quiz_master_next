function mlw_qmn_setTab(tab) {
  jQuery("a.nav-tab-active").toggleClass("nav-tab-active");
  if (tab == 1)
  {
    jQuery("#mlw_quiz_what_new").show();
    jQuery("#mlw_quiz_changelog").hide();
    jQuery("#mlw_quiz_requested").hide();
    jQuery("#mlw_qmn_tab_1").toggleClass("nav-tab-active");
  }
  if (tab == 2)
  {
    jQuery("#mlw_quiz_what_new").hide();
    jQuery("#mlw_quiz_changelog").show();
    jQuery("#mlw_quiz_requested").hide();
    jQuery("#mlw_qmn_tab_2").toggleClass("nav-tab-active");
  }
}
