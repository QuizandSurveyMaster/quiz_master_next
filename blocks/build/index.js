/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/edit.js":
/*!*********************!*\
  !*** ./src/edit.js ***!
  \*********************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ Edit; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_editor__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/editor */ "@wordpress/editor");
/* harmony import */ var _wordpress_editor__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_editor__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_core_data__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/core-data */ "@wordpress/core-data");
/* harmony import */ var _wordpress_core_data__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_core_data__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./editor.scss */ "./src/editor.scss");
/* harmony import */ var _helper__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./helper */ "./src/helper.js");











function Edit(props) {
  //check for QSM initialize data
  if ('undefined' === typeof qsmBlockData) {
    return null;
  }
  const {
    className,
    attributes,
    setAttributes,
    isSelected,
    clientId
  } = props;
  /*
  "	quiz_name": {
  		"type": "string",
  		"default": ""
  	},
  	"quiz_featured_image": {
  		"type": "string",
  		"default": ""
  	},
  	"form_type": {
  		"type": "string",
  		"default": ""
  	},
  	"system": {
  		"type": "string",
  		"default": ""
  	},
  	"timer_limit": {
  		"type": "string",
  		"default": ""
  	},
  	"pagination": {
  		"type": "string",
  		"default": ""
  	},
  	"enable_pagination_quiz": {
  		"type": "number",
  		"default": 0
  	},
  	"progress_bar": {
  		"type": "number",
  		"default": 0
  	},
  	"require_log_in": {
  		"type": "number",
  		"default": 0
  	},
  	"disable_first_page": {
  		"type": "number",
  		"default": 0
  	},
  	"comment_section": {
  		"type": "number",
  		"default": 1
  	}
  */
  const {
    quizID
  } = attributes;
  const [quizAttr, setQuizAttr] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(qsmBlockData.globalQuizsetting);
  const [quizList, setQuizList] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(qsmBlockData.QSMQuizList);
  const [createQuiz, setCreateQuiz] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const [saveQuiz, setSaveQuiz] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const [showAdvanceOption, setShowAdvanceOption] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const [quizTemplate, setQuizTemplate] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)([]);
  const quizOptions = qsmBlockData.quizOptions;
  /**Initialize block from server */
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    let shouldSetQSMAttr = true;
    if (shouldSetQSMAttr) {
      if (!(0,_helper__WEBPACK_IMPORTED_MODULE_9__.qsmIsEmpty)(quizID) && 0 < quizID && ((0,_helper__WEBPACK_IMPORTED_MODULE_9__.qsmIsEmpty)(quizAttr) || (0,_helper__WEBPACK_IMPORTED_MODULE_9__.qsmIsEmpty)(quizAttr?.quizID) || quizID != quizAttr.quiz_id)) {
        _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
          path: '/quiz-survey-master/v1/quiz/structure',
          method: 'POST',
          data: {
            quizID: quizID
          }
        }).then(res => {
          console.log(res);
          if ('success' == res.status) {
            let result = res.result;
            setQuizAttr({
              ...quizAttr,
              ...result
            });
            if (!(0,_helper__WEBPACK_IMPORTED_MODULE_9__.qsmIsEmpty)(result.qpages)) {
              let quizTemp = [];
              result.qpages.forEach(page => {
                let questions = [];
                if (!(0,_helper__WEBPACK_IMPORTED_MODULE_9__.qsmIsEmpty)(page.question_arr)) {
                  page.question_arr.forEach(question => {
                    if (!(0,_helper__WEBPACK_IMPORTED_MODULE_9__.qsmIsEmpty)(question)) {
                      let answers = [];
                      //answers options blocks
                      if (!(0,_helper__WEBPACK_IMPORTED_MODULE_9__.qsmIsEmpty)(question.answers) && 0 < question.answers.length) {
                        question.answers.forEach((answer, aIndex) => {
                          answers.push(['qsm/quiz-answer-option', {
                            optionID: aIndex,
                            content: answer[0],
                            points: answer[1],
                            isCorrect: answer[2]
                          }]);
                        });
                      }
                      //question blocks
                      questions.push(['qsm/quiz-question', {
                        questionID: question.question_id,
                        type: question.question_type_new,
                        answerEditor: question.settings.answerEditor,
                        title: question.settings.question_title,
                        description: question.question_name,
                        required: question.settings.required,
                        hint: question.hints,
                        answers: question.answers,
                        correctAnswerInfo: question.question_answer_info,
                        category: question.category,
                        multicategories: question.multicategories,
                        commentBox: question.comments,
                        matchAnswer: question.settings.matchAnswer,
                        featureImageID: question.settings.featureImageID,
                        featureImageSrc: question.settings.featureImageSrc,
                        settings: question.settings
                      }, answers]);
                    }
                  });
                }
                //console.log("page",page);
                quizTemp.push(['qsm/quiz-page', {
                  pageID: page.id,
                  pageKey: page.pagekey,
                  hidePrevBtn: page.hide_prevbtn,
                  quizID: page.quizID
                }, questions]);
              });
              setQuizTemplate(quizTemp);
            }
            // QSM_QUIZ = [
            // 	[

            // 	]
            // ];
          } else {
            console.log("error " + res.msg);
          }
        });
      }
    }

    //cleanup
    return () => {
      shouldSetQSMAttr = false;
    };
  }, [quizID]);
  const feedbackIcon = () => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.Icon, {
    icon: () => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
      width: "24",
      height: "24",
      viewBox: "0 0 24 24",
      fill: "none",
      xmlns: "http://www.w3.org/2000/svg",
      color: "#ffffff"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("rect", {
      x: "2.75",
      y: "3.75",
      width: "18.5",
      height: "16.5",
      stroke: "#0EA489",
      strokeWidth: "1.5"
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("rect", {
      x: "6",
      y: "7",
      width: "12",
      height: "1",
      fill: "#0EA489"
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("rect", {
      x: "6",
      y: "11",
      width: "12",
      height: "1",
      fill: "#0EA489"
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("rect", {
      x: "6",
      y: "15",
      width: "12",
      height: "1",
      fill: "#0EA489"
    }))
  });
  const quizPlaceholder = () => {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.Placeholder, {
      icon: feedbackIcon,
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Quiz And Survey Master')
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, !(0,_helper__WEBPACK_IMPORTED_MODULE_9__.qsmIsEmpty)(quizList) && 0 < quizList.length && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "qsm-placeholder-select-create-quiz"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.SelectControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('', 'quiz-master-next'),
      value: quizID,
      options: quizList,
      onChange: quizID => setAttributes({
        quizID
      }),
      disabled: createQuiz,
      __nextHasNoMarginBottom: true
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('OR', 'quiz-master-next')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.Button, {
      variant: "link",
      onClick: () => setCreateQuiz(!createQuiz)
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Add New', 'quiz-master-next'))), ((0,_helper__WEBPACK_IMPORTED_MODULE_9__.qsmIsEmpty)(quizList) || createQuiz) && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.__experimentalVStack, {
      spacing: "3",
      className: "qsm-placeholder-quiz-create-form"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.TextControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Quiz Name *', 'quiz-master-next'),
      help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Enter a name for this Quiz', 'quiz-master-next'),
      value: quizAttr?.quiz_name || '',
      onChange: val => setQuizAttributes(val, 'quiz_name')
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.Button, {
      variant: "link",
      onClick: () => setShowAdvanceOption(!showAdvanceOption)
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Advance options', 'quiz-master-next')), showAdvanceOption && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.SelectControl, {
      label: quizOptions?.form_type?.label,
      value: quizAttr?.form_type,
      options: quizOptions?.form_type?.options,
      onChange: val => setQuizAttributes(val, 'form_type'),
      __nextHasNoMarginBottom: true
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.SelectControl, {
      label: quizOptions?.system?.label,
      value: quizAttr?.system,
      options: quizOptions?.system?.options,
      onChange: val => setQuizAttributes(val, 'system'),
      help: quizOptions?.system?.help,
      __nextHasNoMarginBottom: true
    }), ['timer_limit', 'pagination'].map(item => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.TextControl, {
      type: "number",
      label: quizOptions?.[item]?.label,
      help: quizOptions?.[item]?.help,
      value: (0,_helper__WEBPACK_IMPORTED_MODULE_9__.qsmIsEmpty)(quizAttr[item]) ? 0 : quizAttr[item],
      onChange: val => setQuizAttributes(val, item)
    })), ['enable_contact_form', 'enable_pagination_quiz', 'show_question_featured_image_in_result', 'progress_bar', 'require_log_in', 'disable_first_page', 'comment_section'].map(item => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.ToggleControl, {
      label: quizOptions?.[item]?.label,
      help: quizOptions?.[item]?.help,
      checked: !(0,_helper__WEBPACK_IMPORTED_MODULE_9__.qsmIsEmpty)(quizAttr[item]) && '1' == quizAttr[item],
      onChange: () => setQuizAttributes(!(0,_helper__WEBPACK_IMPORTED_MODULE_9__.qsmIsEmpty)(quizAttr[item]) && '1' == quizAttr[item] ? 0 : 1, item)
    }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.Button, {
      variant: "primary",
      disabled: saveQuiz || (0,_helper__WEBPACK_IMPORTED_MODULE_9__.qsmIsEmpty)(quizAttr.quiz_name),
      onClick: () => createNewQuiz()
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Create Quiz', 'quiz-master-next')))));
  };
  const setQuizAttributes = (value, attr_name) => {
    let newAttr = quizAttr;
    newAttr[attr_name] = value;
    setQuizAttr({
      ...newAttr
    });
  };
  const createNewQuiz = () => {};
  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.useBlockProps)();
  const innerBlocksProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.useInnerBlocksProps)(blockProps, {
    template: quizTemplate,
    allowedBlocks: ['qsm/quiz-page']
  });
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.InspectorControls, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Quiz settings', 'quiz-master-next'),
    initialOpen: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Quiz Name *', 'quiz-master-next'),
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Enter a name for this Quiz', 'quiz-master-next'),
    value: quizAttr?.quiz_name || '',
    onChange: val => setQuizAttributes(val, 'quiz_name')
  }))), (0,_helper__WEBPACK_IMPORTED_MODULE_9__.qsmIsEmpty)(quizID) || '0' == quizID ? quizPlaceholder() : (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    ...innerBlocksProps
  }));
}

/***/ }),

/***/ "./src/helper.js":
/*!***********************!*\
  !*** ./src/helper.js ***!
  \***********************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   qsmIsEmpty: function() { return /* binding */ qsmIsEmpty; },
/* harmony export */   qsmSanitizeName: function() { return /* binding */ qsmSanitizeName; },
/* harmony export */   qsmStripTags: function() { return /* binding */ qsmStripTags; }
/* harmony export */ });
//Check if undefined, null, empty
const qsmIsEmpty = data => 'undefined' === typeof data || null === data || '' === data;
const qsmSanitizeName = name => {
  if (qsmIsEmpty(name)) {
    name = '';
  } else {
    name = name.toLowerCase().replace(/ /g, '_');
    name = name.replace(/\W/g, '');
  }
  return name;
};

// Remove anchor tags from button text content.
const qsmStripTags = text => text.replace(/<\/?a[^>]*>/g, '');

/***/ }),

/***/ "./src/index.js":
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./style.scss */ "./src/style.scss");
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./edit */ "./src/edit.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./block.json */ "./src/block.json");




const save = props => null;
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_3__.name, {
  /**
   * @see ./edit.js
   */
  edit: _edit__WEBPACK_IMPORTED_MODULE_2__["default"],
  save: save
});

/***/ }),

/***/ "./src/editor.scss":
/*!*************************!*\
  !*** ./src/editor.scss ***!
  \*************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./src/style.scss":
/*!************************!*\
  !*** ./src/style.scss ***!
  \************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "@wordpress/api-fetch":
/*!**********************************!*\
  !*** external ["wp","apiFetch"] ***!
  \**********************************/
/***/ (function(module) {

module.exports = window["wp"]["apiFetch"];

/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ (function(module) {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ (function(module) {

module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ (function(module) {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/core-data":
/*!**********************************!*\
  !*** external ["wp","coreData"] ***!
  \**********************************/
/***/ (function(module) {

module.exports = window["wp"]["coreData"];

/***/ }),

/***/ "@wordpress/data":
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
/***/ (function(module) {

module.exports = window["wp"]["data"];

/***/ }),

/***/ "@wordpress/editor":
/*!********************************!*\
  !*** external ["wp","editor"] ***!
  \********************************/
/***/ (function(module) {

module.exports = window["wp"]["editor"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ (function(module) {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ (function(module) {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "./src/block.json":
/*!************************!*\
  !*** ./src/block.json ***!
  \************************/
/***/ (function(module) {

module.exports = JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"qsm/quiz","version":"0.1.0","title":"QSM Quiz","category":"widgets","icon":"feedback","description":"Easily and quickly add quizzes and surveys to your website.","attributes":{"quizID":{"type":"string","default":"0"}},"providesContext":{"quiz-master-next/quizID":"quizID"},"example":{},"supports":{"html":false},"textdomain":"main-block","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css","render":"file:./render.php","viewScript":"file:./view.js"}');

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	!function() {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = function(result, chunkIds, fn, priority) {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var chunkIds = deferred[i][0];
/******/ 				var fn = deferred[i][1];
/******/ 				var priority = deferred[i][2];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every(function(key) { return __webpack_require__.O[key](chunkIds[j]); })) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	!function() {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"index": 0,
/******/ 			"./style-index": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = function(chunkId) { return installedChunks[chunkId] === 0; };
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = function(parentChunkLoadingFunction, data) {
/******/ 			var chunkIds = data[0];
/******/ 			var moreModules = data[1];
/******/ 			var runtime = data[2];
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some(function(id) { return installedChunks[id] !== 0; })) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkqsm"] = self["webpackChunkqsm"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	}();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["./style-index"], function() { return __webpack_require__("./src/index.js"); })
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map