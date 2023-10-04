/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

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

/***/ "./src/question/edit.js":
/*!******************************!*\
  !*** ./src/question/edit.js ***!
  \******************************/
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
/* harmony import */ var _helper__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ../helper */ "./src/helper.js");










/**
 * https://github.com/WordPress/gutenberg/blob/HEAD/packages/block-editor/src/components/rich-text/README.md#allowedformats-array
 *  
 */

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
    clientId,
    context
  } = props;

  /** https://github.com/WordPress/gutenberg/issues/22282  */
  const isParentOfSelectedBlock = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_6__.useSelect)(select => isSelected || select('core/block-editor').hasSelectedInnerBlock(clientId, true));
  const quizID = context['quiz-master-next/quizID'];
  const pageID = context['quiz-master-next/pageID'];
  const {
    questionID,
    type,
    description,
    title,
    correctAnswerInfo,
    commentBox,
    category,
    multicategories,
    hint,
    featureImageID,
    featureImageSrc,
    answers,
    answerEditor,
    matchAnswer,
    otherSettings,
    settings
  } = attributes;
  const [quesAttr, setQuesAttr] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(settings);

  /**Initialize block from server */
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    let shouldSetQSMAttr = true;
    if (shouldSetQSMAttr) {}

    //cleanup
    return () => {
      shouldSetQSMAttr = false;
    };
  }, [quizID]);
  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.useBlockProps)({
    className: isParentOfSelectedBlock ? ' in-editing-mode' : ''
  });

  //Decode htmlspecialchars
  const decodeHtml = html => {
    var txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
  };
  const QUESTION_TEMPLATE = [['qsm/quiz-answer-option', {
    optionID: '0'
  }], ['qsm/quiz-answer-option', {
    optionID: '1'
  }]];
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.InspectorControls, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Question settings', 'quiz-master-next'),
    initialOpen: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", {
    className: "block-editor-block-card__title"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('ID', 'quiz-master-next') + ': ' + questionID), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.SelectControl, {
    label: qsmBlockData.question_type.label,
    value: type || qsmBlockData.question_type.default,
    onChange: type => setAttributes({
      type
    }),
    __nextHasNoMarginBottom: true
  }, !(0,_helper__WEBPACK_IMPORTED_MODULE_8__.qsmIsEmpty)(qsmBlockData.question_type.options) && qsmBlockData.question_type.options.map(qtypes => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("optgroup", {
    label: qtypes.category,
    key: "qtypes" + qtypes.category
  }, qtypes.types.map(qtype => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: qtype.slug,
    key: "qtype" + qtype.slug
  }, qtype.name))))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.SelectControl, {
    label: qsmBlockData.answerEditor.label,
    value: answerEditor || qsmBlockData.answerEditor.default,
    options: qsmBlockData.answerEditor.options,
    onChange: answerEditor => setAttributes({
      answerEditor
    }),
    __nextHasNoMarginBottom: true
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.PanelBody, {
    title: qsmBlockData.commentBox.heading
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.SelectControl, {
    label: qsmBlockData.commentBox.label,
    value: commentBox || qsmBlockData.commentBox.default,
    options: qsmBlockData.commentBox.options,
    onChange: commentBox => setAttributes({
      commentBox
    }),
    __nextHasNoMarginBottom: true
  }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    ...blockProps
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.RichText, {
    tagName: "h4",
    "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Question title', 'quiz-master-next'),
    placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Type your question here', 'quiz-master-next'),
    value: title,
    onChange: title => setAttributes({
      title: (0,_helper__WEBPACK_IMPORTED_MODULE_8__.qsmStripTags)(title)
    }),
    allowedFormats: [],
    withoutInteractiveFormatting: true,
    className: 'qsm-question-title'
  }), isParentOfSelectedBlock && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.RichText, {
    tagName: "p",
    "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Question description', 'quiz-master-next'),
    placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Description goes here', 'quiz-master-next'),
    value: decodeHtml(description),
    onChange: description => setAttributes({
      description
    }),
    className: 'qsm-question-description',
    __unstableEmbedURLOnPaste: true,
    __unstableAllowPrefixTransformations: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.InnerBlocks, {
    allowedBlocks: ['qsm/quiz-answer-option'],
    template: QUESTION_TEMPLATE
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.RichText, {
    tagName: "p",
    "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Correct Answer Info', 'quiz-master-next'),
    placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Correct answer info goes here', 'quiz-master-next'),
    value: decodeHtml(correctAnswerInfo),
    onChange: correctAnswerInfo => setAttributes({
      correctAnswerInfo
    }),
    className: 'qsm-question-correct-answer-info',
    __unstableEmbedURLOnPaste: true,
    __unstableAllowPrefixTransformations: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.RichText, {
    tagName: "p",
    "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Hint', 'quiz-master-next'),
    placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('hint goes here', 'quiz-master-next'),
    value: hint,
    onChange: hint => setAttributes({
      hint: (0,_helper__WEBPACK_IMPORTED_MODULE_8__.qsmStripTags)(hint)
    }),
    allowedFormats: [],
    withoutInteractiveFormatting: true,
    className: 'qsm-question-hint'
  }))));
}

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

/***/ "./src/question/block.json":
/*!*********************************!*\
  !*** ./src/question/block.json ***!
  \*********************************/
/***/ (function(module) {

module.exports = JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"qsm/quiz-question","version":"0.1.0","title":"Question","category":"widgets","parent":["qsm/quiz-page"],"icon":"feedback","description":"QSM Quiz Question","attributes":{"questionID":{"type":"string","default":"0"},"type":{"type":"string","default":"0"},"description":{"type":"string","source":"html","selector":"p","default":""},"title":{"type":"string","default":""},"correctAnswerInfo":{"type":"string","source":"html","selector":"p","default":""},"commentBox":{"type":"string","default":"0"},"category":{"type":"number"},"multicategories":{"type":"array"},"hint":{"type":"string"},"featureImageID":{"type":"number"},"featureImageSrc":{"type":"string"},"answers":{"type":"array"},"answerEditor":{"type":"string","default":"text"},"matchAnswer":{"type":"string","default":"random"},"required":{"type":"string","default":"0"},"otherSettings":{"type":"object","default":{}},"settings":{"type":"object","default":{}}},"usesContext":["quiz-master-next/quizID","quiz-master-next/pageID"],"providesContext":{"quiz-master-next/questionID":"questionID"},"example":{},"supports":{"html":false},"textdomain":"main-block","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css","render":"file:./render.php","viewScript":"file:./view.js"}');

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
/************************************************************************/
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
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
!function() {
/*!*******************************!*\
  !*** ./src/question/index.js ***!
  \*******************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./edit */ "./src/question/edit.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./block.json */ "./src/question/block.json");



(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_2__.name, {
  /**
   * @see ./edit.js
   */
  edit: _edit__WEBPACK_IMPORTED_MODULE_1__["default"]
});
}();
/******/ })()
;
//# sourceMappingURL=index.js.map