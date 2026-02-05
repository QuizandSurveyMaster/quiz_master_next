(function($){
	'use strict';

	var QSM_SETUP_WIZARD_STORAGE_KEY = 'qsm_setup_wizard_completed';
	var QSM_SETUP_WIZARD_STATE_STORAGE_KEY = 'qsm_setup_wizard_state';

	var QSM_TOUR_START_DELAY = 400;
	var qsmNextTourStartTimer = null;
	var qsmTourState = {
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
		addAnswerClicked: false
	};

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

	function qsmIsSetupWizardCompleted() {
		try {
			return window.localStorage && window.localStorage.getItem( QSM_SETUP_WIZARD_STORAGE_KEY ) === '1';
		} catch (e) {
			return false;
		}
	}

	function qsmMarkSetupWizardCompleted() {
		try {
			if ( window.localStorage ) {
				window.localStorage.setItem( QSM_SETUP_WIZARD_STORAGE_KEY, '1' );
			}
		} catch (e) {
			// ignore
		}
	}

	function qsmResetSetupWizardCompleted() {
		try {
			if ( window.localStorage ) {
				window.localStorage.removeItem( QSM_SETUP_WIZARD_STORAGE_KEY );
			}
		} catch (e) {
			// ignore
		}
	}

	function qsmSetSetupWizardState( state ) {
		try {
			if ( !window.localStorage ) {
				return;
			}
			if ( !state ) {
				window.localStorage.removeItem( QSM_SETUP_WIZARD_STATE_STORAGE_KEY );
				return;
			}
			window.localStorage.setItem( QSM_SETUP_WIZARD_STATE_STORAGE_KEY, JSON.stringify( state ) );
		} catch (e) {
			// ignore
		}
	}

	function qsmGetSetupWizardState() {
		try {
			if ( !window.localStorage ) {
				return null;
			}
			var raw = window.localStorage.getItem( QSM_SETUP_WIZARD_STATE_STORAGE_KEY );
			if ( !raw ) {
				return null;
			}
			return JSON.parse( raw );
		} catch (e) {
			return null;
		}
	}

	function qsmClearSetupWizardState() {
		qsmSetSetupWizardState( null );
	}

	function qsmIsSetupWizardTourName( tourName ) {
		return tourName === 'first_question' || tourName === 'question_behavior' || tourName === 'question_enhancements';
	}

	function qsmStartWizardTourByName( tourName, startIndex ) {
		if ( tourName === 'first_question' ) {
			qsmStartFirstQuestionTour( startIndex );
			return;
		}
		if ( tourName === 'question_behavior' ) {
			qsmStartQuestionBehaviorTour( startIndex );
			return;
		}
		if ( tourName === 'question_enhancements' ) {
			qsmStartQuestionEnhancementsTour( startIndex );
			return;
		}
	}

	function qsmHasAnySavedQuestions() {
		return $( '.questions .question' ).length > 0;
	}

	function qsmShouldStartSetupWizard() {
		if ( qsmTourState.forceSetupWizard ) {
			return true;
		}
		if ( qsmIsSetupWizardCompleted() ) {
			return false;
		}
		return !qsmHasAnySavedQuestions();
	}

	function qsmMakeElementVisibleForTour( $el ) {
		var $affected = $el.parentsUntil( 'body' ).addBack();
		var previous = [];

		$affected.each(function(){
			var $node = $( this );
			var styleAttr = $node.attr( 'style' );
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
			for ( var i = 0; i < previous.length; i++ ) {
				if ( previous[i].style === null ) {
					previous[i].el.removeAttr( 'style' );
				} else {
					previous[i].el.attr( 'style', previous[i].style );
				}
			}
		};
	}

	function qsmStartFirstQuestionTour( startIndex ) {
		qsmTourState.tourName = 'first_question';
		qsmTourState.waitingForFirstQuestionSave = false;
		qsmTourState.onEnd = null;
		qsmTourState.steps = [
			{
				selector: '.questionElements:visible #question_type',
				fallbackSelectors: [ '.questionElements:visible', 'body' ],
				content: '<h3>Create your first question</h3><p>Choose the question type and add answers.</p><h4>Question Type</h4><p>Choose how users will answer this question.</p>',
				position: { edge: 'bottom', align: 'center' },
				wizardStep: 1,
				totalWizardSteps: 3,
				showBack: false
			},
			{
				selector: '#change-answer-editor',
				content: '<h3>Answer Type</h3><p>Select the format of your answer options.</p>',
				position: { edge: 'bottom', align: 'center' },
				wizardStep: 1,
				totalWizardSteps: 3,
			},
			{
				selector: '#question_title',
				content: '<h3>Question Title</h3><p>Write the question you want to ask your users.</p>',
				position: { edge: 'bottom', align: 'center' },
				wizardStep: 1,
				totalWizardSteps: 3,
			},
			{
				selector: '.qsm-show-question-desc-box',
				content: '<h3>Edit Description</h3><p>Add extra instructions or context (optional).</p>',
				position: { edge: 'bottom', align: 'left' },
				wizardStep: 1,
				totalWizardSteps: 3,
			},
			{
				selector: '#answers',
				content: '<h3>Add Answers</h3><p>Add all possible answers for this question.</p>',
				position: { edge: 'bottom', align: 'left' },
				wizardStep: 1,
				totalWizardSteps: 3,
			},
			{
				selector: '#answers .answers-single:first .remove-answer-icon',
				content: '<h3>Add or remove answers</h3><p>Click <strong>+</strong> to add more answers.</p>',
				position: { edge: 'left', align: 'center' },
				wizardStep: 1,
				totalWizardSteps: 3,
				forceVisible: true,
				beforeOpen: function(){
					if ( !qsmTourState.addAnswerClicked ) {
						var $button = $( '#answers .answers-single:first .remove-answer-icon .qsm-add-answer-button' ).first();
						if ( $button.length ) {
							$button.trigger( 'click' );
						}
						qsmTourState.addAnswerClicked = true;
					}
				}
			},
			{
				selector: '#answers .answers-single:first .answer-text-div',
				content: '<h3>Answer Text</h3><p>Write the answer text for this option.</p>',
				position: { edge: 'left', align: 'center' },
				wizardStep: 1,
				totalWizardSteps: 3,
				forceVisible: true
			},
			{
				selector: '#answers .answers-single:first .qsm-answer-labels',
				content: '<h3>Select Labels</h3><p>Optional labels for advanced organizational use cases.</p>',
				position: { edge: 'right', align: 'center' },
				wizardStep: 1,
				totalWizardSteps: 3,
			},
			{
				selector: '#answers .answers-single:first .answer-point-div',
				content: '<h3>Points</h3><p>Assign points if this question is graded.</p>',
				position: { edge: 'left', align: 'center' },
				wizardStep: 1,
				totalWizardSteps: 3,
				forceVisible: true
			},
			{
				selector: '#answers .answers-single:first .answer-correct-div',
				content: '<h3>Mark Correct Answer</h3><p>Select the correct answer(s).</p>',
				position: { edge: 'left', align: 'center' },
				wizardStep: 1,
				totalWizardSteps: 3,
				forceVisible: true
			},
			{
				selector: '#answers .answers-single:nth-child(2) .remove-answer-icon',
				content: '<h3>Add or remove answers</h3><p>Click <strong>+</strong> to add more answers.</p>',
				position: { edge: 'left', align: 'center' },
				wizardStep: 1,
				totalWizardSteps: 3,
				forceVisible: true
			},
			{
				selector: '#answers .answers-single:nth-child(2) .answer-text-div',
				content: '<h3>Answer Text</h3><p>Write the answer text for this option.</p>',
				position: { edge: 'left', align: 'center' },
				wizardStep: 1,
				totalWizardSteps: 3,
				forceVisible: true
			},
			{
				selector: '#answers .answers-single:nth-child(2) .qsm-answer-labels',
				content: '<h3>Select Labels</h3><p>Optional labels for advanced organizational use cases.</p>',
				position: { edge: 'right', align: 'center' },
				wizardStep: 1,
				totalWizardSteps: 3,
			},
			{
				selector: '#answers .answers-single:nth-child(2) .answer-point-div',
				content: '<h3>Points</h3><p>Assign points if this question is graded.</p>',
				position: { edge: 'left', align: 'center' },
				wizardStep: 1,
				totalWizardSteps: 3,
				forceVisible: true
			},
			{
				selector: '#answers .answers-single:nth-child(2) .answer-correct-div',
				content: '<h3>Mark Correct Answer</h3><p>Select the correct answer(s).</p>',
				position: { edge: 'left', align: 'center' },
				wizardStep: 1,
				totalWizardSteps: 3,
				forceVisible: true
			},
			{
				selector: '#save-popup-button',
				content: '<h3>Save Question</h3><p>Click <strong>Save Question</strong> to save your first question.</p>',
				position: { edge: 'right', align: 'center' },
				wizardStep: 1,
				totalWizardSteps: 3,
				showNext: false,
				showSkip: true,
				showBack: true,
				beforeCloseOnClick: function(){
					qsmTourState.waitingForFirstQuestionSave = true;
				},
				closeOnClick: '#save-popup-button'
			}
		];

		qsmTourState.started = true;
		qsmTourState.index = startIndex || 0;
		qsmWaitForSelector( '.questionElements:visible #question_type', 5000, function(){
			qsmOpenTourStepWithDelay( qsmTourState.index );
		});
	}

	function qsmStartQuestionEnhancementsTour( startIndex ) {
		qsmTourState.tourName = 'question_enhancements';
		qsmTourState.onEnd = function(){
			qsmMarkSetupWizardCompleted();
			qsmTourState.forceSetupWizard = false;
			qsmClearSetupWizardState();
		};
		qsmTourState.steps = [
			{
				selector: '#correct_answer_info_area',
				content: '<h3>Enhance your question</h3><p>Help users learn and engage better</p><h4>Correct Answer Info</h4><p>Add an explanation to support the correct answer.</p>',
				position: { edge: 'top', align: 'center' },
				wizardStep: 3,
				totalWizardSteps: 3,
				skipText: 'Skip enhancements',
				beforeOpen: function(){
					var $area = $( '#correct_answer_info_area' );
					if ( $area.length ) {
						var $content = $area.find( '.qsm-toggle-box-content' ).first();
						if ( $content.length && !$content.is( ':visible' ) ) {
							$area.find( '.qsm-toggle-box-handle' ).first().trigger( 'click' );
						}
					}
				}
			},
			{
				selector: '#comments_area',
				content: '<h3>Comment Box</h3><p>Allow users to add comments for this question.</p>',
				position: { edge: 'top', align: 'center' },
				wizardStep: 3,
				totalWizardSteps: 3,
				skipText: 'Skip enhancements',
				beforeOpen: function(){
					var $area = $( '#comments_area' );
					if ( $area.length ) {
						var $content = $area.find( '.qsm-toggle-box-content' ).first();
						if ( $content.length && !$content.is( ':visible' ) ) {
							$area.find( '.qsm-toggle-box-handle' ).first().trigger( 'click' );
						}
					}
				}
			},
			{
				selector: '#hint_area',
				content: '<h3>Hint</h3><p>Provide a hint to guide users before answering.</p>',
				position: { edge: 'top', align: 'center' },
				wizardStep: 3,
				totalWizardSteps: 3,
				skipText: 'Skip enhancements',
				beforeOpen: function(){
					var $area = $( '#hint_area' );
					if ( $area.length ) {
						var $content = $area.find( '.qsm-toggle-box-content' ).first();
						if ( $content.length && !$content.is( ':visible' ) ) {
							$area.find( '.qsm-toggle-box-handle' ).first().trigger( 'click' );
						}
					}
				}
			},
			{
				selector: '#featureImagediv',
				content: '<h3>Featured Image</h3><p>Add an image to visually enhance this question.</p>',
				position: { edge: 'top', align: 'center' },
				wizardStep: 3,
				totalWizardSteps: 3,
				skipText: 'Skip enhancements'
			},
			{
				selector: '.questionElements',
				fallbackSelectors: [ 'body' ],
				content: '<h3>🎉 Congratulations!</h3><p>You’ve created your first quiz question.</p><p>You can now add more questions using the same steps</p>',
				position: { edge: 'left', align: 'center' },
				showSkip: false,
				showBack: false,
				doneText: 'Done'
			}
		];

		qsmTourState.started = true;
		qsmTourState.index = startIndex || 0;
		qsmWaitForSelector( '#answers', 5000, function(){
			qsmOpenTourStepWithDelay( qsmTourState.index );
		});
	}

	function qsmStartQuestionBehaviorTour( startIndex ) {
		qsmTourState.tourName = 'question_behavior';
		qsmTourState.onEnd = qsmStartQuestionEnhancementsTour;
		qsmTourState.steps = [
			{
				selector: '#answer_limit_area',
				content: '<h3>Control how this question works</h3><p>Decide how answers are selected and graded.</p><h4>Answer Limit</h4><p>Set how many answers users can select.</p>',
				position: { edge: 'top', align: 'center' },
				wizardStep: 2,
				totalWizardSteps: 3,
				skipText: 'Skip for now',
				beforeOpen: function(){
					var $area = $( '#answer_limit_area' );
					if ( $area.length ) {
						var $content = $area.find( '.qsm-toggle-box-content' ).first();
						if ( $content.length && !$content.is( ':visible' ) ) {
							$area.find( '.qsm-toggle-box-handle' ).first().trigger( 'click' );
						}
					}
				}
			},
			{
				selector: '#grading_mode_area',
				content: '<h3>Grading Mode</h3><p>Choose how this question should be graded.</p><p>In quizzes, grading is typically based on <strong>Correct / Incorrect</strong> and (optionally) <strong>Points</strong> per answer.</p>',
				position: { edge: 'top', align: 'left' },
				wizardStep: 2,
				totalWizardSteps: 3,
				skipText: 'Skip for now',
				beforeOpen: function(){
					var $area = $( '#grading_mode_area' );
					if ( $area.length ) {
						var $content = $area.find( '.qsm-toggle-box-content' ).first();
						if ( $content.length && !$content.is( ':visible' ) ) {
							$area.find( '.qsm-toggle-box-handle' ).first().trigger( 'click' );
						}
					}
				}
			},
			{
				selector: '#add_poll_type_area',
				content: '<h3>Add Poll Type</h3><p>Turn this into a poll to show how others responded.</p>',
				position: { edge: 'top', align: 'center' },
				wizardStep: 2,
				totalWizardSteps: 3,
				skipText: 'Skip for now',
				beforeOpen: function(){
					var $area = $( '#add_poll_type_area' );
					if ( $area.length ) {
						var $content = $area.find( '.qsm-toggle-box-content' ).first();
						if ( $content.length && !$content.is( ':visible' ) ) {
							$area.find( '.qsm-toggle-box-handle' ).first().trigger( 'click' );
						}
					}
				}
			},
			{
				selector: '.post-body-content',
				fallbackSelectors: [ 'body' ],
				content: '<h3>👍 Nice!</h3><p>Your question behavior is now set.</p>',
				position: { edge: 'top', align: 'left' },
				showSkip: false,
				showBack: false,
				doneText: 'Done'
			}
		];

		qsmTourState.started = true;
		qsmTourState.index = startIndex || 0;
		qsmWaitForSelector( '#answers', 5000, function(){
			qsmOpenTourStep( qsmTourState.index );
		});
	}

	function qsmScrollIntoView( $el ) {
		try {
			if ( $el && $el.length && $el[0] && typeof $el[0].scrollIntoView === 'function' ) {
				$el[0].scrollIntoView({ block: 'center', inline: 'nearest' });
			}
		} catch (e) {
			// ignore
		}
	}

	function qsmGetTourTargetForStep( step ) {
		var $target = $();
		if ( step && step.selector ) {
			$target = $( step.selector ).first();
			if ( $target.length ) {
				return $target;
			}
		}
		if ( step && step.fallbackSelectors && step.fallbackSelectors.length ) {
			for ( var i = 0; i < step.fallbackSelectors.length; i++ ) {
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
		var style = document.createElement( 'style' );
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

	function qsmEnsureWizardStepperStyles() {
		if ( document.getElementById( 'qsm-tour-wizard-stepper-style' ) ) {
			return;
		}
		var style = document.createElement( 'style' );
		style.id = 'qsm-tour-wizard-stepper-style';
		style.type = 'text/css';
		style.textContent = '' +
			'.wp-pointer.qsm-wizard-stepper-pointer .wp-pointer-content h3{' +
				'display:flex;align-items:center;gap:6px;' +
			'}' +
			'.wp-pointer.qsm-wizard-stepper-pointer .wp-pointer-content h3:before{' +
				'display:inline-flex;align-items:center;justify-content:center;' +
				'width:22px;height:22px;border-radius:999px;' +
				'background:#2271b1;color:#fff;font-size:12px;line-height:1;font-weight:700;' +
				'content:"";' +
			'}' +
			'.wp-pointer.qsm-wizard-stepper-pointer.qsm-wizard-step-1 .wp-pointer-content h3:before{content:"1/3";}' +
			'.wp-pointer.qsm-wizard-stepper-pointer.qsm-wizard-step-2 .wp-pointer-content h3:before{content:"2/3";}' +
			'.wp-pointer.qsm-wizard-stepper-pointer.qsm-wizard-step-3 .wp-pointer-content h3:before{content:"3/3";}';
		document.head.appendChild( style );
	}

	function qsmApplyWizardStepIndicator( $target, step, stepIndex ) {
		if ( !step || !step.wizardStep ) {
			return;
		}
		qsmEnsureWizardStepperStyles();

		var api = $target.data( 'wpPointer' );
		var $pointer = null;
		if ( api && api.pointer ) {
			$pointer = api.pointer;
		} else {
			$pointer = $( '.wp-pointer:visible' ).last();
		}
		if ( !$pointer || !$pointer.length ) {
			return;
		}

		$pointer.removeClass( 'qsm-wizard-stepper-pointer qsm-wizard-step-1 qsm-wizard-step-2 qsm-wizard-step-3' );
		$pointer.addClass( 'qsm-wizard-stepper-pointer' );
		$pointer.addClass( 'qsm-wizard-step-' + step.wizardStep );

		var $h3 = $pointer.find( '.wp-pointer-content h3' ).first();
		if ( !$h3.length ) {
			return;
		}

		// Remove any manually added counters inside the H3.
		$h3.find( 'span' ).remove();
		$h3.contents().filter(function(){
			return this.nodeType === 3;
		}).each(function(){
			this.nodeValue = this.nodeValue.replace( /\s+/g, ' ' );
		});
		$h3.text( $.trim( $h3.text() ) );

		// Step indicator is drawn using CSS (:before/:after) based on pointer classes.
	}

	function qsmApplySpotlight( $target ) {
		qsmEnsureSpotlightStyles();

		var $overlay = $( '#qsm-tour-spotlight-overlay' );
		if ( !$overlay.length ) {
			$overlay = $( '<div id="qsm-tour-spotlight-overlay" class="qsm-tour-spotlight-overlay"></div>' );
			$( 'body' ).append( $overlay );
		}

		var el = $target && $target[0] ? $target[0] : null;
		var prev = null;
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
			if ( typeof qsmTourState.onEnd === 'function' ) {
				var onEnd = qsmTourState.onEnd;
				qsmTourState.onEnd = null;
				setTimeout(function(){
					try { onEnd(); } catch (e) { /* ignore */ }
				}, 0);
			}
			return;
		}

		qsmTourState.index = stepIndex;
		var step = qsmTourState.steps[ stepIndex ];
		if ( step && step.wizardStep && qsmIsSetupWizardTourName( qsmTourState.tourName ) && !qsmIsSetupWizardCompleted() ) {
			qsmSetSetupWizardState({
				tourName: qsmTourState.tourName,
				stepIndex: stepIndex
			});
		}
		var $target = qsmGetTourTargetForStep( step );
		if ( !$target.length ) {
			qsmOpenTourStep( stepIndex + 1 );
			return;
		}

		if ( typeof step.beforeOpen === 'function' ) {
			try {
				step.beforeOpen( $target, stepIndex );
			} catch (e) {
				// ignore
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
		var cleanupFns = [];
		if ( step.forceVisible || !$target.is( ':visible' ) ) {
			cleanupFns.push( qsmMakeElementVisibleForTour( $target ) );
		}
		if ( false !== step.spotlight ) {
			cleanupFns.push( qsmApplySpotlight( $target ) );
		}
		if ( cleanupFns.length ) {
			qsmTourState.cleanupCurrent = function(){
				for ( var i = 0; i < cleanupFns.length; i++ ) {
					try { cleanupFns[i](); } catch (e) { /* ignore */ }
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
				var nextIndex = qsmTourState.nextIndexOnClose;
				qsmTourState.nextIndexOnClose = null;
				if ( qsmTourState.started && null !== nextIndex ) {
					qsmOpenTourStep( nextIndex );
					return;
				}
				qsmTourState.started = false;
				if ( typeof qsmTourState.onEnd === 'function' ) {
					var onEnd = qsmTourState.onEnd;
					qsmTourState.onEnd = null;
					setTimeout(function(){
						try { onEnd(); } catch (e) { /* ignore */ }
					}, 0);
				}
			},
			buttons: function( event, t ) {
				var $buttons = $( '<div class="qsm-admin-tour-buttons"></div>' );
				var $left = $( '<div class="qsm-admin-tour-buttons-left"></div>' );
				var $right = $( '<div class="qsm-admin-tour-buttons-right"></div>' );
				var totalSteps = qsmTourState.steps.length;
				var currentStep = stepIndex + 1;
				var $counter = $( '<span class="qsm-admin-tour-counter"></span>' );
				var $back = $( '<button type="button" class="button">Back</button>' );
				var $skip = $( '<button type="button" class="button">Skip</button>' );
				var $next = $( '<button type="button" class="button button-primary">Next</button>' );
				var $done = $( '<button type="button" class="button button-primary">Done</button>' );
				var isLastStep = ( stepIndex >= qsmTourState.steps.length - 1 );
				var showBack = ( true === step.showBack ) || isLastStep;
				var showSkip = ( false !== step.showSkip ) && !isLastStep;
				var showNext = ( false !== step.showNext ) && !isLastStep;
				var showDone = isLastStep;
				if ( step.doneText ) {
					$done.text( step.doneText );
				}
				if ( step.skipText ) {
					$skip.text( step.skipText );
				}

				$back.on( 'click', function(e){
					e.preventDefault();
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

				if ( step.wizardStep || currentStep == totalSteps ) {
					$counter.text( 'Step ' + currentStep + ' of ' + totalSteps );
					$left.append( $counter );
				}

				if ( showSkip ) {
					$right.append( $skip );
				}
				if ( showBack ) {
					$right.append( $back );
				}
				if ( showDone ) {
					$right.append( $done );
				} else if ( showNext ) {
					$right.append( $next );
				}

				$buttons.css({
					display: 'flex',
					alignItems: 'center',
					justifyContent: 'space-between'
				});
				$right.css({
					display: 'flex',
					gap: '6px',
					alignItems: 'center'
				});
				$buttons.append( $left );
				$buttons.append( $right );

				return $buttons;
			}
		}).pointer('open');

		// Apply wizard step indicator classes after pointer opens.
		setTimeout(function(){
			qsmApplyWizardStepIndicator( $target, step, stepIndex );
		}, 0);

		if ( step.closeOnClick ) {
			$(document).one( 'click.qsmTourCloseOnClick', step.closeOnClick, function(){
				if ( typeof step.beforeCloseOnClick === 'function' ) {
					try { step.beforeCloseOnClick(); } catch (e) { /* ignore */ }
				}
				try {
					$target.pointer('close');
				} catch (e) {
					// ignore
				}
			});
		}

		// Fine-tune pointer arrow position (beyond edge/align) if requested.
		setTimeout(function(){
			var api = $target.data( 'wpPointer' );
			var $pointer = null;
			if ( api && api.pointer ) {
				$pointer = api.pointer;
			} else {
				$pointer = $( '.wp-pointer:visible' ).last();
			}
			if ( !$pointer || !$pointer.length ) {
				return;
			}
			if ( step.pointerClass ) {
				$pointer.addClass( step.pointerClass );
			}
			if ( step.arrowCss && typeof step.arrowCss === 'object' ) {
				$pointer.find( '.wp-pointer-arrow' ).css( step.arrowCss );
			}
		}, 0);
	}

	function qsmWaitForSelector( selector, timeoutMs, cb ) {
		var start = Date.now();
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
		if ( !$('body').hasClass('admin_page_mlw_quiz_options') ) {
			return;
		}

		$(document).on('click', '#qsm-show-setup-wizard-again', function(e){
			e.preventDefault();
			qsmResetSetupWizardCompleted();
			qsmTourState.forceSetupWizard = true;
			qsmTourState.pendingFirstQuestionTour = true;
		});

		if ( !$( '#qsm-show-setup-wizard-again' ).length ) {
			var $anchor = $( '#qsm-start-admin-tour' );
			if ( $anchor.length ) {
				$( '<a href="#" id="qsm-show-setup-wizard-again" style="margin-left:10px;">Show setup wizard again</a>' ).insertAfter( $anchor );
			}
		}

		$(document).on('click', '.questions .new-question-button', function(){
			qsmTourState.pendingFirstQuestionTour = qsmShouldStartSetupWizard();
		});
		$(document).on('qsm_open_edit_popup', function(){
			if ( qsmTourState.started ) {
				return;
			}
			if ( !qsmIsSetupWizardCompleted() ) {
				var wizardState = qsmGetSetupWizardState();
				if ( wizardState && wizardState.tourName ) {
					qsmStartWizardTourByName( wizardState.tourName, wizardState.stepIndex || 0 );
					return;
				}
			}
			if ( qsmTourState.pendingFirstQuestionTour ) {
				qsmTourState.pendingFirstQuestionTour = false;
				qsmStartFirstQuestionTour();
			}
		});
		$(document).on('qsm_admin_question_saved_success', function(){
			if ( qsmTourState.tourName !== 'first_question' || ( !qsmTourState.started && !qsmTourState.waitingForFirstQuestionSave ) ) {
				return;
			}
			// Do NOT mark wizard complete here. Completion is marked after enhancements tour ends.
			qsmTourState.waitingForFirstQuestionSave = false;
			qsmSetSetupWizardState({ tourName: 'first_question', stepIndex: 0 });
			qsmTourState.steps = [
				{
					selector: '#save-popup-button',
					content: '<h3>✅ Great start!</h3><p>You’ve successfully created your first question.</p>',
					position: { edge: 'left', align: 'center' },
					doneText: 'Done',
					showSkip: false,
					showBack: false
				}
			];
			qsmTourState.index = 0;
			qsmTourState.onEnd = qsmStartQuestionBehaviorTour;
			qsmTourState.started = true;
			qsmOpenTourStepWithDelay( 0 );
		});

		$(document).on('qsm_start_question_behavior_tour', function(){
			if ( qsmTourState.started ) {
				return;
			}
			qsmStartQuestionBehaviorTour();
		});
	});
})(jQuery);
