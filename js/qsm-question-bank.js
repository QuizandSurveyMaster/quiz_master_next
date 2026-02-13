(function ($) {
	'use strict';

	const pageData = window.qsmQuestionBankData || {};

	const QuestionBankPage = {
		state: {
			page: 1,
			totalPages: 1,
			search: '',
			quiz: '',
			category: '',
			type: '',
		},

		prepareQuizNonces() {
			if (!pageData.quizNonces || 'object' !== typeof pageData.quizNonces) {
				return new Map();
			}
			const entries = Object.entries(pageData.quizNonces).map(([key, value]) => [parseInt(key, 10), value]);
			return new Map(entries);
		},
		editorReady: false,
		saveSuccessPatched: false,
		modelRequests: {},
		isCreating: false,
		bulkState: {
			file: null,
			isOpen: false,
			isUploading: false,
		},

		init() {
			this.$form = $('#qsm-question-bank-filters');
			if (!this.$form.length || !pageData.restUrl) {
				return;
			}

			this.template = this.getQuestionTemplate();
			if (!this.template) {
				console.warn('[QSM Question Bank] Missing wp.template("question") template.');
				return;
			}

			this.$search = $('#qsm-question-bank-search');
			this.$quiz = $('#qsm-question-bank-quiz');
			this.$category = $('#qsm-question-bank-category');
			this.$type = $('#qsm-question-bank-type');
			this.$list = $('#qsm-question-bank-list');
			this.$emptyNotice = $('#qsm-question-bank-empty');
			this.$loader = $('#qsm-question-bank-loader');
			this.$reset = $('#qsm-question-bank-reset');
			this.$pagination = $('#qsm-question-bank-pagination');
			this.$prev = $('#qsm-question-bank-prev');
			this.$next = $('#qsm-question-bank-next');
			this.$pageButtons = $('#qsm-question-bank-page-buttons');
			this.$pageInfo = $('#qsm-question-bank-page-info');
			this.$createButton = $('#qsm-question-bank-create');
			this.$bulkImportButton = $('#qsm-bulk-question-import');
			this.$bulkModal = $('#qsm-bulk-upload-modal');
			this.$bulkForm = $('#qsm-bulk-upload-form');
			this.$bulkFileInput = $('#qsm-bulk-upload-file');
			this.$bulkDropzone = $('#qsm-bulk-upload-dropzone');
			this.$bulkFileLabel = $('#qsm-bulk-upload-file-label');
			this.$bulkStatus = $('#qsm-bulk-upload-status');
			this.$bulkSummary = $('#qsm-bulk-upload-summary');
			this.$bulkCancel = $('#qsm-bulk-upload-cancel');
			this.$bulkSubmit = $('#qsm-bulk-upload-submit');
			this.$bulkBrowse = this.$bulkModal.find('.qsm-bulk-upload-browse');
			this.bulkConfig = pageData?.bulkUpload || {};

			this.questionTypeMap = this.prepareQuestionTypeMap();
			this.quizNonces = this.prepareQuizNonces();
			this.prepareSortableFallback();
			this.initBulkUpload();

			this.bindEvents();
			this.initEditorBridge();
			this.fetchQuestions({ resetPage: true });
		},

		bindEvents() {
			this.$form.on('submit', (event) => {
				event.preventDefault();
				this.state.search = this.$search.val();
				this.state.quiz = this.$quiz.val();
				this.state.category = this.$category.val();
				this.state.type = this.$type.val();
				this.fetchQuestions({ resetPage: true });
			});

			this.$reset.on('click', (event) => {
				event.preventDefault();
				this.$search.val('');
				this.$quiz.val('');
				this.$category.val('');
				this.$type.val('');
				this.state = {
					page: 1,
					totalPages: 1,
					search: '',
					quiz: '',
					category: '',
					type: '',
				};
				this.fetchQuestions({ resetPage: true });
			});

			this.$prev.on('click', (event) => {
				event.preventDefault();
				if (this.state.page > 1) {
					this.state.page -= 1;
					this.fetchQuestions();
				}
			});

			this.$next.on('click', (event) => {
				event.preventDefault();
				if (this.state.page < this.state.totalPages) {
					this.state.page += 1;
					this.fetchQuestions();
				}
			});

			this.$pageButtons.on('click', '.qsm-question-bank-page-btn', (event) => {
				event.preventDefault();
				const targetPage = parseInt($(event.currentTarget).data('page'), 10);
				if (!Number.isNaN(targetPage) && targetPage !== this.state.page) {
					this.state.page = targetPage;
					this.fetchQuestions();
				}
			});

			this.$list.on('click', '.edit-question-button', (event) => {
				this.handleEditClick(event, false);
				if ( this.bulkState.isOpen ) {
					this.toggleBulkPanel(false);
				}
			});

			this.$createButton.on('click', (event) => {
				event.preventDefault();
				this.handleCreateQuestion(true);
                QSMAdmin.displayAlert(qsm_admin_messages.creating_question, 'info');
				if ( this.bulkState.isOpen ) {
					this.toggleBulkPanel(false);
				}
			});
		},

		getQuestionTemplate() {
			if ('undefined' === typeof wp || 'function' !== typeof wp.template) {
				return null;
			}
			try {
				return wp.template('question');
			} catch (error) {
				console.error('[QSM Question Bank] Unable to load question template', error);
				return null;
			}
		},

		prepareQuestionTypeMap() {
			const map = {};
			if (Array.isArray(pageData.questionTypes)) {
				pageData.questionTypes.forEach((type) => {
					if (type.slug) {
						map[type.slug] = type.name || type.slug;
					}
				});
			}
			return map;
		},

		prepareSortableFallback() {
			if (!this.$list.length || 'function' !== typeof this.$list.sortable) {
				return;
			}
			if (!this.$list.hasClass('ui-sortable')) {
				this.$list.sortable({
					items: '.question',
					handle: '.dashicons-move',
					placeholder: 'qsm-question-bank-sortable-placeholder',
				});
			}
			this.$list.sortable('disable');
		},

		updateRestNonceForQuiz(quizId) {
			if ('undefined' === typeof window.qsmQuestionSettings) {
				return;
			}
			const parsedId = Number.isNaN(parseInt(quizId, 10)) ? 0 : parseInt(quizId, 10);
			const nonce = this.quizNonces.get(parsedId) || this.quizNonces.get(0);
			window.qsmQuestionSettings.quizID = parsedId;
			if ('object' === typeof window.qsmTextTabObject) {
				window.qsmTextTabObject.quiz_id = parsedId;
			}
			if (nonce) {
				window.qsmQuestionSettings.rest_user_nonce = nonce;
			}
		},

		initEditorBridge() {
			let attempts = 0;
			const maxAttempts = 40;
			const attemptBootstrap = () => {
				attempts += 1;
				if (window.QSMQuestion && window.qsmQuestionSettings) {
					this.setupEditorBridge();
					return;
				}
				if (attempts >= maxAttempts) {
					console.warn('[QSM Question Bank] Editor bridge unavailable.');
					return;
				}
				setTimeout(attemptBootstrap, 250);
			};
			attemptBootstrap();
		},

		setupEditorBridge() {
			if (this.editorReady) {
				return;
			}
			if (!QSMQuestion.questionCollection && window.Backbone && QSMQuestion.question) {
				QSMQuestion.questionCollection = Backbone.Collection.extend({
					url: (window.wpApiSettings?.root || '') + 'quiz-survey-master/v1/questions',
					model: QSMQuestion.question,
				});
			}
			if (!QSMQuestion.questions && QSMQuestion.questionCollection) {
				QSMQuestion.questions = new QSMQuestion.questionCollection();
			}
			this.patchSaveSuccess();
			this.editorReady = true;
		},

		patchSaveSuccess() {
			if (this.saveSuccessPatched || !window.QSMQuestion || 'function' !== typeof QSMQuestion.saveSuccess) {
				return;
			}
			const original = QSMQuestion.saveSuccess;
			QSMQuestion.saveSuccess = (...args) => {
				const result = original.apply(QSMQuestion, args);
				const model = args[0];
				if (model) {
					this.decorateUpdatedCard(model);
				}
				return result;
			};
			this.saveSuccessPatched = true;
		},

		decorateUpdatedCard(model) {
			if (!this.$list || !model) {
				return;
			}
			const $card = this.$list.find(`.question[data-question-id="${model.id}"]`);
			if ($card.length) {
				$card.addClass('qsm-question-bank-item');
				$card.attr('data-question-type', model.get('type') || '');
			}
		},

		primeQuestionModel(question) {
			if (!window.QSMQuestion || !question) {
				return null;
			}
			const id = parseInt(question.id, 10);
			if (!id) {
				return null;
			}
			if (!QSMQuestion.questions) {
				if (window.Backbone && QSMQuestion.questionCollection) {
					QSMQuestion.questions = new QSMQuestion.questionCollection();
				} else {
					return null;
				}
			}
			let model = QSMQuestion.questions.get(id);
			if (model) {
				model.set(question);
				return model;
			}
			model = new QSMQuestion.question(question);
			QSMQuestion.questions.add(model, { merge: true });
			return model;
		},

		fetchQuestions({ resetPage = false } = {}) {
			if (this.isLoading) {
				return;
			}

			if (resetPage) {
				this.state.page = 1;
			}

			this.toggleLoading(true);

			$.ajax({
				url: pageData.restUrl,
				method: 'GET',
				beforeSend: (xhr) => {
					xhr.setRequestHeader('X-WP-Nonce', pageData.nonce);
				},
				data: {
					page: this.state.page,
					search: this.state.search,
					quizID: this.state.quiz,
					category: this.state.category,
					type: this.state.type,
					per_page: pageData.perPage || 20,
				},
			}).done((response) => {
				this.renderResponse(response);
			}).fail(() => {
				this.showError();
			}).always(() => {
				this.toggleLoading(false);
			});
		},

		renderResponse(response) {
			const questions = Array.isArray(response?.questions) ? response.questions : [];
			const pagination = response?.pagination || {};
			this.state.totalPages = parseInt(pagination.total_pages, 10) || 1;
			this.$list.empty();

			if (!questions.length) {
				this.showEmpty();
				return;
			}

			this.$emptyNotice.hide();

			questions.forEach((question) => {
				this.primeQuestionModel(question);
				const $question = this.buildQuestion(question);
				this.$list.append($question);
			});

			this.renderPagination();
		},

		buildQuestion(question) {
			const id = parseInt(question.id, 10) || '';
			const questionMarkup = question.question_title || question.name || pageData.i18n.questionPlaceholder;
			const category = question.category || '';
			const type = question.type || '';
			const templateData = {
				id,
				type,
				question: questionMarkup,
				category,
			};

			const $node = $(this.template(templateData));
			$node.addClass('qsm-question-bank-item');
			// $node.find('.form-actions, .qsm-actions-link-box, .qsm-admin-select-question-input').remove();
			return $node;
		},

		renderPagination() {
			if (this.state.totalPages <= 1) {
				this.$pagination.hide();
				return;
			}

			this.$pagination.show();
			this.$prev.prop('disabled', this.state.page <= 1);
			this.$next.prop('disabled', this.state.page >= this.state.totalPages);

			const infoText = (pageData.i18n.pageOf || 'Page %1$s of %2$s')
				.replace('%1$s', this.state.page)
				.replace('%2$s', this.state.totalPages);
			this.$pageInfo.text(infoText);

			const maxButtons = 5;
			let start = Math.max(1, this.state.page - 2);
			let end = Math.min(this.state.totalPages, start + maxButtons - 1);
			start = Math.max(1, end - maxButtons + 1);

			this.$pageButtons.empty();
			for (let page = start; page <= end; page++) {
				const $button = $('<button type="button" class="button qsm-question-bank-page-btn"></button>')
					.text(page)
					.data('page', page);
				if (page === this.state.page) {
					$button.addClass('current button-primary').attr('disabled', true);
				}
				this.$pageButtons.append($button);
			}
		},

		showEmpty() {
			this.$list.html(
				`<div class="qsm-question-bank-empty-state">${this.escape(pageData.i18n.noResults || 'No questions found.')}</div>`
			);
			this.$emptyNotice.show();
			this.$pagination.hide();
		},

		showError() {
			this.$list.html(
				`<div class="qsm-question-bank-error">${this.escape(pageData.i18n.error)}</div>`
			);
			this.$pagination.hide();
		},

		toggleLoading(isLoading) {
			this.isLoading = isLoading;
			if (isLoading) {
				this.$list.addClass('qsm-question-bank-list--loading');
				this.$loader.show();
			} else {
				this.$list.removeClass('qsm-question-bank-list--loading');
				this.$loader.hide();
			}
		},

		escape(value) {
			return $('<div>').text(value ?? '').html();
		},

		handleEditClick(event, useModal = false) {
			event.preventDefault();
			if (!this.editorReady) {
				this.showNotice(pageData?.i18n?.loading || 'Preparing editor…', 'info');
				return;
			}
			const $button = $(event.currentTarget);
			const $question = $button.closest('.question');
			const questionId = parseInt($question.data('question-id'), 10);
			if (!questionId || !window.QSMQuestion) {
				return;
			}
			$button.addClass('is-loading');
			this.ensureQuestionModel(questionId)
				.then((model) => {
					this.openModelInPopup(model, $button, useModal);
				})
				.catch((error) => {
					this.showNotice(pageData?.i18n?.error || 'Unable to load question.', 'error');
					if (
						window.QSMAdmin &&
						typeof QSMAdmin.displayError === 'function' &&
						error &&
						(typeof error.errorThrown !== 'undefined' || typeof error.statusText !== 'undefined')
					) {
						QSMAdmin.displayError(error);
					} else {
						console.error(error);
					}
				})
				.finally(() => {
					$button.removeClass('is-loading');
				});
		},

		handleCreateQuestion(useModal = false) {
			if (!this.editorReady || !window.QSMQuestion || !QSMQuestion.questions) {
				this.showNotice(pageData?.i18n?.loading || 'Preparing editor…', 'info');
				return;
			}
			if (this.isCreating) {
				return;
			}
			this.isCreating = true;
			const attributes = {
				quizID: window.qsmQuestionSettings?.quizID || 0,
				page: 0,
			};
			QSMQuestion.questions.create(attributes, {
				wait: true,
				headers: {
					'X-WP-Nonce': window.qsmQuestionSettings?.nonce || pageData.nonce,
				},
				success: (model) => {
					this.isCreating = false;
					const $card = this.upsertQuestionCard(model, { prepend: true });
					const $trigger = useModal ? null : ($card ? $card.find('.edit-question-button').first() : null);
					this.openModelInPopup(model, $trigger, useModal);
				},
				error: (error) => {
					this.isCreating = false;
					this.showNotice(pageData?.i18n?.error || 'Unable to create question.', 'error');
					if (
						window.QSMAdmin &&
						typeof QSMAdmin.displayError === 'function' &&
						error &&
						(typeof error.errorThrown !== 'undefined' || typeof error.statusText !== 'undefined')
					) {
						QSMAdmin.displayError(error);
					} else {
						console.error(error);
					}
				},
			});
		},

		ensureQuestionModel(questionId) {
			if (!questionId) {
				return Promise.reject('invalid-question-id');
			}
			if (this.modelRequests[questionId]) {
				return this.modelRequests[questionId];
			}
			this.modelRequests[questionId] = new Promise((resolve, reject) => {
				const endpoint = this.getQuestionEndpoint(questionId);
				if (!endpoint) {
					reject('missing-endpoint');
					return;
				}
				$.ajax({
					url: endpoint,
					method: 'GET',
					beforeSend: (xhr) => {
						xhr.setRequestHeader('X-WP-Nonce', window.qsmQuestionSettings?.nonce || pageData.nonce);
					},
				})
					.done((response) => {
						let model = QSMQuestion.questions?.get(questionId);
						if (model) {
							model.set(response);
						} else {
							model = new QSMQuestion.question(response);
							if (QSMQuestion.questions) {
								QSMQuestion.questions.add(model, { merge: true });
							}
						}
						const quizId = response?.quizID ?? model?.get('quizID');
						this.updateRestNonceForQuiz(quizId);
						resolve(model);
					})
					.fail(reject)
					.always(() => {
						delete this.modelRequests[questionId];
					});
			});
			return this.modelRequests[questionId];
		},

		getQuestionEndpoint(questionId) {
			if (window.wpApiSettings?.root) {
				return `${wpApiSettings.root}quiz-survey-master/v1/questions/${questionId}`;
			}
			if (!pageData.restUrl) {
				return '';
			}
			const base = pageData.restUrl.replace('bank_questions/0/', 'questions/');
			return `${base}${questionId}`;
		},

		openModelInPopup(model, $trigger, useModal = false) {
			if (!model) {
				return;
			}
			const $button = ($trigger && $trigger.length)
				? $trigger
				: this.$list.find(`.question[data-question-id="${model.id}"] .edit-question-button`).first();
			if (!$button.length) {
				return;
			}
			QSMQuestion.openEditPopup(model.id, $button, useModal);
		},

		upsertQuestionCard(model, { prepend = false } = {}) {
			if (!this.template || !model) {
				return null;
			}
			const templateData = {
				id: model.id,
				type: model.get('type') || '',
				question: this.getModelQuestionText(model),
				category: model.get('category') || '',
			};
			const $node = $(this.template(templateData)).addClass('qsm-question-bank-item');
			if (prepend) {
				this.$list.prepend($node);
			} else {
				this.$list.append($node);
			}
			return $node;
		},

		getModelQuestionText(model) {
			return model.get('question_title') || model.get('name') || pageData.i18n.questionPlaceholder;
		},

		showNotice(message, type = 'info') {
			if (window.QSMAdmin && typeof QSMAdmin.displayAlert === 'function') {
				QSMAdmin.displayAlert(message, type);
				return;
			}
			console.log(`[QSM Question Bank] ${message}`);
		},

		initBulkUpload() {
			if (!this.$bulkModal.length || !this.$bulkImportButton.length) {
				return;
			}
			this.bulkModalConfig = {
				awaitOpenAnimation: true,
				awaitCloseAnimation: true,
				onClose: () => {
					this.bulkState.isOpen = false;
					this.resetBulkForm();
					this.showBulkStatus(this.getBulkMessage('bulkUploadClosed', 'Bulk upload closed.'), 'info');
				},
			};
			this.$bulkImportButton.on('click', (event) => {
				event.preventDefault();
				if ( this.bulkState.isUploading ) {
					return;
				}
				this.toggleBulkPanel(true);
				QSMQuestion.closeEditPopup();
			});
			this.$bulkCancel.on('click', (event) => {
				event.preventDefault();
				this.toggleBulkPanel(false);
			});
			this.$bulkBrowse.on('click', (event) => {
				event.preventDefault();
				this.$bulkFileInput.trigger('click');
			});
			this.$bulkFileInput.on('change', (event) => {
				const file = event.target.files?.[0] || null;
				this.setBulkFile(file);
			});
			this.$bulkDropzone.on('drag dragstart dragend dragover dragenter dragleave drop', (event) => {
				event.preventDefault();
				event.stopPropagation();
			});
			this.$bulkDropzone.on('dragover dragenter', () => {
				this.$bulkDropzone.addClass('is-dragover');
			});
			this.$bulkDropzone.on('dragleave dragend drop', () => {
				this.$bulkDropzone.removeClass('is-dragover');
			});
			this.$bulkDropzone.on('drop', (event) => {
				const file = event.originalEvent?.dataTransfer?.files?.[0];
				if (file) {
					this.setBulkFile(file);
				}
			});
			this.$bulkForm.on('submit', (event) => {
				event.preventDefault();
				this.submitBulkUpload();
			});
		},

		toggleBulkPanel(forceOpen = null) {
			if (!this.$bulkModal.length) {
				return;
			}
			const targetState = forceOpen === null ? !this.bulkState.isOpen : forceOpen;
			const shouldOpen = Boolean(targetState);
			const hasMicroModal = 'undefined' !== typeof window.MicroModal;
			if ( shouldOpen ) {
				if ( this.bulkState.isOpen ) {
					return;
				}
				this.bulkState.isOpen = true;
				this.showBulkStatus(this.getBulkMessage('bulkUploadOpened', 'Bulk upload ready.'), 'info');
				if ( hasMicroModal ) {
					window.MicroModal.show('qsm-bulk-upload-modal', this.bulkModalConfig);
				} else {
					this.$bulkModal.attr('aria-hidden', 'false').addClass('is-visible');
				}
				return;
			}
			if ( ! this.bulkState.isOpen ) {
				return;
			}
			if ( hasMicroModal ) {
				window.MicroModal.close('qsm-bulk-upload-modal');
				return;
			}
			this.$bulkModal.attr('aria-hidden', 'true').removeClass('is-visible');
			this.bulkState.isOpen = false;
			this.resetBulkForm();
			this.showBulkStatus(this.getBulkMessage('bulkUploadClosed', 'Bulk upload closed.'), 'info');
		},

		setBulkFile(file) {
			if (!file) {
				this.bulkState.file = null;
				this.$bulkFileInput.val('');
				this.$bulkFileLabel.text('');
				return;
			}
			if (!this.isCsvFile(file)) {
				this.showBulkStatus(this.getBulkMessage('bulkUploadInvalid', 'Invalid file type.'), 'error');
				this.bulkState.file = null;
				return;
			}
			if (this.bulkConfig?.maxFileSize && file.size > this.bulkConfig.maxFileSize) {
				this.showBulkStatus(this.getBulkMessage('bulkUploadTooLarge', 'File is too large.'), 'error');
				this.bulkState.file = null;
				return;
			}
			this.bulkState.file = file;
			this.$bulkFileLabel.text(file.name);
			this.showBulkStatus(`${file.name} selected.`, 'info');
		},

		submitBulkUpload() {
			if (this.bulkState.isUploading) {
				return;
			}
			if (!this.bulkState.file) {
				this.showBulkStatus(this.getBulkMessage('bulkUploadNoFile', 'Select a CSV to upload.'), 'error');
				return;
			}
			if (!this.$bulkForm.length) {
				return;
			}
			const formData = new FormData(this.$bulkForm[0]);
			formData.set('bulk_csv', this.bulkState.file);
			if (this.bulkConfig?.action) {
				formData.append('action', this.bulkConfig.action);
			}
			if (this.bulkConfig?.nonce) {
				formData.append('_ajax_nonce', this.bulkConfig.nonce);
			}
			this.bulkState.isUploading = true;
			this.$bulkSubmit.prop('disabled', true).addClass('is-busy');
			this.showBulkStatus(this.getBulkMessage('bulkUploadUploading', 'Uploading…'), 'info');
			$.ajax({
				url: this.bulkConfig?.ajaxUrl || window.ajaxurl,
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
			})
				.done((response) => {
					const success = response?.success || response?.status === 'success';
					if (success) {
						const message = response?.data?.message || response?.message || this.getBulkMessage('bulkUploadSuccess', 'Upload complete.');
						this.showBulkStatus(message, 'success');
						this.renderBulkSummary(response?.data?.summary || response?.summary || {});
						this.fetchQuestions({ resetPage: true });
					} else {
						const errorMessage = response?.data?.message || response?.data?.error || response?.message || this.getBulkMessage('bulkUploadError', 'Upload failed.');
						this.showBulkStatus(errorMessage, 'error');
					}
				})
				.fail((error) => {
					console.error(error);
					this.showBulkStatus(this.getBulkMessage('bulkUploadError', 'Upload failed.'), 'error');
				})
				.always(() => {
					this.bulkState.isUploading = false;
					this.$bulkSubmit.prop('disabled', false).removeClass('is-busy');
				});
		},

		resetBulkForm() {
			if (!this.$bulkForm.length) {
				return;
			}
			this.$bulkForm[0].reset();
			this.bulkState.file = null;
			this.$bulkFileLabel.text('');
			this.$bulkSummary.empty();
		},

		isCsvFile(file) {
			if (!file) {
				return false;
			}
			const mime = (file.type || '').toLowerCase();
			const name = (file.name || '').toLowerCase();
			return mime.includes('csv') || name.endsWith('.csv');
		},

		showBulkStatus(message, type = 'info') {
			if (!this.$bulkStatus.length) {
				this.showNotice(message, type);
				return;
			}
			this.$bulkStatus.removeClass('is-info is-error is-success').addClass(`is-${type}`);
			this.$bulkStatus.text(message || '');
		},

		renderBulkSummary(summary) {
			if (!this.$bulkSummary.length) {
				return;
			}
			if (!summary || ('object' !== typeof summary && !Array.isArray(summary))) {
				this.$bulkSummary.empty();
				return;
			}
			if (Array.isArray(summary)) {
				const items = summary.map((item) => `<li>${this.escape(item)}</li>`).join('');
				this.$bulkSummary.html(`<ul>${items}</ul>`);
				return;
			}
			const rows = Object.entries(summary).map(([key, value]) => (
				`<li><strong>${this.escape(key)}:</strong> ${this.escape(String(value))}</li>`
			)).join('');
			this.$bulkSummary.html(`<ul>${rows}</ul>`);
		},

		getBulkMessage(key, fallback = '') {
			return pageData?.i18n?.[key] || fallback;
		},
	};

	$(function () {
		QuestionBankPage.init();
	});
})(jQuery);
