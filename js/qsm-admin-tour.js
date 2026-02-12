(function($){
	'use strict';

	var QSM_SETUP_WIZARD_STORAGE_KEY = 'qsm_setup_wizard_completed';
	var QSM_SETUP_WIZARD_STATE_STORAGE_KEY = 'qsm_setup_wizard_state';

	var QSM_TOUR_START_DELAY = 400;
	var QSM_TOTAL_TOUR_STEPS = 9;
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
		addAnswerClicked: false,
		waitingForEnhancementsSave: false,
		enhancementFinalStep: null,
		manualStart: false
	};

	var QSM_TOUR_ACTIVE_QUESTION_TARGETS = [
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
		for ( var i = 0; i < QSM_TOUR_ACTIVE_QUESTION_TARGETS.length; i++ ) {
			var selector = QSM_TOUR_ACTIVE_QUESTION_TARGETS[ i ];
			if ( !selector ) {
				continue;
			}
			var $candidate = $( selector ).first();
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
		var style = document.createElement( 'style' );
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

	function qsmDisableFutureSetupTours() {
		qsmMarkSetupWizardCompleted();
		qsmTourState.forceSetupWizard = false;
		qsmTourState.pendingFirstQuestionTour = false;
		qsmClearSetupWizardState();
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

	window.qsmIsSetupWizardActive = function() {
		return Boolean( qsmTourState.started || qsmTourState.pendingFirstQuestionTour || qsmTourState.forceSetupWizard || qsmShouldStartSetupWizard() );
	};

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

	function qsmAnnotateGlobalSteps( steps, startingIndex ) {
		var marker = startingIndex || 1;
		steps.forEach( function( step ) {
			step.globalStep = marker++;
		} );
	}

	function qsmStartFirstQuestionTour( startIndex ) {
		qsmTourState.tourName = 'first_question';
		qsmTourState.waitingForFirstQuestionSave = false;
		qsmTourState.onEnd = null;
		qsmTourState.steps = [
			{
				selector: '.questionElements:visible #question_type',
				fallbackSelectors: [ '.questionElements:visible', 'body' ],
				content: '<h3>Create your first question</h3><p>Choose your question type.</p>',
				position: { edge: 'bottom', align: 'center' },
				showBack: false
			},
			{
				selector: '#question_title',
				content: '<h3>Question Title</h3><p>Write the question you want to ask your users.</p>',
				position: { edge: 'bottom', align: 'center' },
			},
			{
				selector: '#answers',
				content: '<h3>Add Answers</h3><p>Add all possible answers for this question.</p>' +
				'<p>Use the <b>+</b> and <b>-</b> buttons to add or remove answers.</p>' +
				'<p>Assign <b>points</b> and mark the <b>correct answer</b>.</p>' +
				'<p>Select the appropriate <b>label</b> (Optional).</p>',
				position: { edge: 'bottom', align: 'left' },
				pointerClass: 'qsm-pointer-wide',
				beforeOpen: function(){
					var currentAnswers = $('#answers').find('.answers-single');
					if (currentAnswers.length) {
						for (var i = currentAnswers.length; i < 4; i++) {
							$('#new-answer-button').trigger('click');
						}
					} else {
						$('#new-answer-button').trigger('click').trigger('click').trigger('click').trigger('click');
					}
				}
			},
			{
				selector: '#save-popup-button',
				content: '<h3>Save Question</h3><p>Click <strong>Save Question</strong> to save your first question.</p>',
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
		qsmTourState.index = startIndex || 0;
		qsmWaitForSelector( '.questionElements:visible #question_type', 5000, function(){
			qsmOpenTourStepWithDelay( qsmTourState.index );
		});
	}

	function qsmStartQuestionEnhancementsTour( startIndex ) {
		qsmTourState.tourName = 'question_enhancements';
		qsmTourState.waitingForEnhancementsSave = false;
		qsmTourState.onEnd = function(){
			qsmMarkSetupWizardCompleted();
			qsmTourState.forceSetupWizard = false;
			qsmClearSetupWizardState();
		};
		qsmTourState.enhancementFinalStep = {
			selector: '.questions .question.opened',
			fallbackSelectors: [ '.questions .question.opened', '.question.opened', '.questionElements', '#save-popup-button', '.questions .question:first', 'body' ],
			content: '<h3>🎉 Congratulations!</h3><p>Your advanced settings have been saved successfully.</p><p>The question logic and behavior are now updated.</p>',
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
				content: '<h3>Featured Image (Optional)</h3><p>Add an image to visually enhance this question.</p>',
				position: { edge: 'right', align: 'center' },
				skipText: 'Skip'
			},
			{
				selector: '#categorydiv',
				content: '<h3>Category (Optional)</h3><p>Assign this question to one or more categories to organize, filter, and reuse it across quizzes.</p>',
				position: { edge: 'right', align: 'center' },
				skipText: 'Skip'
			},
			{
				selector: '#submitdiv .ui-sortable-handle',
				content: '<h3>Published / Draft</h3><p>Use the toggle to switch between Draft and Published.</p><p>Set it to Published to make the question available in quizzes, or keep it as Draft to continue editing.</p>',
				position: { edge: 'right', align: 'center' },
				skipText: 'Skip',
				spotlight: false
			},
			{
				selector: '.qsm-question-misc-options.advanced-content',
				content: '<h3>Advanced Settings</h3><p>Here you can configure advanced settings for this question.</p><p>Use this section to control evaluation and learner feedback.</p>',
				position: { edge: 'bottom', align: 'center' },
				skipText: 'Skip',
			},
			{
				selector: '#save-popup-button',
				content: '<h3>Save your updates</h3><p>Click “Save Question” to apply your changes and complete the setup</p>',
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
		qsmTourState.index = startIndex || 0;
		qsmWaitForSelector( '#answers', 5000, function(){
			qsmOpenTourStepWithDelay( qsmTourState.index );
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
			qsmTourState.manualStart = false;
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
		if ( !qsmTourState.manualStart && qsmIsSetupWizardTourName( qsmTourState.tourName ) && !qsmIsSetupWizardCompleted() ) {
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
				qsmTourState.manualStart = false;
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
				var totalSteps = QSM_TOTAL_TOUR_STEPS;
				var currentStep = step.globalStep || (stepIndex + 1);
				var $counter = $( '<span class="qsm-admin-tour-counter"></span>' );
				var $back = $( '<button type="button" class="button">Prev</button>' );
				var $skip = $( '<button type="button" class="button">Skip</button>' );
				var $next = $( '<button type="button" class="button button-primary">Next</button>' );
				var $done = $( '<button type="button" class="button button-primary">Done</button>' );
				var isLastStep = ( stepIndex >= qsmTourState.steps.length - 1 );
				var showBack = ( stepIndex > 0 ) || ( true === step.showBack );
				var showSkip = ( false !== step.showSkip ) && !isLastStep;
				var showNext = ( false !== step.showNext ) && !isLastStep;
				var showDone = ( stepIndex >= qsmTourState.steps.length - 1 ) && !step.hideDoneButton;
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
						var $custom = $( '<button type="button"></button>' );
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
					var counterTotal = step.globalStep <= 4 ? 4 : totalSteps;
					$counter.text( 'Step ' + step.globalStep + ' of ' + counterTotal );
					$left.append( $counter );
				}

				if ( showSkip ) {
					$right.append( $skip );
				}
				if ( showBack ) {
					$right.append( $back );
				}
				if ( showDone ) {
					// $right.append( $done );
				} else if ( showNext ) {
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
				qsmEnsurePointerStyles();
				$pointer.addClass( step.pointerClass );
				if ( step.pointerClass.indexOf( 'qsm-pointer-congrats' ) !== -1 && !$pointer.find('.qsm-pointer-congrats-close-target').length ) {
					var $closeButton = $( '<button type="button" class="qsm-pointer-congrats-close-target" aria-label="Close"></button>' );
					$closeButton.on( 'click', function( e ) {
						e.preventDefault();
						qsmTourState.started = false;
						qsmTourState.nextIndexOnClose = null;
						try { $target.pointer('close'); } catch (err) { /* ignore */ }
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
			var buttonCount = $right.find('button').length;
			if ( buttonCount > 0 ) {
				$right[0].style.minWidth = '40%';
				$right[0].style.width = (buttonCount < 3 ? '40%' : '60%');
			}
		}
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

		$(document).on('click', '#qsm-start-admin-tour', function(e){
			e.preventDefault();
			$('.qsm-help-tab-dropdown-list, .qsm-help-tab-handle').removeClass('opened');
			qsmTourState.manualStart = true;
			qsmTourState.forceSetupWizard = false;
			qsmTourState.pendingFirstQuestionTour = false;
			qsmTourState.started = false;
			qsmTourState.waitingForFirstQuestionSave = false;
			qsmTourState.onEnd = null;
			var hasEditorVisible = $('.questionElements:visible #question_type').length > 0;
			if ( hasEditorVisible ) {
				qsmStartFirstQuestionTour();
				return;
			}
			qsmTourState.pendingFirstQuestionTour = true;
			var $editButton = $('.questions .question:first .edit-question-button');
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
			if ( qsmTourState.tourName === 'first_question' && ( qsmTourState.started || qsmTourState.waitingForFirstQuestionSave ) ) {
				// Do NOT mark wizard complete here. Completion is marked after enhancements tour ends.
				qsmTourState.waitingForFirstQuestionSave = false;
				qsmSetSetupWizardState({ tourName: 'first_question', stepIndex: 0 });
				qsmTourState.steps = [
					{
						selector: '.questions .question.opened',
						fallbackSelectors: [ '.questions .question.opened', '.question.opened', '.questionElements', '#save-popup-button', '.questions .question:first', 'body' ],
						content: '<h3>✅ Great start!</h3><p><b>Your question is ready with basic settings.</b></p><p>Now you can customize logic and behavior to unlock its full potential.</p>',
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
