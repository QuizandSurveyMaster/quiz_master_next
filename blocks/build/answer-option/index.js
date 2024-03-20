/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/@wordpress/icons/build-module/library/image.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@wordpress/icons/build-module/library/image.js ***!
  \*********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_primitives__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/primitives */ "@wordpress/primitives");
/* harmony import */ var _wordpress_primitives__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_1__);

/**
 * WordPress dependencies
 */

const image = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_1__.SVG, {
  viewBox: "0 0 24 24",
  xmlns: "http://www.w3.org/2000/svg"
}, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_1__.Path, {
  d: "M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM5 4.5h14c.3 0 .5.2.5.5v8.4l-3-2.9c-.3-.3-.8-.3-1 0L11.9 14 9 12c-.3-.2-.6-.2-.8 0l-3.6 2.6V5c-.1-.3.1-.5.4-.5zm14 15H5c-.3 0-.5-.2-.5-.5v-2.4l4.1-3 3 1.9c.3.2.7.2.9-.1L16 12l3.5 3.4V19c0 .3-.2.5-.5.5z"
}));
/* harmony default export */ __webpack_exports__["default"] = (image);
//# sourceMappingURL=image.js.map

/***/ }),

/***/ "./src/answer-option/edit.js":
/*!***********************************!*\
  !*** ./src/answer-option/edit.js ***!
  \***********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ Edit; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/html-entities */ "@wordpress/html-entities");
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_escape_html__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/escape-html */ "@wordpress/escape-html");
/* harmony import */ var _wordpress_escape_html__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_escape_html__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/url */ "@wordpress/url");
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_url__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_8__);
/* harmony import */ var _component_ImageType__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ../component/ImageType */ "./src/component/ImageType.js");
/* harmony import */ var _helper__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ../helper */ "./src/helper.js");












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
    context,
    mergeBlocks,
    onReplace,
    onRemove
  } = props;
  const quizID = context['quiz-master-next/quizID'];
  const pageID = context['quiz-master-next/pageID'];
  const questionID = context['quiz-master-next/questionID'];
  const questionType = context['quiz-master-next/questionType'];
  const answerType = context['quiz-master-next/answerType'];
  const questionChanged = context['quiz-master-next/questionChanged'];
  const name = 'qsm/quiz-answer-option';
  const {
    optionID,
    content,
    caption,
    points,
    isCorrect
  } = attributes;
  const {
    selectBlock
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_5__.useDispatch)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.store);

  //Use to to update block attributes using clientId
  const {
    updateBlockAttributes
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_5__.useDispatch)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.store);
  const questionClientID = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_5__.useSelect)(select => {
    //get parent gutena form clientIds
    let questionClientID = select(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.store).getBlockParentsByBlockName(clientId, 'qsm/quiz-question', true);
    return (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(questionClientID) ? '' : questionClientID[0];
  }, [clientId]);

  //detect change in question
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    let shouldSetChanged = true;
    if (shouldSetChanged && isSelected && !(0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(questionClientID) && false === questionChanged) {
      updateBlockAttributes(questionClientID, {
        isChanged: true
      });
    }

    //cleanup
    return () => {
      shouldSetChanged = false;
    };
  }, [content, caption, points, isCorrect]);

  /**Initialize block from server */
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    let shouldSetQSMAttr = true;
    if (shouldSetQSMAttr) {
      if (!(0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(content) && (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_6__.isURL)(content) && (-1 !== content.indexOf('https://') || -1 !== content.indexOf('http://')) && ['rich', 'text'].includes(answerType)) {
        setAttributes({
          content: '',
          caption: ''
        });
      }
    }

    //cleanup
    return () => {
      shouldSetQSMAttr = false;
    };
  }, [answerType]);
  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.useBlockProps)({
    className: isSelected ? ' is-highlighted ' : ''
  });
  const inputType = ['4', '10'].includes(questionType) ? "checkbox" : "radio";
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.InspectorControls, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Settings', 'quiz-master-next'),
    initialOpen: true
  }, /**Image answer option */
  'image' === answerType && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.TextControl, {
    type: "text",
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Caption', 'quiz-master-next'),
    value: caption,
    onChange: caption => setAttributes({
      caption: (0,_wordpress_escape_html__WEBPACK_IMPORTED_MODULE_3__.escapeAttribute)(caption)
    })
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.TextControl, {
    type: "number",
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Points', 'quiz-master-next'),
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Answer points', 'quiz-master-next'),
    value: points,
    onChange: points => setAttributes({
      points
    })
  }), ['0', '4', '1', '10', '2'].includes(questionType) && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.ToggleControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Correct', 'quiz-master-next'),
    checked: !(0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(isCorrect) && '1' == isCorrect,
    onChange: () => setAttributes({
      isCorrect: !(0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmIsEmpty)(isCorrect) && '1' == isCorrect ? 0 : 1
    })
  }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    ...blockProps
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.__experimentalHStack, {
    className: "edit-post-document-actions__title",
    spacing: 1,
    justify: "left"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: inputType,
    disabled: true,
    readOnly: true,
    tabIndex: "-1"
  }), /**Text answer option*/
  !['rich', 'image'].includes(answerType) && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.RichText, {
    tagName: "p",
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Answer options', 'quiz-master-next'),
    "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Question answer', 'quiz-master-next'),
    placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Your Answer', 'quiz-master-next'),
    value: (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmStripTags)((0,_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_2__.decodeEntities)(content)),
    onChange: content => setAttributes({
      content: (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmStripTags)((0,_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_2__.decodeEntities)(content))
    }),
    onSplit: (value, isOriginal) => {
      let newAttributes;
      if (isOriginal || value) {
        newAttributes = {
          ...attributes,
          content: value
        };
      }
      const block = (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_8__.createBlock)(name, newAttributes);
      if (isOriginal) {
        block.clientId = clientId;
      }
      return block;
    },
    onMerge: mergeBlocks,
    onReplace: onReplace,
    onRemove: onRemove,
    allowedFormats: [],
    withoutInteractiveFormatting: true,
    className: 'qsm-question-answer-option',
    identifier: "text"
  }), /**Rich Text answer option */
  'rich' === answerType && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.RichText, {
    tagName: "p",
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Answer options', 'quiz-master-next'),
    "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Question answer', 'quiz-master-next'),
    placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Your Answer', 'quiz-master-next'),
    value: (0,_helper__WEBPACK_IMPORTED_MODULE_10__.qsmDecodeHtml)((0,_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_2__.decodeEntities)(content)),
    onChange: content => setAttributes({
      content
    }),
    onSplit: (value, isOriginal) => {
      let newAttributes;
      if (isOriginal || value) {
        newAttributes = {
          ...attributes,
          content: value
        };
      }
      const block = (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_8__.createBlock)(name, newAttributes);
      if (isOriginal) {
        block.clientId = clientId;
      }
      return block;
    },
    onMerge: mergeBlocks,
    onReplace: onReplace,
    onRemove: onRemove,
    className: 'qsm-question-answer-option',
    identifier: "text",
    __unstableEmbedURLOnPaste: true,
    __unstableAllowPrefixTransformations: true
  }), /**Image answer option */
  'image' === answerType && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_component_ImageType__WEBPACK_IMPORTED_MODULE_9__["default"], {
    url: (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_6__.isURL)(content) ? content : '',
    caption: caption,
    setURLCaption: (url, caption) => setAttributes({
      content: (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_6__.isURL)(url) ? url : '',
      caption: caption
    })
  }))));
}

/***/ }),

/***/ "./src/component/ImageType.js":
/*!************************************!*\
  !*** ./src/component/ImageType.js ***!
  \************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   ImageType: function() { return /* binding */ ImageType; },
/* harmony export */   isExternalImage: function() { return /* binding */ isExternalImage; },
/* harmony export */   pickRelevantMediaFiles: function() { return /* binding */ pickRelevantMediaFiles; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_blob__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/blob */ "@wordpress/blob");
/* harmony import */ var _wordpress_blob__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blob__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/@wordpress/icons/build-module/library/image.js");
/* harmony import */ var _wordpress_notices__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/notices */ "@wordpress/notices");
/* harmony import */ var _wordpress_notices__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_notices__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @wordpress/url */ "@wordpress/url");
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(_wordpress_url__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var _helper__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ../helper */ "./src/helper.js");

/**
 * Image Component: Upload, Use media, external image url
 */










const pickRelevantMediaFiles = (image, size) => {
  const imageProps = Object.fromEntries(Object.entries(image !== null && image !== void 0 ? image : {}).filter(([key]) => ['alt', 'id', 'link', 'caption'].includes(key)));
  imageProps.url = image?.sizes?.[size]?.url || image?.media_details?.sizes?.[size]?.source_url || image.url;
  return imageProps;
};

/**
 * Is the URL a temporary blob URL? A blob URL is one that is used temporarily
 * while the image is being uploaded and will not have an id yet allocated.
 *
 * @param {number=} id  The id of the image.
 * @param {string=} url The url of the image.
 *
 * @return {boolean} Is the URL a Blob URL
 */
const isTemporaryImage = (id, url) => !id && (0,_wordpress_blob__WEBPACK_IMPORTED_MODULE_1__.isBlobURL)(url);

/**
 * Is the url for the image hosted externally. An externally hosted image has no
 * id and is not a blob url.
 *
 * @param {number=} id  The id of the image.
 * @param {string=} url The url of the image.
 *
 * @return {boolean} Is the url an externally hosted url?
 */
const isExternalImage = (id, url) => url && !id && !(0,_wordpress_blob__WEBPACK_IMPORTED_MODULE_1__.isBlobURL)(url);
function ImageType({
  url = '',
  caption = '',
  alt = '',
  setURLCaption
}) {
  const ALLOWED_MEDIA_TYPES = ['image'];
  const [id, setId] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(null);
  const [temporaryURL, setTemporaryURL] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)();
  const ref = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)();
  const {
    imageDefaultSize,
    mediaUpload
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.useSelect)(select => {
    const {
      getSettings
    } = select(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.store);
    const settings = getSettings();
    return {
      imageDefaultSize: settings.imageDefaultSize,
      mediaUpload: settings.mediaUpload
    };
  }, []);
  const {
    createErrorNotice
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.useDispatch)(_wordpress_notices__WEBPACK_IMPORTED_MODULE_6__.store);
  function onUploadError(message) {
    createErrorNotice(message, {
      type: 'snackbar'
    });
    setURLCaption(undefined, undefined);
    setTemporaryURL(undefined);
  }
  function onSelectImage(media) {
    if (!media || !media.url) {
      setURLCaption(undefined, undefined);
      return;
    }
    if ((0,_wordpress_blob__WEBPACK_IMPORTED_MODULE_1__.isBlobURL)(media.url)) {
      setTemporaryURL(media.url);
      return;
    }
    setTemporaryURL();
    let mediaAttributes = pickRelevantMediaFiles(media, imageDefaultSize);
    setId(mediaAttributes.id);
    setURLCaption(mediaAttributes.url, mediaAttributes.caption);
  }
  function onSelectURL(newURL) {
    if (newURL !== url) {
      setURLCaption(newURL, caption);
    }
  }
  let isTemp = isTemporaryImage(id, url);

  // Upload a temporary image on mount.
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (!isTemp) {
      return;
    }
    const file = (0,_wordpress_blob__WEBPACK_IMPORTED_MODULE_1__.getBlobByURL)(url);
    if (file) {
      mediaUpload({
        filesList: [file],
        onFileChange: ([img]) => {
          onSelectImage(img);
        },
        allowedTypes: ALLOWED_MEDIA_TYPES,
        onError: message => {
          isTemp = false;
          onUploadError(message);
        }
      });
    }
  }, []);

  // If an image is temporary, revoke the Blob url when it is uploaded (and is
  // no longer temporary).
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (isTemp) {
      setTemporaryURL(url);
      return;
    }
    (0,_wordpress_blob__WEBPACK_IMPORTED_MODULE_1__.revokeBlobURL)(temporaryURL);
  }, [isTemp, url]);
  const isExternal = isExternalImage(id, url);
  const src = isExternal ? url : undefined;
  const mediaPreview = !!url && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
    alt: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__.__)('Edit image'),
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__.__)('Edit image'),
    className: 'edit-image-preview',
    src: url
  });
  let img = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
    src: temporaryURL || url,
    alt: "",
    className: "qsm-answer-option-image",
    style: {
      width: '200',
      height: 'auto'
    }
  }), temporaryURL && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Spinner, null));
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("figure", null, (0,_helper__WEBPACK_IMPORTED_MODULE_8__.qsmIsEmpty)(url) ? (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.MediaPlaceholder, {
    icon: (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.BlockIcon, {
      icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_9__["default"]
    }),
    onSelect: onSelectImage,
    onSelectURL: onSelectURL,
    onError: onUploadError,
    accept: "image/*",
    allowedTypes: ALLOWED_MEDIA_TYPES,
    value: {
      id,
      src
    },
    mediaPreview: mediaPreview,
    disableMediaButtons: temporaryURL || url
  }) : (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.BlockControls, {
    group: "other"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.MediaReplaceFlow, {
    mediaId: id,
    mediaURL: url,
    allowedTypes: ALLOWED_MEDIA_TYPES,
    accept: "image/*",
    onSelect: onSelectImage,
    onSelectURL: onSelectURL,
    onError: onUploadError
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, img)));
}
/* harmony default export */ __webpack_exports__["default"] = (ImageType);

/***/ }),

/***/ "./src/component/icon.js":
/*!*******************************!*\
  !*** ./src/component/icon.js ***!
  \*******************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   answerOptionBlockIcon: function() { return /* binding */ answerOptionBlockIcon; },
/* harmony export */   qsmBlockIcon: function() { return /* binding */ qsmBlockIcon; },
/* harmony export */   questionBlockIcon: function() { return /* binding */ questionBlockIcon; },
/* harmony export */   warningIcon: function() { return /* binding */ warningIcon; }
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

/***/ }),

/***/ "./src/helper.js":
/*!***********************!*\
  !*** ./src/helper.js ***!
  \***********************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   qsmAddObjToFormData: function() { return /* binding */ qsmAddObjToFormData; },
/* harmony export */   qsmDecodeHtml: function() { return /* binding */ qsmDecodeHtml; },
/* harmony export */   qsmFormData: function() { return /* binding */ qsmFormData; },
/* harmony export */   qsmGenerateRandomKey: function() { return /* binding */ qsmGenerateRandomKey; },
/* harmony export */   qsmIsEmpty: function() { return /* binding */ qsmIsEmpty; },
/* harmony export */   qsmMatchingValueKeyArray: function() { return /* binding */ qsmMatchingValueKeyArray; },
/* harmony export */   qsmSanitizeName: function() { return /* binding */ qsmSanitizeName; },
/* harmony export */   qsmStripTags: function() { return /* binding */ qsmStripTags; },
/* harmony export */   qsmUniqid: function() { return /* binding */ qsmUniqid; },
/* harmony export */   qsmUniqueArray: function() { return /* binding */ qsmUniqueArray; },
/* harmony export */   qsmValueOrDefault: function() { return /* binding */ qsmValueOrDefault; }
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

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ (function(module) {

module.exports = window["React"];

/***/ }),

/***/ "@wordpress/blob":
/*!******************************!*\
  !*** external ["wp","blob"] ***!
  \******************************/
/***/ (function(module) {

module.exports = window["wp"]["blob"];

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

/***/ "@wordpress/data":
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
/***/ (function(module) {

module.exports = window["wp"]["data"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ (function(module) {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/escape-html":
/*!************************************!*\
  !*** external ["wp","escapeHtml"] ***!
  \************************************/
/***/ (function(module) {

module.exports = window["wp"]["escapeHtml"];

/***/ }),

/***/ "@wordpress/html-entities":
/*!**************************************!*\
  !*** external ["wp","htmlEntities"] ***!
  \**************************************/
/***/ (function(module) {

module.exports = window["wp"]["htmlEntities"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ (function(module) {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "@wordpress/notices":
/*!*********************************!*\
  !*** external ["wp","notices"] ***!
  \*********************************/
/***/ (function(module) {

module.exports = window["wp"]["notices"];

/***/ }),

/***/ "@wordpress/primitives":
/*!************************************!*\
  !*** external ["wp","primitives"] ***!
  \************************************/
/***/ (function(module) {

module.exports = window["wp"]["primitives"];

/***/ }),

/***/ "@wordpress/url":
/*!*****************************!*\
  !*** external ["wp","url"] ***!
  \*****************************/
/***/ (function(module) {

module.exports = window["wp"]["url"];

/***/ }),

/***/ "./src/answer-option/block.json":
/*!**************************************!*\
  !*** ./src/answer-option/block.json ***!
  \**************************************/
/***/ (function(module) {

module.exports = JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"qsm/quiz-answer-option","version":"0.1.0","title":"Answer Option","category":"widgets","parent":["qsm/quiz-question"],"icon":"remove","description":"QSM Quiz answer option","attributes":{"optionID":{"type":"string","default":"0"},"content":{"type":"string","default":""},"caption":{"type":"string","default":""},"points":{"type":"string","default":"0"},"isCorrect":{"type":"string","default":"0"}},"usesContext":["quiz-master-next/quizID","quiz-master-next/pageID","quiz-master-next/quizAttr","quiz-master-next/questionID","quiz-master-next/questionType","quiz-master-next/answerType","quiz-master-next/questionChanged"],"example":{},"supports":{"html":false},"textdomain":"main-block","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css"}');

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
/*!************************************!*\
  !*** ./src/answer-option/index.js ***!
  \************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./edit */ "./src/answer-option/edit.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./block.json */ "./src/answer-option/block.json");
/* harmony import */ var _component_icon__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../component/icon */ "./src/component/icon.js");




(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_2__.name, {
  icon: _component_icon__WEBPACK_IMPORTED_MODULE_3__.answerOptionBlockIcon,
  __experimentalLabel(attributes, {
    context
  }) {
    const {
      content
    } = attributes;
    const customName = attributes?.metadata?.name;
    const hasContent = content?.length > 0;

    // In the list view, use the answer content as the label.
    // If the content is empty, fall back to the default label.
    if (context === 'list-view' && (customName || hasContent)) {
      return customName || content;
    }
  },
  merge(attributes, attributesToMerge) {
    return {
      content: (attributes.content || '') + (attributesToMerge.content || '')
    };
  },
  edit: _edit__WEBPACK_IMPORTED_MODULE_1__["default"]
});
}();
/******/ })()
;
//# sourceMappingURL=index.js.map