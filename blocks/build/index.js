/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/component/InputComponent.js":
/*!*****************************************!*\
  !*** ./src/component/InputComponent.js ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ InputComponent)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_escape_html__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/escape-html */ "@wordpress/escape-html");
/* harmony import */ var _wordpress_escape_html__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_escape_html__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _helper__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../helper */ "./src/helper.js");







/**
 * Create Input component based on data provided
 * id: attribute name
 * type: input type
 */
const noop = () => {};
function InputComponent({
  className = '',
  quizAttr,
  setAttributes,
  data,
  onChangeFunc = noop
}) {
  var _quizAttr$id, _quizAttr$id2, _quizAttr$id3, _quizAttr$id4, _quizAttr$id5;
  const processData = () => {
    data.defaultvalue = data.default;
    if (!(0,_helper__WEBPACK_IMPORTED_MODULE_4__.qsmIsEmpty)(data?.options)) {
      switch (data.type) {
        case 'checkbox':
          if (1 === data.options.length) {
            data.type = 'toggle';
          }
          data.label = data.options[0].label;
          break;
        case 'radio':
          if (1 == data.options.length) {
            data.label = data.options[0].label;
            data.type = 'toggle';
          } else {
            data.type = 'select';
          }
          break;
        default:
          break;
      }
    }
    data.label = (0,_helper__WEBPACK_IMPORTED_MODULE_4__.qsmIsEmpty)(data.label) ? '' : (0,_wordpress_escape_html__WEBPACK_IMPORTED_MODULE_2__.escapeAttribute)(data.label);
    data.help = (0,_helper__WEBPACK_IMPORTED_MODULE_4__.qsmIsEmpty)(data.help) ? '' : (0,_wordpress_escape_html__WEBPACK_IMPORTED_MODULE_2__.escapeAttribute)(data.help);
    return data;
  };
  const newData = processData();
  const {
    id,
    label = '',
    type,
    help = '',
    options = [],
    defaultvalue
  } = newData;
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, 'toggle' === type && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.ToggleControl, {
    label: label,
    help: help,
    checked: !(0,_helper__WEBPACK_IMPORTED_MODULE_4__.qsmIsEmpty)(quizAttr[id]) && '1' == quizAttr[id],
    onChange: () => onChangeFunc(!(0,_helper__WEBPACK_IMPORTED_MODULE_4__.qsmIsEmpty)(quizAttr[id]) && '1' == quizAttr[id] ? 0 : 1, id)
  }), 'select' === type && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: label,
    value: (_quizAttr$id = quizAttr[id]) !== null && _quizAttr$id !== void 0 ? _quizAttr$id : defaultvalue,
    options: options,
    onChange: val => onChangeFunc(val, id),
    help: help,
    __nextHasNoMarginBottom: true
  }), 'number' === type && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TextControl, {
    type: "number",
    label: label,
    value: (_quizAttr$id2 = quizAttr[id]) !== null && _quizAttr$id2 !== void 0 ? _quizAttr$id2 : defaultvalue,
    onChange: val => onChangeFunc(val, id),
    help: help,
    __nextHasNoMarginBottom: true
  }), 'text' === type && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TextControl, {
    type: "text",
    label: label,
    value: (_quizAttr$id3 = quizAttr[id]) !== null && _quizAttr$id3 !== void 0 ? _quizAttr$id3 : defaultvalue,
    onChange: val => onChangeFunc(val, id),
    help: help,
    __nextHasNoMarginBottom: true
  }), 'textarea' === type && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TextareaControl, {
    label: label,
    value: (_quizAttr$id4 = quizAttr[id]) !== null && _quizAttr$id4 !== void 0 ? _quizAttr$id4 : defaultvalue,
    onChange: val => onChangeFunc(val, id),
    help: help,
    __nextHasNoMarginBottom: true
  }), 'checkbox' === type && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.CheckboxControl, {
    label: label,
    help: help,
    checked: !(0,_helper__WEBPACK_IMPORTED_MODULE_4__.qsmIsEmpty)(quizAttr[id]) && '1' == quizAttr[id],
    onChange: () => onChangeFunc(!(0,_helper__WEBPACK_IMPORTED_MODULE_4__.qsmIsEmpty)(quizAttr[id]) && '1' == quizAttr[id] ? 0 : 1, id)
  }), 'radio' === type && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.RadioControl, {
    label: label,
    help: help,
    selected: (_quizAttr$id5 = quizAttr[id]) !== null && _quizAttr$id5 !== void 0 ? _quizAttr$id5 : defaultvalue,
    options: options,
    onChange: val => onChangeFunc(val, id)
  }));
}

/***/ }),

/***/ "./src/component/icon.js":
/*!*******************************!*\
  !*** ./src/component/icon.js ***!
  \*******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   answerOptionBlockIcon: () => (/* binding */ answerOptionBlockIcon),
/* harmony export */   plusIcon: () => (/* binding */ plusIcon),
/* harmony export */   qsmBlockIcon: () => (/* binding */ qsmBlockIcon),
/* harmony export */   questionBlockIcon: () => (/* binding */ questionBlockIcon),
/* harmony export */   warningIcon: () => (/* binding */ warningIcon)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);


//QSM Quiz Block
const qsmBlockIcon = () => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Icon, {
  icon: () => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    width: "24",
    height: "24",
    viewBox: "0 0 24 24",
    fill: "none",
    xmlns: "http://www.w3.org/2000/svg"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("rect", {
    width: "24",
    height: "24",
    rx: "3",
    fill: "black"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    d: "M17.8146 17.8349C19.3188 16.3426 20.25 14.2793 20.25 12C20.2485 7.44425 16.5267 3.75 11.9348 3.75C7.34282 3.75 3.62109 7.44425 3.62109 12C3.62109 16.5558 7.34282 20.25 11.9348 20.25H18.9988C19.4682 20.25 19.7074 19.7112 19.3813 19.3885L17.8146 17.8334V17.8349ZM11.8753 17.5195C8.72666 17.5195 6.17388 15.0737 6.17388 12.0569C6.17388 9.04022 8.72666 6.59442 11.8753 6.59442C15.024 6.59442 17.5768 9.04022 17.5768 12.0569C17.5768 15.0737 15.024 17.5195 11.8753 17.5195Z",
    fill: "white"
  }))
});

//Question Block
const questionBlockIcon = () => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Icon, {
  icon: () => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    width: "25",
    height: "25",
    viewBox: "0 0 25 25",
    fill: "none",
    xmlns: "http://www.w3.org/2000/svg"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("rect", {
    x: "0.102539",
    y: "0.101562",
    width: "24",
    height: "24",
    rx: "4.68852",
    fill: "#ADADAD"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    d: "M17.0475 17.191C17.2367 17.3683 17.3313 17.5752 17.3313 17.8117C17.3313 18.06 17.2426 18.2787 17.0653 18.4679C16.8879 18.6453 16.6751 18.734 16.4268 18.734C16.1667 18.734 15.9538 18.6512 15.7883 18.4857L14.937 17.6521C13.8492 18.4088 12.5959 18.7872 11.177 18.7872C10.0301 18.7872 9.01325 18.533 8.12646 18.0245C7.2515 17.5161 6.57163 16.8126 6.08685 15.914C5.6139 15.0035 5.37742 13.9631 5.37742 12.7925C5.37742 11.5273 5.64937 10.41 6.19327 9.44044C6.74898 8.45907 7.48206 7.70234 8.3925 7.17027C9.31475 6.6382 10.308 6.37216 11.3721 6.37216C12.4481 6.37216 13.459 6.64411 14.4049 7.18801C15.3508 7.72008 16.1075 8.46498 16.6751 9.42271C17.2426 10.3804 17.5264 11.4505 17.5264 12.6329C17.5264 14.0636 17.1007 15.3287 16.2494 16.4283L17.0475 17.191ZM11.177 17.1732C12.0874 17.1732 12.9269 16.9249 13.6955 16.4283L12.5604 15.311C12.3949 15.1454 12.3121 14.9799 12.3121 14.8144C12.3121 14.6015 12.4244 14.3887 12.6491 14.1759C12.8855 13.9631 13.122 13.8566 13.3585 13.8566C13.5122 13.8566 13.6364 13.9039 13.7309 13.9985L14.9724 15.1868C15.4927 14.4183 15.7528 13.5492 15.7528 12.5797C15.7528 11.7284 15.5518 10.9539 15.1498 10.2563C14.7596 9.54686 14.2335 8.99114 13.5713 8.58913C12.9092 8.18712 12.1998 7.98611 11.443 7.98611C10.6981 7.98611 9.99462 8.18121 9.33249 8.57139C8.67036 8.94975 8.13828 9.49956 7.73627 10.2208C7.34609 10.9421 7.15099 11.7756 7.15099 12.7216C7.15099 13.6083 7.32244 14.3887 7.66533 15.0627C8.02005 15.7366 8.49891 16.2569 9.10192 16.6234C9.71676 16.99 10.4085 17.1732 11.177 17.1732Z",
    fill: "white"
  }))
});

//Answer option Block
const answerOptionBlockIcon = () => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Icon, {
  icon: () => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    width: "24",
    height: "24",
    viewBox: "0 0 24 24",
    fill: "none",
    xmlns: "http://www.w3.org/2000/svg"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("rect", {
    width: "24",
    height: "24",
    rx: "4.21657",
    fill: "#ADADAD"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    d: "M8.96182 17.2773H7.33619L10.9889 7.12707H12.7583L16.411 17.2773H14.7853L11.9157 8.97077H11.8364L8.96182 17.2773ZM9.23441 13.3025H14.5078V14.5911H9.23441V13.3025Z",
    fill: "white"
  }))
});

//Warning icon
const warningIcon = () => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Icon, {
  icon: () => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    width: "54",
    height: "54",
    viewBox: "0 0 54 54",
    fill: "none",
    xmlns: "http://www.w3.org/2000/svg"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    d: "M27.1855 23.223V28.0626M15.1794 32.4196C14.0618 34.3554 15.4595 36.7739 17.6934 36.7739H36.6776C38.9102 36.7739 40.3079 34.3554 39.1916 32.4196L29.7008 15.9675C28.5832 14.0317 25.7878 14.0317 24.6702 15.9675L15.1794 32.4196ZM27.1855 31.9343H27.1945V31.9446H27.1855V31.9343Z",
    stroke: "#B45309",
    strokeWidth: "1.65929",
    strokeLinecap: "round",
    strokeLinejoin: "round"
  }))
});

//plus icon 
const plusIcon = () => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Icon, {
  icon: () => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    xmlns: "http://www.w3.org/2000/svg",
    viewBox: "0 0 24 24",
    width: "24",
    height: "24",
    "aria-hidden": "true",
    focusable: "false"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    d: "M18 11.2h-5.2V6h-1.6v5.2H6v1.6h5.2V18h1.6v-5.2H18z"
  }))
});

/***/ }),

/***/ "./src/edit.js":
/*!*********************!*\
  !*** ./src/edit.js ***!
  \*********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Edit)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/html-entities */ "@wordpress/html-entities");
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_notices__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/notices */ "@wordpress/notices");
/* harmony import */ var _wordpress_notices__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_notices__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _wordpress_editor__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @wordpress/editor */ "@wordpress/editor");
/* harmony import */ var _wordpress_editor__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(_wordpress_editor__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__);
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./editor.scss */ "./src/editor.scss");
/* harmony import */ var _helper__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./helper */ "./src/helper.js");
/* harmony import */ var _component_InputComponent__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./component/InputComponent */ "./src/component/InputComponent.js");
/* harmony import */ var _component_icon__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./component/icon */ "./src/component/icon.js");














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
  const page_post_id = context['postId'];
  const {
    createNotice
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_6__.useDispatch)(_wordpress_notices__WEBPACK_IMPORTED_MODULE_5__.store);
  //quiz attribute
  const globalQuizsetting = qsmBlockData.globalQuizsetting;
  const {
    quizID,
    postID,
    quizAttr = globalQuizsetting
  } = attributes;

  //quiz list
  const [quizList, setQuizList] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(qsmBlockData.QSMQuizList);
  //quiz list
  const [quizMessage, setQuizMessage] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)({
    error: false,
    msg: ''
  });
  //whether creating a new quiz
  const [createQuiz, setCreateQuiz] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  //whether saving quiz
  const [saveQuiz, setSaveQuiz] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  //whether to show advance option
  const [showAdvanceOption, setShowAdvanceOption] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  //Quiz template on set Quiz ID
  const [quizTemplate, setQuizTemplate] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)([]);
  //Quiz Options to create attributes label, description and layout
  const quizOptions = qsmBlockData.quizOptions;

  //check if page is saving
  const isSavingPage = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_6__.useSelect)(select => {
    const {
      isAutosavingPost,
      isSavingPost
    } = select(_wordpress_editor__WEBPACK_IMPORTED_MODULE_7__.store);
    return isSavingPost() && !isAutosavingPost();
  }, []);
  const editorSelectors = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_6__.useSelect)(select => {
    return select('core/editor');
  }, []);
  const {
    getBlock
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_6__.useSelect)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.store);

  /**Initialize block from server */
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    let shouldSetQSMAttr = true;
    if (shouldSetQSMAttr) {
      //add upgrade modal
      if ('0' == qsmBlockData.is_pro_activated) {
        setTimeout(() => {
          addUpgradePopupHtml();
        }, 100);
      }
      //initialize QSM block
      if (!(0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(quizID) && 0 < quizID) {
        //Check if quiz exists
        let hasQuiz = false;
        quizList.forEach(quizElement => {
          if (quizID == quizElement.value) {
            hasQuiz = true;
            return true;
          }
        });
        if (hasQuiz) {
          initializeQuizAttributes(quizID);
        } else {
          setAttributes({
            quizID: undefined
          });
          setQuizMessage({
            error: true,
            msg: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Quiz not found. Please select an existing quiz or create a new one.', 'quiz-master-next')
          });
        }
      }
    }

    //cleanup
    return () => {
      shouldSetQSMAttr = false;
    };
  }, []);

  /**Add modal advanced-question-type */
  const addUpgradePopupHtml = () => {
    let modalEl = document.getElementById('modal-advanced-question-type');
    if ((0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(modalEl)) {
      _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
        path: '/quiz-survey-master/v1/quiz/advance-ques-type-upgrade-popup',
        method: 'POST'
      }).then(res => {
        let bodyEl = document.getElementById('wpbody-content');
        if (!(0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(bodyEl) && 'success' == res.status) {
          bodyEl.insertAdjacentHTML('afterbegin', res.result);
        }
      }).catch(error => {
        console.log('error', error);
      });
    }
  };

  /**Initialize quiz attributes: first time render only */
  const initializeQuizAttributes = quiz_id => {
    if (!(0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(quiz_id) && 0 < quiz_id) {
      _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
        path: '/quiz-survey-master/v1/quiz/structure',
        method: 'POST',
        data: {
          quizID: quiz_id
        }
      }).then(res => {
        if ('success' == res.status) {
          setQuizMessage({
            error: false,
            msg: ''
          });
          let result = res.result;
          setAttributes({
            quizID: parseInt(quiz_id),
            postID: result.post_id,
            quizAttr: {
              ...quizAttr,
              ...result
            }
          });
          if (!(0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(result.qpages)) {
            let quizTemp = [];
            result.qpages.forEach(page => {
              let questions = [];
              if (!(0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(page.question_arr)) {
                page.question_arr.forEach(question => {
                  if (!(0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(question)) {
                    let answers = [];
                    //answers options blocks
                    if (!(0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(question.answers) && 0 < question.answers.length) {
                      question.answers.forEach((answer, aIndex) => {
                        answers.push(['qsm/quiz-answer-option', {
                          optionID: aIndex,
                          content: answer[0],
                          points: answer[1],
                          isCorrect: answer[2],
                          caption: (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(answer[3])
                        }]);
                      });
                    }
                    //question blocks
                    questions.push(['qsm/quiz-question', {
                      questionID: question.question_id,
                      isPublished: typeof question.settings.isPublished !== 'undefined' ? question.settings.isPublished : 1,
                      linked_question: typeof question.linked_question !== 'undefined' ? question.linked_question : '',
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
              quizTemp.push(['qsm/quiz-page', {
                pageID: page.id,
                pageKey: page.pagekey,
                hidePrevBtn: page.hide_prevbtn,
                quizID: page.quizID
              }, questions]);
            });
            setQuizTemplate(quizTemp);
          }
        } else {
          console.log("error " + res.msg);
        }
      }).catch(error => {
        console.log('error', error);
      });
    }
  };

  /**
   *
   * @returns Placeholder for quiz in case quiz ID is not set
   */
  const quizPlaceholder = () => {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.Placeholder, {
      className: "qsm-placeholder-wrapper",
      icon: _component_icon__WEBPACK_IMPORTED_MODULE_12__.qsmBlockIcon,
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Quiz And Survey Master', 'quiz-master-next'),
      instructions: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Easily and quickly add quizzes and surveys inside the block editor.', 'quiz-master-next')
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, !(0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(quizList) && 0 < quizList.length && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "qsm-placeholder-select-create-quiz"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.SelectControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('', 'quiz-master-next'),
      value: quizID,
      options: quizList,
      onChange: quizID => initializeQuizAttributes(quizID),
      disabled: createQuiz,
      __nextHasNoMarginBottom: true
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('OR', 'quiz-master-next')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.Button, {
      variant: "link",
      onClick: () => setCreateQuiz(!createQuiz)
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Add New', 'quiz-master-next'))), ((0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(quizList) || createQuiz) && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.__experimentalVStack, {
      spacing: "3",
      className: "qsm-placeholder-quiz-create-form"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.TextControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Quiz Name *', 'quiz-master-next'),
      help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Enter a name for this Quiz', 'quiz-master-next'),
      value: quizAttr?.quiz_name || '',
      onChange: val => setQuizAttributes(val, 'quiz_name')
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.Button, {
      variant: "link",
      onClick: () => setShowAdvanceOption(!showAdvanceOption)
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Advance options', 'quiz-master-next')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "qsm-advance-settings"
    }, showAdvanceOption && quizOptions.map(qSetting => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_component_InputComponent__WEBPACK_IMPORTED_MODULE_11__["default"], {
      key: 'qsm-settings' + qSetting.id,
      data: qSetting,
      quizAttr: quizAttr,
      setAttributes: setAttributes,
      onChangeFunc: setQuizAttributes
    }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.Button, {
      variant: "primary",
      disabled: saveQuiz || (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(quizAttr.quiz_name),
      onClick: () => createNewQuiz()
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Create Quiz', 'quiz-master-next'))), quizMessage.error && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
      className: "qsm-error-text"
    }, quizMessage.msg)));
  };

  /**
   * Set attribute value
   * @param { any } value attribute value to set
   * @param { string } attr_name attribute name
   */
  const setQuizAttributes = (value, attr_name) => {
    let newAttr = quizAttr;
    newAttr[attr_name] = value;
    setAttributes({
      quizAttr: {
        ...newAttr
      }
    });
  };

  /**
   * Prepare quiz data e.g. quiz details, questions, answers etc to save
   * @returns quiz data
   */
  const getQuizDataToSave = () => {
    let blocks = getBlock(clientId);
    if ((0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(blocks)) {
      return false;
    }
    blocks = blocks.innerBlocks;
    let quizDataToSave = {
      quiz_id: quizAttr.quiz_id,
      post_id: quizAttr.post_id,
      quiz: {},
      pages: [],
      qpages: [],
      questions: []
    };
    let pageSNo = 0;
    //loop through inner blocks
    blocks.forEach(block => {
      if ('qsm/quiz-page' === block.name) {
        let pageID = block.attributes.pageID;
        let questions = [];
        if (!(0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(block.innerBlocks) && 0 < block.innerBlocks.length) {
          let questionBlocks = block.innerBlocks;
          //Question Blocks
          questionBlocks.forEach(questionBlock => {
            if ('qsm/quiz-question' !== questionBlock.name) {
              return true;
            }
            let questionAttr = questionBlock.attributes;
            let answerEditor = (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(questionAttr?.answerEditor, 'text');
            let answers = [];
            //Answer option blocks
            if (!(0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(questionBlock.innerBlocks) && 0 < questionBlock.innerBlocks.length) {
              let answerOptionBlocks = questionBlock.innerBlocks;
              answerOptionBlocks.forEach(answerOptionBlock => {
                if ('qsm/quiz-answer-option' !== answerOptionBlock.name) {
                  return true;
                }
                let answerAttr = answerOptionBlock.attributes;
                let answerContent = (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(answerAttr?.content);
                //if rich text
                if (!(0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(questionAttr?.answerEditor) && 'rich' === questionAttr.answerEditor) {
                  answerContent = (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmDecodeHtml)((0,_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_3__.decodeEntities)(answerContent));
                }
                let ans = [answerContent, (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(answerAttr?.points), (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(answerAttr?.isCorrect)];
                //answer options are image type
                if ('image' === answerEditor && !(0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(answerAttr?.caption)) {
                  ans.push(answerAttr?.caption);
                }
                answers.push(ans);
              });
            }

            //questions Data
            questions.push(questionAttr.questionID);
            //update question only if changes occured
            if (questionAttr.isChanged) {
              quizDataToSave.questions.push({
                "id": questionAttr.questionID,
                "quizID": quizAttr.quiz_id,
                "postID": quizAttr.post_id,
                "answerEditor": answerEditor,
                "type": (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(questionAttr?.type, '0'),
                "name": (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmDecodeHtml)((0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(questionAttr?.description)),
                "question_title": (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(questionAttr?.title),
                "answerInfo": (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmDecodeHtml)((0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(questionAttr?.correctAnswerInfo)),
                "comments": (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(questionAttr?.commentBox, '1'),
                "hint": (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(questionAttr?.hint),
                "category": (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(questionAttr?.category),
                "multicategories": (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(questionAttr?.multicategories, []),
                "required": (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(questionAttr?.required, 0),
                "is_published": (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(questionAttr?.isPublished, 1),
                "merged_question": (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(questionAttr?.linked_question, ''),
                "answers": answers,
                "featureImageID": (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(questionAttr?.featureImageID),
                "featureImageSrc": (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(questionAttr?.featureImageSrc),
                "page": pageSNo,
                "other_settings": {
                  ...(0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(questionAttr?.settings, {}),
                  "required": (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(questionAttr?.required, 0),
                  "isPublished": (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(questionAttr?.isPublished, 1),
                  "question_title": (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmValueOrDefault)(questionAttr?.title),
                  "answerEditor": answerEditor
                }
              });
            }
          });
        }

        // pages[0][]: 2512
        // 	qpages[0][id]: 2
        // 	qpages[0][quizID]: 76
        // 	qpages[0][pagekey]: Ipj90nNT
        // 	qpages[0][hide_prevbtn]: 0
        // 	qpages[0][questions][]: 2512
        // 	post_id: 111
        //page data
        quizDataToSave.pages.push(questions);
        quizDataToSave.qpages.push({
          'id': pageID,
          'quizID': quizAttr.quiz_id,
          'pagekey': (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(block.attributes.pageKey) ? (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmUniqid)() : block.attributes.pageKey,
          'hide_prevbtn': block.attributes.hidePrevBtn,
          'questions': questions
        });
        pageSNo++;
      }
    });

    //Quiz details
    quizDataToSave.quiz = {
      'quiz_name': quizAttr.quiz_name,
      'quiz_id': quizAttr.quiz_id,
      'post_id': quizAttr.post_id
    };
    if (showAdvanceOption) {
      ['form_type', 'system', 'timer_limit', 'pagination', 'enable_contact_form', 'enable_pagination_quiz', 'show_question_featured_image_in_result', 'progress_bar', 'require_log_in', 'disable_first_page', 'comment_section'].forEach(item => {
        if ('undefined' !== typeof quizAttr[item] && null !== quizAttr[item]) {
          quizDataToSave.quiz[item] = quizAttr[item];
        }
      });
    }
    return quizDataToSave;
  };

  //saving Quiz on save page
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (isSavingPage) {
      let quizData = getQuizDataToSave();
      //save quiz status
      setSaveQuiz(true);

      //post status
      let post_status = 'publish';
      if (!(0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(editorSelectors)) {
        post_status = editorSelectors.getEditedPostAttribute('status');
      }
      if ((0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(post_status)) {
        post_status = 'publish';
      }
      quizData = (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmFormData)({
        'save_entire_quiz': '1',
        'quizData': JSON.stringify(quizData),
        'qsm_block_quiz_nonce': qsmBlockData.nonce,
        'page_post_id': (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(page_post_id) ? 0 : page_post_id,
        'post_status': post_status,
        "nonce": qsmBlockData.saveNonce //save pages nonce
      });

      //AJAX call
      _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
        path: '/quiz-survey-master/v1/quiz/save_quiz',
        method: 'POST',
        body: quizData
      }).then(res => {
        //create notice
        createNotice(res.status, res.msg, {
          isDismissible: true,
          type: 'snackbar'
        });
      }).catch(error => {
        console.log('error', error);
        createNotice('error', error.message, {
          isDismissible: true,
          type: 'snackbar'
        });
      });
    }
  }, [isSavingPage]);

  /**
   * Create new quiz and set quiz ID
   *
   */
  const createNewQuiz = () => {
    if ((0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(quizAttr.quiz_name)) {
      console.log("empty quiz_name");
      return;
    }
    //save quiz status
    setSaveQuiz(true);
    let quizData = (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmFormData)({
      'quiz_name': quizAttr.quiz_name,
      'qsm_new_quiz_nonce': qsmBlockData.qsm_new_quiz_nonce
    });
    ['form_type', 'system', 'timer_limit', 'pagination', 'enable_contact_form', 'enable_pagination_quiz', 'show_question_featured_image_in_result', 'progress_bar', 'require_log_in', 'disable_first_page', 'comment_section'].forEach(item => 'undefined' === typeof quizAttr[item] || null === quizAttr[item] ? '' : quizData.append(item, quizAttr[item]));

    //AJAX call
    _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
      path: '/quiz-survey-master/v1/quiz/create_quiz',
      method: 'POST',
      body: quizData
    }).then(res => {
      //save quiz status
      setSaveQuiz(false);
      if ('success' == res.status) {
        //create a question
        let newQuestion = (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmFormData)({
          "id": null,
          "quizID": res.quizID,
          "answerEditor": "text",
          "type": "0",
          "name": "",
          "question_title": "",
          "answerInfo": "",
          "comments": "1",
          "hint": "",
          "category": "",
          "required": 0,
          "isPublished": 1,
          "answers": [],
          "page": 0
        });
        //AJAX call
        _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
          path: '/quiz-survey-master/v1/questions',
          method: 'POST',
          body: newQuestion
        }).then(response => {
          if ('success' == response.status) {
            let question_id = response.id;

            /**Page attributes required format */
            // pages[0][]: 2512
            // 	qpages[0][id]: 2
            // 	qpages[0][quizID]: 76
            // 	qpages[0][pagekey]: Ipj90nNT
            // 	qpages[0][hide_prevbtn]: 0
            // 	qpages[0][questions][]: 2512
            // 	post_id: 111

            let newPage = (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmFormData)({
              "action": qsmBlockData.save_pages_action,
              "quiz_id": res.quizID,
              "nonce": qsmBlockData.saveNonce,
              "post_id": res.quizPostID
            });
            newPage.append('pages[0][]', question_id);
            newPage.append('qpages[0][id]', 1);
            newPage.append('qpages[0][quizID]', res.quizID);
            newPage.append('qpages[0][pagekey]', (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmUniqid)());
            newPage.append('qpages[0][hide_prevbtn]', 0);
            newPage.append('qpages[0][questions][]', question_id);

            //create a page
            _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
              url: qsmBlockData.ajax_url,
              method: 'POST',
              body: newPage
            }).then(pageResponse => {
              if ('success' == pageResponse.status) {
                //set new quiz
                initializeQuizAttributes(res.quizID);
              }
            });
          }
        }).catch(error => {
          console.log('error', error);
          createNotice('error', error.message, {
            isDismissible: true,
            type: 'snackbar'
          });
        });
      }

      //create notice
      createNotice(res.status, res.msg, {
        isDismissible: true,
        type: 'snackbar'
      });
    }).catch(error => {
      console.log('error', error);
      createNotice('error', error.message, {
        isDismissible: true,
        type: 'snackbar'
      });
    });
  };

  /**
   * Inner Blocks
   */
  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.useBlockProps)();
  const innerBlocksProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.useInnerBlocksProps)(blockProps, {
    template: quizTemplate,
    allowedBlocks: ['qsm/quiz-page']
  });
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.InspectorControls, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Quiz settings', 'quiz-master-next'),
    initialOpen: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    className: "qsm-inspector-label"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Status', 'quiz-master-next') + ':', (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    className: "qsm-inspector-label-value"
  }, quizAttr.post_status)), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Quiz Name *', 'quiz-master-next'),
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Enter a name for this Quiz', 'quiz-master-next'),
    value: quizAttr?.quiz_name || '',
    onChange: val => setQuizAttributes(val, 'quiz_name'),
    className: "qsm-no-mb"
  }), (!(0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(quizID) || '0' != quizID) && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.ExternalLink, {
    href: qsmBlockData.quiz_settings_url + '&quiz_id=' + quizID + '&tab=options'
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Advance Quiz Settings', 'quiz-master-next'))))), (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(quizID) || '0' == quizID ? (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    ...blockProps
  }, " ", quizPlaceholder(), " ") : (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    ...innerBlocksProps
  }));
}

/***/ }),

/***/ "./src/helper.js":
/*!***********************!*\
  !*** ./src/helper.js ***!
  \***********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   qsmAddObjToFormData: () => (/* binding */ qsmAddObjToFormData),
/* harmony export */   qsmDecodeHtml: () => (/* binding */ qsmDecodeHtml),
/* harmony export */   qsmFormData: () => (/* binding */ qsmFormData),
/* harmony export */   qsmGenerateRandomKey: () => (/* binding */ qsmGenerateRandomKey),
/* harmony export */   qsmIsEmpty: () => (/* binding */ qsmIsEmpty),
/* harmony export */   qsmMatchingValueKeyArray: () => (/* binding */ qsmMatchingValueKeyArray),
/* harmony export */   qsmSanitizeName: () => (/* binding */ qsmSanitizeName),
/* harmony export */   qsmStripTags: () => (/* binding */ qsmStripTags),
/* harmony export */   qsmUniqid: () => (/* binding */ qsmUniqid),
/* harmony export */   qsmUniqueArray: () => (/* binding */ qsmUniqueArray),
/* harmony export */   qsmValueOrDefault: () => (/* binding */ qsmValueOrDefault)
/* harmony export */ });
//Check if undefined, null, empty
const qsmIsEmpty = data => 'undefined' === typeof data || null === data || '' === data;

//Get Unique array values
const qsmUniqueArray = arr => {
  if (qsmIsEmpty(arr) || !Array.isArray(arr)) {
    return arr;
  }
  return arr.filter((val, index, arr) => arr.indexOf(val) === index);
};

//Match array of object values and return array of cooresponding matching keys 
const qsmMatchingValueKeyArray = (values, obj) => {
  if (qsmIsEmpty(obj) || !Array.isArray(values)) {
    return values;
  }
  return values.map(val => Object.keys(obj).find(key => obj[key] == val));
};

//Decode htmlspecialchars
const qsmDecodeHtml = html => {
  var txt = document.createElement("textarea");
  txt.innerHTML = html;
  return txt.value;
};
const qsmSanitizeName = name => {
  if (qsmIsEmpty(name)) {
    name = '';
  } else {
    name = name.toLowerCase().replace(/ /g, '_');
    name = name.replace(/\W/g, '');
  }
  return name;
};

// Remove anchor tags from text content.
const qsmStripTags = text => {
  let div = document.createElement("div");
  div.innerHTML = qsmDecodeHtml(text);
  return div.innerText;
};

//prepare form data
const qsmFormData = (obj = false) => {
  let newData = new FormData();
  //add to check if api call from editor
  newData.append('qsm_block_api_call', '1');
  if (false !== obj) {
    for (let k in obj) {
      if (obj.hasOwnProperty(k)) {
        newData.append(k, obj[k]);
      }
    }
  }
  return newData;
};

//add objecyt to form data
const qsmAddObjToFormData = (formKey, valueObj, data = new FormData()) => {
  if (qsmIsEmpty(formKey) || qsmIsEmpty(valueObj) || 'object' !== typeof valueObj) {
    return data;
  }
  for (let key in valueObj) {
    if (valueObj.hasOwnProperty(key)) {
      let value = valueObj[key];
      if ('object' === value) {
        qsmAddObjToFormData(formKey + '[' + key + ']', value, data);
      } else {
        data.append(formKey + '[' + key + ']', valueObj[key]);
      }
    }
  }
  return data;
};

//Generate random number
const qsmGenerateRandomKey = length => {
  const charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
  let key = "";

  // Generate random bytes
  const values = new Uint8Array(length);
  window.crypto.getRandomValues(values);
  for (let i = 0; i < length; i++) {
    // Use the random byte to index into the charset
    key += charset[values[i] % charset.length];
  }
  return key;
};

//generate uiniq id
const qsmUniqid = (prefix = "", random = false) => {
  const id = qsmGenerateRandomKey(8);
  return `${prefix}${id}${random ? `.${qsmGenerateRandomKey(7)}` : ""}`;
};

//return data if not empty otherwise default value
const qsmValueOrDefault = (data, defaultValue = '') => qsmIsEmpty(data) ? defaultValue : data;

/***/ }),

/***/ "./src/index.js":
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./style.scss */ "./src/style.scss");
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./edit */ "./src/edit.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./block.json */ "./src/block.json");
/* harmony import */ var _component_icon__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./component/icon */ "./src/component/icon.js");
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/compose */ "@wordpress/compose");
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_compose__WEBPACK_IMPORTED_MODULE_5__);






const save = props => null;
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_3__.name, {
  icon: _component_icon__WEBPACK_IMPORTED_MODULE_4__.qsmBlockIcon,
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./src/style.scss":
/*!************************!*\
  !*** ./src/style.scss ***!
  \************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "@wordpress/api-fetch":
/*!**********************************!*\
  !*** external ["wp","apiFetch"] ***!
  \**********************************/
/***/ ((module) => {

module.exports = window["wp"]["apiFetch"];

/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ ((module) => {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ ((module) => {

module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/compose":
/*!*********************************!*\
  !*** external ["wp","compose"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["compose"];

/***/ }),

/***/ "@wordpress/data":
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["data"];

/***/ }),

/***/ "@wordpress/editor":
/*!********************************!*\
  !*** external ["wp","editor"] ***!
  \********************************/
/***/ ((module) => {

module.exports = window["wp"]["editor"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/escape-html":
/*!************************************!*\
  !*** external ["wp","escapeHtml"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["escapeHtml"];

/***/ }),

/***/ "@wordpress/html-entities":
/*!**************************************!*\
  !*** external ["wp","htmlEntities"] ***!
  \**************************************/
/***/ ((module) => {

module.exports = window["wp"]["htmlEntities"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "@wordpress/notices":
/*!*********************************!*\
  !*** external ["wp","notices"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["notices"];

/***/ }),

/***/ "./src/block.json":
/*!************************!*\
  !*** ./src/block.json ***!
  \************************/
/***/ ((module) => {

module.exports = JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"qsm/quiz","version":"0.1.0","title":"QSM","category":"widgets","keywords":["Quiz","QSM Quiz","Survey","form","Quiz Block"],"icon":"vault","description":"Easily and quickly add quizzes and surveys inside the block editor.","attributes":{"quizID":{"type":"number","default":0},"postID":{"type":"number"},"quizAttr":{"type":"object"}},"providesContext":{"quiz-master-next/quizID":"quizID","quiz-master-next/quizAttr":"quizAttr"},"usesContext":["postId","postStatus"],"example":{},"supports":{"html":false},"textdomain":"main-block","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css"}');

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
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
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
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
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
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
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
/******/ 		var chunkLoadingGlobal = globalThis["webpackChunkqsm"] = globalThis["webpackChunkqsm"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["./style-index"], () => (__webpack_require__("./src/index.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map