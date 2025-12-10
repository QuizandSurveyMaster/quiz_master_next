var QSM_Migration_Process;
jQuery(function ($) {

    // All state, utilities and logic live inside this object
    QSM_Migration_Process = {
        migrationForm: $('#qsm-migration-form'),
        startButton: $('#qsm-start-migration'),
        statusBox: $('.qsm-migration-status'),
        detailsBox: $('.qsm-migration-details'),
        progressBar: $('.qsm-migration-progress'),
        totalRecords: 0,
        batchSize: 0,
        offset: 0, // This will track the total number of LOGGED records (migrated + failed)
        processingFailedOnly: false,

        // UI helpers
        updateProgress: function (processed) {
            if (QSM_Migration_Process.totalRecords === 0) return;
            const percent = Math.min(100, (processed / QSM_Migration_Process.totalRecords) * 100);
            QSM_Migration_Process.progressBar.css('width', percent + '%');
        },

        updateStatus: function (message) {
            QSM_Migration_Process.statusBox.html(message);
        },

        updateDetails: function (html) {
            QSM_Migration_Process.detailsBox.html(html);
        },

        disableUI: function () {
            QSM_Migration_Process.startButton.prop('disabled', true);
            QSM_Migration_Process.migrationForm.addClass('qsm-migration-disabled');
        },

        enableUI: function () {
            QSM_Migration_Process.startButton.prop('disabled', false);
            QSM_Migration_Process.migrationForm.removeClass('qsm-migration-disabled');
            // If completed, we can show a re-try button for failed if needed, but for now just leave it enabled
            // and relying on the PHP check for completion.
        },

        // Step 1: Start migration (initialize tables + get totals)
        start: function () {
            QSM_Migration_Process.disableUI();
            QSM_Migration_Process.updateStatus(qsmMigrationData.startMessage);
            QSM_Migration_Process.updateDetails('');

            // Reset state
            QSM_Migration_Process.offset = 0;
            QSM_Migration_Process.processingFailedOnly = false;

            $.ajax({
                type: 'POST',
                url: qsmMigrationData.ajax_url,
                data: {
                    action: 'qsm_start_migration',
                    nonce: qsmMigrationData.nonce
                },
                success: function (response) {
                    if (!response.success) {
                        QSM_Migration_Process.enableUI();
                        QSM_Migration_Process.updateStatus(qsmMigrationData.errorMessage);
                        console.error(response.data);
                        return;
                    }

                    const data = response.data;

                    QSM_Migration_Process.totalRecords = data.total_records;
                    QSM_Migration_Process.batchSize = data.batch_size;
                    QSM_Migration_Process.offset = data.processed_count; // Start from already processed count

                    if (data.already_done) {
                        QSM_Migration_Process.updateStatus(data.message);
                        QSM_Migration_Process.progressBar.css('width', '100%');
                        QSM_Migration_Process.enableUI();
                        return;
                    }

                    QSM_Migration_Process.updateProgress(QSM_Migration_Process.offset);
                    QSM_Migration_Process.updateStatus(qsmMigrationData.processingMessage);

                    QSM_Migration_Process.updateDetails(`
                        <div>${qsmMigrationData.labelTotalRecords} <strong>${QSM_Migration_Process.totalRecords}</strong></div>
                        <div>${qsmMigrationData.labelProcessed} <strong>${QSM_Migration_Process.offset}</strong></div>
                    `);

                    // Start batch processing
                    QSM_Migration_Process.processBatch();
                },
                error: function (xhr) {
                    QSM_Migration_Process.enableUI();
                    QSM_Migration_Process.updateStatus(qsmMigrationData.errorMessage);
                    console.error(xhr.responseText);
                }
            });
        },

        // Step 2: Process batches recursively
        processBatch: function () {
            const self = this;

            // Stop condition check for client side
            if (self.offset >= self.totalRecords && !self.processingFailedOnly) {
                // If we've processed everything in normal mode, switch to failed-only mode for final pass
                self.processingFailedOnly = true;
                QSM_Migration_Process.updateStatus(qsmMigrationData.processingMessage);
            }
            
            // If we are in failed-only mode and total_records is 0, we are done (but the PHP handles this better)
            if (self.totalRecords === 0) {
                 QSM_Migration_Process.updateStatus(qsmMigrationData.successMessage);
                 QSM_Migration_Process.progressBar.css('width', '100%');
                 QSM_Migration_Process.enableUI();
                 return;
            }

            $.ajax({
                type: 'POST',
                url: qsmMigrationData.ajax_url,
                data: {
                    action: 'qsm_process_migration_batch',
                    nonce: qsmMigrationData.nonce,
                    current_processed_count: self.offset, // Send current count for status calculation
                    process_failed_only: self.processingFailedOnly ? 1 : 0
                },
                success: function (response) {
                    if (!response.success) {
                        QSM_Migration_Process.enableUI();
                        QSM_Migration_Process.updateStatus(qsmMigrationData.errorMessage);
                        console.error(response.data);
                        return;
                    }

                    const data = response.data;
                    
                    // Update the running total of LOGGED records (migrated + failed)
                    QSM_Migration_Process.offset = data.next_offset; 

                    // Update detailed information
                    QSM_Migration_Process.updateDetails(`
                        <div>${qsmMigrationData.labelTotalRecords} <strong>${QSM_Migration_Process.totalRecords}</strong></div>
                        <div>${qsmMigrationData.labelProcessed} <strong>${QSM_Migration_Process.offset}</strong></div>
                        <div>${qsmMigrationData.labelInserted} <strong>${data.migrated_results}</strong></div>
                        <div>${qsmMigrationData.labelFailed} <strong>${data.failed_results}</strong></div>
                    `);

                    // Update progress bar
                    QSM_Migration_Process.updateProgress(QSM_Migration_Process.offset);

                    // Case 1: All done (final completion)
                    if (data.completed === true) {
                        QSM_Migration_Process.updateStatus(qsmMigrationData.successMessage);
                        QSM_Migration_Process.progressBar.css('width', '100%');
                        QSM_Migration_Process.enableUI();
                        return;
                    }
                    
                    // Case 2: No records were processed in this batch (end of normal/failed-only pass)
                    if (data.results_processed === 0) {
                        if (!self.processingFailedOnly) {
                             // Switch to a final pass that focuses only on failed IDs.
                            self.processingFailedOnly = true;
                            QSM_Migration_Process.updateStatus(qsmMigrationData.processingMessage + ' Finalizing migration, retrying failed results...');
                            QSM_Migration_Process.processBatch();
                            return;
                        } else {
                            // In failed-only mode, 0 processed means no failed records left to retry.
                            QSM_Migration_Process.updateStatus(qsmMigrationData.successMessage);
                            QSM_Migration_Process.progressBar.css('width', '100%');
                            QSM_Migration_Process.enableUI();
                            return;
                        }
                    }

                    // Case 3: Recursively process next batch
                    QSM_Migration_Process.processBatch();
                },
                error: function (xhr) {
                    QSM_Migration_Process.enableUI();
                    QSM_Migration_Process.updateStatus(qsmMigrationData.errorMessage);
                    QSM_Migration_Process.detailsBox.append(`<div>${qsmMigrationData.labelErrorNote}</div>`);
                    console.error(xhr.responseText);
                }
            });
        }
    };

    // Bind form submission to the object API
    QSM_Migration_Process.migrationForm.on('submit', function (e) {
        e.preventDefault();

        if (!confirm(qsmMigrationData.confirmMessage)) return;

        QSM_Migration_Process.start();
    });

});