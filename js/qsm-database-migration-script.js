var QSM_MIGRATION_PROCESS;

jQuery(document).ready(function ($) {
    QSM_MIGRATION_PROCESS = {
        processToMigrate: async function () {
            const response = await QSM_MIGRATION_PROCESS.ajaxRequest('qsm_start_migration', {});
            if (response.success) {
                QSM_MIGRATION_PROCESS.updateStatus(qsmMigrationData.successMessage, 'success');
            } else {
                QSM_MIGRATION_PROCESS.updateStatus(response.data.message || qsmMigrationData.errorMessage, 'error');
            }
        },

        ajaxRequest: function (action, data) {
            return jQuery.ajax({ // Added return statement
                url: qsmMigrationData.ajax_url,
                type: 'POST',
                data: {
                    action: action,
                    nonce: qsmMigrationData.nonce,
                    ...data
                }
            }).then(function (response) { // Use .then to return the response
                return response; // Ensure the response is returned
            });
        },
        updateStatus: function (message, type) {
            $('.qsm-migration-status').removeClass('success error progress').addClass(type).text(message).show();
        }
    };

    jQuery(document).on('submit', '#qsm-migration-form', async function (e) {
        e.preventDefault();
        
        // if (!confirm(qsmMigrationData.confirmMessage)) {
        //     return;
        // }
        
        const migrationButton = $('#qsm-start-migration');

        migrationButton.prop('disabled', true);
        QSM_MIGRATION_PROCESS.updateStatus(qsmMigrationData.startMessage, 'progress');
        await QSM_MIGRATION_PROCESS.processToMigrate();
        migrationButton.prop('disabled', false);
    });
});
