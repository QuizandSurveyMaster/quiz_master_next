
(function($){
	'use strict';

	const QSM_SETUP_WIZARD_STORAGE_KEY = 'qsm_setup_wizard_completed';
	
	const QSM_TOUR_START_DELAY = 400;
	const QSM_TOTAL_TOUR_STEPS = 9;
	let qsmNextTourStartTimer = null;
	const QSM_ADVANCED_HELPER_TEXTS = {
		answer_limit_area: 'Set how many answers users can select.',
		grading_mode_area: 'Choose how this question should be graded.',
		add_poll_type_area: 'Turn this into a poll to show how others responded.',
		correct_answer_info_area: 'Add an explanation to support the correct answer.',
		comments_area: 'Allow users to add comments for this question.',
		hint_area: 'Provide a hint to guide users before answering.'
	};
	const qsmTourState = {
		steps: [],
		index: 0,
		started: false,
		nextIndexOnClose: null,
		cleanupCurrent: null,
		tourName: null,
		pendingFirstQuestionTour: false,
		waitingForFirstQuestionSave: false,
		onEnd: null,
		forceSetupWizard: false,
		addAnswerClicked: false,
		waitingForEnhancementsSave: false,
		enhancementFinalStep: null,
		manualStart: false
	};

	const QSM_TOUR_ACTIVE_QUESTION_TARGETS = [
		'.questions .question.opened',
		'.qsm_tab_content .question.opened',
		'.qsm-quiz-questions .question.opened',
		'.question.opened',
		'.questions .question:visible:first',
		'.qsm_tab_content .question:visible:first',
		'.qsm-quiz-questions .question:visible:first',
		'.questionElements:visible',
		'.questionElements',
		'#save-popup-button',
		'body'
	];

	function qsmEnsureAdvancedHelperStyles() {
		if ( document.getElementById( 'qsm-advanced-helper-style' ) ) {
			return;
		}
		const style = document.createElement( 'style' );
		style.id = 'qsm-advanced-helper-style';
		style.type = 'text/css';
		style.textContent = '.qsm-advanced-helper-text{margin-top:6px;margin-bottom:0;font-size:13px;color:#555;}.qsm-advanced-helper-label{padding:0 20px;bottom:8px;position:relative;color:gray;font-size:12px;display:block;}';
		document.head.appendChild( style );
	}

	function qsmApplyAdvancedHelperTexts( $container ) {
		qsmEnsureAdvancedHelperStyles();
		const $context = $container && $container.length ? $container : $( document );
		Object.keys( QSM_ADVANCED_HELPER_TEXTS ).forEach( function( areaId ) {
			const $area = $context.find( '#' + areaId ).first();
			if ( !$area.length ) {
				return;
			}
			const $content = $area.find( '.qsm-toggle-box-content' ).first();
			if ( !$content.length ) {
				return;
			}
			const helperData = $content.data( 'qsmHelperData' );
			if ( helperData && helperData.updateVisibility ) {
				helperData.updateVisibility();
				return;
			}
			const helperText = window.qsm_admin_messages?.guided_wizard?.[ areaId ] || QSM_ADVANCED_HELPER_TEXTS[ areaId ];
			const $helperLabel = $( '<span class="qsm-advanced-helper-label helper-text"></span>' ).text( helperText );
			const $helper = $( '<p class="qsm-advanced-helper-text"></p>' ).text( helperText );
			$content.before( $helperLabel );
			const updateVisibility = function(){
				$helperLabel.toggle( $content.is( ':visible' ) );
			};
			updateVisibility();
			const data = { helper: $helper, updateVisibility: updateVisibility };
			if ( 'undefined' !== typeof MutationObserver ) {
				const observer = new MutationObserver( updateVisibility );
				observer.observe( $content[0], { attributes: true, attributeFilter: [ 'style', 'class' ] } );
				data.observer = observer;
			} else {
				$area.on( 'click.qsmAdvancedHelper', '.qsm-toggle-box-handle', function(){
					setTimeout( updateVisibility, 0 );
				});
			}
			$content.data( 'qsmHelperData', data );
		} );
	}

	function qsmOpenTourStepWithDelay( stepIndex ) {
		if ( qsmNextTourStartTimer ) {
			clearTimeout( qsmNextTourStartTimer );
			qsmNextTourStartTimer = null;
		}
		qsmNextTourStartTimer = setTimeout( function() {
			qsmNextTourStartTimer = null;
			qsmOpenTourStep( stepIndex );
		}, QSM_TOUR_START_DELAY );
	}

	function qsmGetActiveQuestionTarget() {
		for ( let i = 0; i < QSM_TOUR_ACTIVE_QUESTION_TARGETS.length; i++ ) {
			const selector = QSM_TOUR_ACTIVE_QUESTION_TARGETS[ i ];
			if ( !selector ) {
				continue;
			}
			const $candidate = $( selector ).first();
			if ( $candidate.length ) {
				return $candidate;
			}
		}
		return $();
	}

	function qsmEnsurePointerStyles() {
		if ( document.getElementById( 'qsm-tour-pointer-style' ) ) {
			return;
		}
		const style = document.createElement( 'style' );
		style.id = 'qsm-tour-pointer-style';
		style.type = 'text/css';
		style.textContent = '' +
			'.wp-pointer.qsm-pointer-centered{position:fixed!important;top:50%!important;left:50%!important;transform:translate(-50%,-50%)!important;max-width:420px;width:calc(100% - 40px);margin:0;z-index:10050;}' +
			'.wp-pointer.qsm-pointer-centered .wp-pointer-arrow,' +
			'.wp-pointer.qsm-pointer-centered .wp-pointer-arrow:before,' +
			'.wp-pointer.qsm-pointer-centered .wp-pointer-arrow:after{display:none!important;}' +
			'.wp-pointer.qsm-pointer-congrats .wp-pointer-content h3{position:relative;padding-right:28px;}' +
			'.wp-pointer.qsm-pointer-congrats .wp-pointer-content h3:after{content:"×";position:absolute;right:0;top:50%;transform:translate(-15px, -15px);font-size:22px;font-weight:700;color:white;pointer-events:none;}' +
			'.wp-pointer.qsm-pointer-congrats .qsm-pointer-congrats-close-target{position:absolute;top:8px;right:8px;width:28px;height:28px;border:none;background:transparent;cursor:pointer;}' +
			'.wp-pointer.qsm-pointer-congrats .qsm-pointer-congrats-close-target:focus{outline:2px solid #fff;}';
		document.head.appendChild( style );
	}

	function qsmUpdateWizardCompletionState( completed ) {
		try {
			if ( typeof window.qsm_admin_messages !== 'undefined' && window.qsm_admin_messages?.guided_wizard ) {
				window.qsm_admin_messages.guided_wizard.completed = completed ? 1 : 0;
			}
		} catch (e) {
			console.debug( e );
		}
	}

	function qsmPersistWizardCompletionState( completed ) {
		try {
			const nonce = window.qsm_admin_messages?.guided_wizard?.nonce;
			if ( !nonce || typeof window.ajaxurl === 'undefined' ) {
				qsmUpdateWizardCompletionState( completed );
				return;
			}
			$.post(
				window.ajaxurl,
				{
					action: completed ? 'qsm_mark_setup_wizard_completed' : 'qsm_reset_setup_wizard_completed',
					nonce: nonce
				}
			)
				.done(function( response ){
					qsmUpdateWizardCompletionState( completed );
					if ( response?.data?.completed !== undefined ) {
						qsmUpdateWizardCompletionState( response.data.completed === 1 || response.data.completed === '1' );
					}
				})
				.fail(function( xhr ){
					console.debug( xhr );
					qsmUpdateWizardCompletionState( completed );
				});
		} catch (e) {
			console.debug( e );
			qsmUpdateWizardCompletionState( completed );
		}
	}

	function qsmIsSetupWizardCompleted() {
		try {
			return window.qsm_admin_messages?.guided_wizard?.completed === 1 || window.qsm_admin_messages?.guided_wizard?.completed === '1';
		} catch (e) {
			console.debug( e );
			return false;
		}
	}

	function qsmMarkSetupWizardCompleted() {
		qsmPersistWizardCompletionState( true );
	}

	function qsmResetSetupWizardCompleted() {
		qsmPersistWizardCompletionState( false );
	}

	function qsmIsSetupWizardActive() {
		return qsmTourState.started && ( qsmTourState.tourName === 'first_question' || qsmTourState.tourName === 'question_enhancements' );
	}

	function qsmGetQuizCount() {
		try {
			const count = window.qsm_admin_messages?.quiz_count;
			const parsed = parseInt( count, 10 );
			return Number.isNaN( parsed ) ? 0 : parsed;
		} catch (e) {
			console.debug( e );
			return 0;
		}
	}

	function qsmShouldStartSetupWizard() {
		if ( qsmTourState.forceSetupWizard ) {
			return true;
		}
		if ( qsmTourState.manualStart ) {
			return false;
		}
		if ( qsmGetQuizCount() !== 0 ) {
			return false;
		}
		return !qsmIsSetupWizardCompleted();
	}

	function qsmMakeElementVisibleForTour( $el ) {
		const $affected = $el.parentsUntil( 'body' ).addBack();
		const previous = [];

		$affected.each(function(){
			const $node = $( this );
			const styleAttr = $node.attr( 'style' );
			previous.push({ el: $node, style: typeof styleAttr === 'undefined' ? null : styleAttr });

			if ( $node.css( 'display' ) === 'none' ) {
				$node.css( 'display', 'block' );
			}
			$node.css({
				visibility: 'visible',
				opacity: '1'
			});
		});

		return function restore(){
			for ( let i = 0; i < previous.length; i++ ) {
				if ( previous[i].style === null ) {
					previous[i].el.removeAttr( 'style' );
				} else {
					previous[i].el.attr( 'style', previous[i].style );
				}
			}
		};
	}

	function qsmApplySpotlight( $target ) {
		qsmEnsureSpotlightStyles();

		let $overlay = $( '#qsm-tour-spotlight-overlay' );
		if ( !$overlay.length ) {
			$overlay = $( '<div id="qsm-tour-spotlight-overlay" class="qsm-tour-spotlight-overlay"></div>' );
			$( 'body' ).append( $overlay );
		}

		const el = $target?.[0] || null;
		let prev = null;
		if ( el ) {
			prev = {
				position: el.style.position,
				zIndex: el.style.zIndex,
				boxShadow: el.style.boxShadow,
				borderRadius: el.style.borderRadius
			};
		}

		$target.addClass( 'qsm-tour-spotlight-target' );
		if ( el ) {
			if ( !prev.position || prev.position === 'static' ) {
				el.style.position = 'relative';
			}
			el.style.zIndex = '9992';
		}

		return function restoreSpotlight(){
			$( '#qsm-tour-spotlight-overlay' ).remove();
			$target.removeClass( 'qsm-tour-spotlight-target' );
			if ( el && prev ) {
				el.style.position = prev.position;
				el.style.zIndex = prev.zIndex;
				el.style.boxShadow = prev.boxShadow;
				el.style.borderRadius = prev.borderRadius;
			}
		};
	}

	function qsmApplySelectorBackground( $target, background ) {
		if ( !$target || !$target.length || !background ) {
			return null;
		}
		const styleValue = typeof background === 'string' ? background : background.color;
		if ( !styleValue ) {
			return null;
		}
		const previous = [];
		$target.each( function() {
			previous.push( { el: this, backgroundColor: this.style.backgroundColor } );
			this.style.backgroundColor = styleValue;
		} );
		return function restoreSelectorBackground(){
			previous.forEach( function( entry ) {
				if ( entry.backgroundColor ) {
					entry.el.style.backgroundColor = entry.backgroundColor;
				} else {
					entry.el.style.removeProperty( 'background-color' );
				}
			} );
		};
	}

	function qsmAnnotateGlobalSteps( steps, startingIndex = 1 ) {
		let marker = startingIndex;
		steps.forEach( function( step ) {
			step.globalStep = marker++;
		} );
	}

	function qsmStartFirstQuestionTour( startIndex = 0 ) {
		qsmTourState.tourName = 'first_question';
		qsmTourState.waitingForFirstQuestionSave = false;
		qsmTourState.onEnd = null;
		qsmTourState.steps = [
			{
				selector: '.questionElements:visible #question_type',
				fallbackSelectors: [ '.questionElements:visible', 'body' ],
				content: '<h3>'+ qsm_admin_messages.guided_wizard.first_question +'</h3><p>'+ qsm_admin_messages.guided_wizard.question_type +'</p>',
				position: { edge: 'bottom', align: 'center' },
				showBack: false
			},
			{
				selector: '#question_title',
				content: '<h3>'+ qsm_admin_messages.guided_wizard.question_title +'</h3><p>'+ qsm_admin_messages.guided_wizard.question_title_desc +'</p>',
				position: { edge: 'bottom', align: 'center' },
			},
			{
				selector: '#answers',
				content: '<h3>'+ qsm_admin_messages.guided_wizard.add_answer +'</h3><p>'+ qsm_admin_messages.guided_wizard.add_answer_text +'</p>' +
				'<p>'+ qsm_admin_messages.guided_wizard.add_answer_desc1 +' <b>+</b> & <b>-</b> '+ qsm_admin_messages.guided_wizard.add_answer_desc2 +'</p>' +
				'<p>'+ qsm_admin_messages.guided_wizard.add_answer_desc3 +' <b>'+ qsm_admin_messages.guided_wizard.add_answer_desc4 +'</b> '+ qsm_admin_messages.guided_wizard.add_answer_desc5 +' <b>'+ qsm_admin_messages.guided_wizard.add_answer_desc6 +'</b>.</p>' +
				'<p>'+ qsm_admin_messages.guided_wizard.add_answer_desc7 +' <b>'+ qsm_admin_messages.guided_wizard.add_answer_desc8 +'</b> '+ qsm_admin_messages.guided_wizard.add_answer_desc9 +'</p>',
				position: { edge: 'bottom', align: 'left' },
				pointerClass: 'qsm-pointer-wide',
				beforeOpen: function(){
					const currentAnswers = $('#answers').find('.answers-single');
					if (currentAnswers.length) {
						for (let i = currentAnswers.length; i < 4; i++) {
							$('#new-answer-button').trigger('click');
						}
					} else {
						$('#new-answer-button').trigger('click').trigger('click').trigger('click').trigger('click');
					}
				}
			},
			{
				selector: '#save-popup-button',
				content: '<h3>'+ qsm_admin_messages.guided_wizard.save_question +'</h3><p>'+ qsm_admin_messages.guided_wizard.save_question_desc +'</p>',
				position: { edge: 'right', align: 'center' },
				showNext: false,
				showSkip: true,
				showBack: true,
				beforeCloseOnClick: function(){
					qsmTourState.waitingForFirstQuestionSave = true;
				},
				closeOnClick: '#save-popup-button'
			}
		];

		qsmAnnotateGlobalSteps( qsmTourState.steps, 1 );
		qsmTourState.started = true;
		qsmTourState.index = startIndex;
		qsmWaitForSelector( '.questionElements:visible #question_type', 5000, function(){
			qsmOpenTourStepWithDelay( qsmTourState.index );
		});
	}

	function qsmStartQuestionEnhancementsTour( startIndex = 0 ) {
		qsmTourState.tourName = 'question_enhancements';
		qsmTourState.waitingForEnhancementsSave = false;
		qsmTourState.onEnd = qsmFinalizeTourSession;
		qsmTourState.enhancementFinalStep = {
			selector: '.questions .question.opened',
			fallbackSelectors: [ '.questions .question.opened', '.question.opened', '.questionElements', '#save-popup-button', '.questions .question:first', 'body' ],
			content: '<h3>🎉 '+ qsm_admin_messages.guided_wizard.congrats2 +'</h3><p>'+ qsm_admin_messages.guided_wizard.congrats2_desc1 +'</p><p>'+ qsm_admin_messages.guided_wizard.congrats2_desc2 +'</p>',
			position: { edge: 'left', align: 'center' },
			pointerClass: 'qsm-pointer-centered qsm-pointer-congrats',
			spotlight: false,
			useActiveQuestionTarget: true,
			showSkip: false,
			showBack: false,
			hideDoneButton: true
		};
		qsmTourState.steps = [
			{
				selector: '#featureImagediv',
				content: '<h3>'+ qsm_admin_messages.guided_wizard.feature_image +'</h3><p>'+ qsm_admin_messages.guided_wizard.feature_image_desc +'</p>',
				position: { edge: 'right', align: 'center' },
				skipText: 'Skip'
			},
			{
				selector: '#categorydiv',
				content: '<h3>'+ qsm_admin_messages.guided_wizard.category +'</h3><p>'+ qsm_admin_messages.guided_wizard.category_desc +'</p>',
				position: { edge: 'right', align: 'center' },
				skipText: 'Skip'
			},
			{
				selector: '#submitdiv .ui-sortable-handle',
				content: '<h3>'+ qsm_admin_messages.guided_wizard.question_status +'</h3><p>'+ qsm_admin_messages.guided_wizard.question_status_desc1 +'</p><p>'+ qsm_admin_messages.guided_wizard.question_status_desc2 +'</p>',
				position: { edge: 'right', align: 'center' },
				skipText: 'Skip',
				applySelectorBackground: 'white',
			},
			{
				selector: '.qsm-question-misc-options.advanced-content',
				content: '<h3>'+ qsm_admin_messages.guided_wizard.advance_setting +'</h3><p>'+ qsm_admin_messages.guided_wizard.advance_setting_desc1 +'</p><p>'+ qsm_admin_messages.guided_wizard.advance_setting_desc2 +'</p>',
				position: { edge: 'bottom', align: 'center' },
				skipText: 'Skip',
				beforeOpen: function(){
					const $container = $( '.qsm-question-misc-options.advanced-content' );
					qsmApplyAdvancedHelperTexts( $container );
				}
			},
			{
				selector: '#save-popup-button',
				content: '<h3>'+ qsm_admin_messages.guided_wizard.save_updates +'</h3><p>'+ qsm_admin_messages.guided_wizard.save_updates_desc +'</p>',
				position: { edge: 'right', align: 'center' },
				showNext: false,
				showBack: true,
				showSkip: false,
				beforeCloseOnClick: function(){
					qsmTourState.waitingForEnhancementsSave = true;
				},
				closeOnClick: '#save-popup-button'
			}
		];

		qsmAnnotateGlobalSteps( qsmTourState.steps, 5 );
		qsmTourState.started = true;
		qsmTourState.index = startIndex;
		qsmWaitForSelector( '#answers', 5000, function(){
			qsmOpenTourStepWithDelay( qsmTourState.index );
		});
	}

	function qsmScrollIntoView( $el ) {
		try {
			$el?.[0]?.scrollIntoView?.({ block: 'center', inline: 'nearest' });
		} catch (e) {
			console.debug( e );
		}
	}

	function qsmGetTourTargetForStep( step ) {
		let $target = $();
		if ( step && step.useActiveQuestionTarget ) {
			$target = qsmGetActiveQuestionTarget();
			if ( $target.length ) {
				return $target;
			}
		}
		if ( step && step.selector ) {
			$target = $( step.selector ).first();
			if ( $target.length ) {
				return $target;
			}
		}
		if ( step && step.fallbackSelectors && step.fallbackSelectors.length ) {
			for ( let i = 0; i < step.fallbackSelectors.length; i++ ) {
				if ( !step.fallbackSelectors[i] ) {
					continue;
				}
				$target = $( step.fallbackSelectors[i] ).first();
				if ( $target.length ) {
					return $target;
				}
			}
		}
		return $();
	}

	function qsmEnsureSpotlightStyles() {
		if ( document.getElementById( 'qsm-tour-spotlight-style' ) ) {
			return;
		}
		const style = document.createElement( 'style' );
		style.id = 'qsm-tour-spotlight-style';
		style.type = 'text/css';
		style.textContent = '' +
			'.qsm-tour-spotlight-overlay{' +
				'position:fixed;inset:0;background:rgba(0,0,0,0.45);' +
				'z-index:9990;' +
			'}' +
			'.qsm-tour-spotlight-target{' +
				'box-shadow:0 0 0 4px rgba(255,255,255,0.9), 0 10px 35px rgba(0,0,0,0.35) !important;' +
				'border-radius:6px !important;' +
			'}';
		document.head.appendChild( style );
	}

	function qsmFinalizeTourSession() {
		qsmMarkSetupWizardCompleted();
		qsmTourState.forceSetupWizard = false;
		qsmTourState.pendingFirstQuestionTour = false;
		qsmTourState.waitingForFirstQuestionSave = false;
	}

	function qsmDisableFutureSetupTours() {
		qsmFinalizeTourSession();
	}

	function qsmOpenTourStep( stepIndex ) {
		if ( stepIndex < 0 ) {
			stepIndex = 0;
		}
		if ( stepIndex >= qsmTourState.steps.length ) {
			if ( typeof qsmTourState.cleanupCurrent === 'function' ) {
				qsmTourState.cleanupCurrent();
				qsmTourState.cleanupCurrent = null;
			}
			qsmTourState.started = false;
			qsmTourState.manualStart = false;
			if ( typeof qsmTourState.onEnd === 'function' ) {
				const onEnd = qsmTourState.onEnd;
				qsmTourState.onEnd = null;
				setTimeout(function(){
					try { onEnd(); } catch (e) { console.debug( e ); }
				}, 0);
				return;
			}
			qsmFinalizeTourSession();
			return;
		}

		qsmTourState.index = stepIndex;
		const step = qsmTourState.steps[ stepIndex ];
		let $target = qsmGetTourTargetForStep( step );
		if ( !$target.length ) {
			qsmOpenTourStep( stepIndex + 1 );
			return;
		}

		if ( typeof step.beforeOpen === 'function' ) {
			try {
				step.beforeOpen( $target, stepIndex );
			} catch (e) {
				console.debug( e );
			}
			$target = qsmGetTourTargetForStep( step );
			if ( !$target.length ) {
				qsmOpenTourStep( stepIndex + 1 );
				return;
			}
		}

		if ( typeof qsmTourState.cleanupCurrent === 'function' ) {
			qsmTourState.cleanupCurrent();
			qsmTourState.cleanupCurrent = null;
		}
		const cleanupFns = [];
		if ( step.forceVisible || !$target.is( ':visible' ) ) {
			cleanupFns.push( qsmMakeElementVisibleForTour( $target ) );
		}
		if ( false !== step.spotlight ) {
			cleanupFns.push( qsmApplySpotlight( $target ) );
		}
		if ( step.applySelectorBackground ) {
			const backgroundCleanup = qsmApplySelectorBackground( $target, step.applySelectorBackground );
			if ( backgroundCleanup ) {
				cleanupFns.push( backgroundCleanup );
			}
		}
		if ( cleanupFns.length ) {
			qsmTourState.cleanupCurrent = function(){
				for ( let i = 0; i < cleanupFns.length; i++ ) {
					try { cleanupFns[i](); } catch (e) { console.debug( e ); }
				}
			};
		}

		qsmScrollIntoView( $target );

		$target.pointer({
			content: step.content,
			position: step.position || { edge: 'left', align: 'center' },
			close: function() {
				if ( typeof qsmTourState.cleanupCurrent === 'function' ) {
					qsmTourState.cleanupCurrent();
					qsmTourState.cleanupCurrent = null;
				}
				const nextIndex = qsmTourState.nextIndexOnClose;
				qsmTourState.nextIndexOnClose = null;
				if ( qsmTourState.started && null !== nextIndex ) {
					qsmOpenTourStep( nextIndex );
					return;
				}
				const awaitingAsyncStep = qsmTourState.waitingForFirstQuestionSave || qsmTourState.waitingForEnhancementsSave;
				qsmTourState.started = false;
				qsmTourState.manualStart = false;
				if ( awaitingAsyncStep ) {
					return;
				}
				if ( typeof qsmTourState.onEnd === 'function' ) {
					const onEnd = qsmTourState.onEnd;
					qsmTourState.onEnd = null;
					setTimeout(function(){
						try { onEnd(); } catch (e) { console.debug( e ); }
					}, 0);
					return;
				}
				qsmFinalizeTourSession();
			},
			buttons: function( event, t ) {
				const $buttons = $( '<div class="qsm-admin-tour-buttons"></div>' );
				const $left = $( '<div class="qsm-admin-tour-buttons-left"></div>' );
				const $right = $( '<div class="qsm-admin-tour-buttons-right"></div>' );
				const totalSteps = QSM_TOTAL_TOUR_STEPS;
				const $counter = $( '<span class="qsm-admin-tour-counter"></span>' );
				const $back = $( '<button type="button" class="button">Prev</button>' );
				const $skip = $( '<button type="button" class="button">Skip</button>' );
				const $next = $( '<button type="button" class="button button-primary">Next</button>' );
				const $done = $( '<button type="button" class="button button-primary">Done</button>' );
				const isLastStep = ( stepIndex >= qsmTourState.steps.length - 1 );
				const showBack = ( stepIndex > 0 ) || ( true === step.showBack );
				const showSkip = ( false !== step.showSkip ) && !isLastStep;
				const showNext = ( false !== step.showNext ) && !isLastStep;
				if ( step.doneText ) {
					$done.text( step.doneText );
				}
				if ( step.skipText ) {
					$skip.text( step.skipText );
				}

				$back.on( 'click', function(e){
					e.preventDefault();
					if ( stepIndex <= 0 ) {
						return;
					}
					qsmTourState.nextIndexOnClose = stepIndex - 1;
					t.element.pointer('close');
				});

				$next.on( 'click', function(e){
					e.preventDefault();
					qsmTourState.nextIndexOnClose = stepIndex + 1;
					t.element.pointer('close');
				});

				$skip.on( 'click', function(e){
					e.preventDefault();
					qsmTourState.started = false;
					qsmTourState.nextIndexOnClose = null;
					t.element.pointer('close');
				});

				$done.on( 'click', function(e){
					e.preventDefault();
					qsmTourState.started = false;
					qsmTourState.nextIndexOnClose = null;
					t.element.pointer('close');
				});

				if ( Array.isArray( step.customButtons ) && step.customButtons.length ) {
					step.customButtons.forEach( function( customButton ) {
						const $custom = $( '<button type="button"></button>' );
						$custom.text( customButton.text || '' );
						$custom.addClass( customButton.className || 'button' );
						$custom.on( 'click', function( event ) {
							if ( typeof customButton.action === 'function' ) {
								customButton.action( event, t );
								return;
							}
							qsmTourState.started = false;
							qsmTourState.nextIndexOnClose = null;
							t.element.pointer('close');
						});
						$right.append( $custom );
					} );
					applyFooterContainerStyles( $right, false );
					$buttons.append( $left );
					$buttons.append( $right );
					return $buttons;
				}

				if ( step.globalStep ) {
					const counterTotal = step.globalStep <= 4 ? 4 : totalSteps;
					$counter.text( 'Step ' + step.globalStep + ' of ' + counterTotal );
					$left.append( $counter );
				}

				if ( showSkip ) {
					$right.append( $skip );
				}
				if ( showBack ) {
					$right.append( $back );
				}
				if ( showNext ) {
					$right.append( $next );
				}

				$buttons[0].style.display = 'flex';
				$buttons[0].style.alignItems = 'center';
				$buttons[0].style.justifyContent = 'space-between';
				applyFooterContainerStyles( $right, true );
				$buttons.append( $left );
				$buttons.append( $right );

				return $buttons;
			}
		}).pointer('open');

		// Wizard step indicator removed.

		if ( step.closeOnClick ) {
			$(document).one( 'click.qsmTourCloseOnClick', step.closeOnClick, function(){
				if ( typeof step.beforeCloseOnClick === 'function' ) {
					try { step.beforeCloseOnClick(); } catch (e) { console.debug( e ); }
				}
				try {
					$target.pointer('close');
				} catch (e) {
					console.debug( e );
				}
			});
		}

		// Fine-tune pointer arrow position (beyond edge/align) if requested.
		setTimeout(function(){
			const api = $target.data( 'wpPointer' );
			let $pointer = null;
			if ( api?.pointer ) {
				$pointer = api.pointer;
			} else {
				$pointer = $( '.wp-pointer:visible' ).last();
			}
			if ( !$pointer || !$pointer.length ) {
				return;
			}
			if ( step.pointerClass ) {
				qsmEnsurePointerStyles();
				$pointer.addClass( step.pointerClass );
				if ( step.pointerClass.indexOf( 'qsm-pointer-congrats' ) !== -1 && !$pointer.find('.qsm-pointer-congrats-close-target').length ) {
					const $closeButton = $( '<button type="button" class="qsm-pointer-congrats-close-target" aria-label="Close"></button>' );
					$closeButton.on( 'click', function( e ) {
						e.preventDefault();
						qsmTourState.started = false;
						qsmTourState.nextIndexOnClose = null;
						try { $target.pointer('close'); } catch (err) { console.debug( err ); }
					});
					$pointer.append( $closeButton );
				}
			}
			if ( step.arrowCss && typeof step.arrowCss === 'object' ) {
				$pointer.find( '.wp-pointer-arrow' ).css( step.arrowCss );
			}
		}, 0);
	}

	function applyFooterContainerStyles( $right, applyWidth ) {
		if ( !$right || !$right.length ) {
			return;
		}
		$right[0].style.display = 'flex';
		$right[0].style.alignItems = 'center';
		$right[0].style.justifyContent = 'space-around';
		if ( applyWidth ) {
			const buttonCount = $right.find('button').length;
			if ( buttonCount > 0 ) {
				$right[0].style.minWidth = '40%';
				$right[0].style.width = (buttonCount < 3 ? '40%' : '60%');
			}
		}
	}

	function qsmWaitForSelector( selector, timeoutMs, cb ) {
		const start = Date.now();
		(function tick(){
			if ( $( selector ).length ) {
				cb();
				return;
			}
			if ( Date.now() - start >= timeoutMs ) {
				cb();
				return;
			}
			setTimeout( tick, 500 );
		})();
	}

	$(function(){
		window.qsmIsSetupWizardActive = qsmIsSetupWizardActive;
		window.qsmIsSetupWizardPending = function(){
			return Boolean( qsmTourState.pendingFirstQuestionTour && !qsmTourState.started );
		};

		if ( !$('body').hasClass('admin_page_mlw_quiz_options') ) {
			return;
		}

		qsmTourState.pendingFirstQuestionTour = qsmShouldStartSetupWizard();

		$(document).on('click', '#qsm-show-setup-wizard-again', function(e){
			e.preventDefault();
			qsmResetSetupWizardCompleted();
			qsmTourState.forceSetupWizard = true;
			qsmTourState.pendingFirstQuestionTour = true;
		});

		$(document).on('click', '#qsm-start-admin-tour', function(e){
			e.preventDefault();
			$('.qsm-help-tab-dropdown-list, .qsm-help-tab-handle').removeClass('opened');
			qsmTourState.manualStart = true;
			qsmTourState.forceSetupWizard = false;
			qsmTourState.pendingFirstQuestionTour = false;
			qsmTourState.started = false;
			qsmTourState.waitingForFirstQuestionSave = false;
			qsmTourState.onEnd = null;
			const hasEditorVisible = $('.questionElements:visible #question_type').length > 0;
			if ( hasEditorVisible ) {
				qsmStartFirstQuestionTour();
				return;
			}
			qsmTourState.pendingFirstQuestionTour = true;
			const $editButton = $('.questions .question:first .edit-question-button');
			if ( $editButton.length ) {
				$editButton.trigger('click');
			} else {
				$('.questions .new-question-button').first().trigger('click');
			}
		});

		$(document).on('click', '.questions .new-question-button', function(){
			qsmTourState.pendingFirstQuestionTour = qsmShouldStartSetupWizard();
		});
		$(document).on('qsm_open_edit_popup', function(){
			if ( qsmTourState.started ) {
				return;
			}
			if ( qsmTourState.pendingFirstQuestionTour ) {
				qsmTourState.pendingFirstQuestionTour = false;
				qsmStartFirstQuestionTour();
			}
		});
		$(document).on('qsm_admin_question_saved_success', function(){
			if ( qsmTourState.tourName === 'first_question' && ( qsmTourState.started || qsmTourState.waitingForFirstQuestionSave ) ) {
				// Do NOT mark wizard complete here. Completion is marked after enhancements tour ends.
				qsmTourState.waitingForFirstQuestionSave = false;
				qsmTourState.steps = [
					{
						selector: '.questions .question.opened',
						fallbackSelectors: [ '.questions .question.opened', '.question.opened', '.questionElements', '#save-popup-button', '.questions .question:first', 'body' ],
						content: '<h3>✅ '+ qsm_admin_messages.guided_wizard.congrats1 +'</h3><p><b>'+ qsm_admin_messages.guided_wizard.congrats1_desc1 +'</b></p><p>'+ qsm_admin_messages.guided_wizard.congrats1_desc2 +'</p>',
						position: { edge: 'left', align: 'center' },
						pointerClass: 'qsm-pointer-centered',
						spotlight: false,
						useActiveQuestionTarget: true,
						customButtons: [
							{
								text: "I'll explore myself",
								className: 'button',
								action: function( event, pointerApi ) {
									event.preventDefault();
									qsmDisableFutureSetupTours();
									qsmTourState.onEnd = null;
									qsmTourState.started = false;
									pointerApi.element.pointer('close');
								}
							},
							{
								text: 'Continue with Tour',
								className: 'button button-primary',
								action: function( event, pointerApi ) {
									event.preventDefault();
									pointerApi.element.pointer('close');
									qsmTourState.nextIndexOnClose = null;
									qsmStartQuestionEnhancementsTour();
								}
							}
						]
					}
				];
				qsmTourState.index = 0;
				qsmTourState.onEnd = qsmStartQuestionEnhancementsTour;
				qsmTourState.started = true;
				qsmOpenTourStepWithDelay( 0 );
				return;
			}
			if ( qsmTourState.tourName === 'question_enhancements' && qsmTourState.waitingForEnhancementsSave ) {
				qsmTourState.waitingForEnhancementsSave = false;
				qsmTourState.steps = [ qsmTourState.enhancementFinalStep ];
				qsmTourState.index = 0;
				qsmTourState.started = true;
				qsmOpenTourStepWithDelay( 0 );
			}
		});

	});
})(jQuery);
