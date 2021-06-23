jQuery(document).ready(function () {
    jQuery(document).on('click', '.qsm-customize-color-settings', function (e) {
        e.preventDefault();
        MicroModal.show('qsm-theme-color-settings');        
        if( jQuery('.my-color-field').length > 0 ){
            jQuery('.my-color-field').wpColorPicker();
        }
    });
    jQuery(document).on('click', '#qsm-save-theme-settings', function(e){
        e.preventDefault();
        jQuery('.qsm-theme-settings-frm').submit();
    });
});