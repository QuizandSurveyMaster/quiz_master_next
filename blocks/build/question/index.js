/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/component/FeaturedImage.js":
/*!****************************************!*\
  !*** ./src/component/FeaturedImage.js ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/hooks */ "@wordpress/hooks");
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_blob__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/blob */ "@wordpress/blob");
/* harmony import */ var _wordpress_blob__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blob__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_notices__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/notices */ "@wordpress/notices");
/* harmony import */ var _wordpress_notices__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_notices__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var _wordpress_core_data__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @wordpress/core-data */ "@wordpress/core-data");
/* harmony import */ var _wordpress_core_data__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(_wordpress_core_data__WEBPACK_IMPORTED_MODULE_8__);
/* harmony import */ var _helper__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ../helper */ "./src/helper.js");

/**
 * WordPress dependencies
 */










const ALLOWED_MEDIA_TYPES = ['image'];

// Used when labels from post type were not yet loaded or when they are not present.
const DEFAULT_FEATURE_IMAGE_LABEL = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Featured image');
const DEFAULT_SET_FEATURE_IMAGE_LABEL = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Set featured image');
const instructions = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('To edit the featured image, you need permission to upload media.'));
const FeaturedImage = ({
  featureImageID,
  onUpdateImage,
  onRemoveImage
}) => {
  const {
    createNotice
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_6__.useDispatch)(_wordpress_notices__WEBPACK_IMPORTED_MODULE_5__.store);
  const toggleRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)();
  const [isLoading, setIsLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const [media, setMedia] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(undefined);
  const {
    mediaFeature,
    mediaUpload
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_6__.useSelect)(select => {
    const {
      getMedia
    } = select(_wordpress_core_data__WEBPACK_IMPORTED_MODULE_8__.store);
    return {
      mediaFeature: (0,_helper__WEBPACK_IMPORTED_MODULE_9__.qsmIsEmpty)(media) && !(0,_helper__WEBPACK_IMPORTED_MODULE_9__.qsmIsEmpty)(featureImageID) && getMedia(featureImageID),
      mediaUpload: select(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_7__.store).getSettings().mediaUpload
    };
  }, []);

  /**Set media data */
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    let shouldSetQSMAttr = true;
    if (shouldSetQSMAttr) {
      if (!(0,_helper__WEBPACK_IMPORTED_MODULE_9__.qsmIsEmpty)(mediaFeature) && 'object' === typeof mediaFeature) {
        setMedia({
          id: featureImageID,
          width: mediaFeature.media_details.width,
          height: mediaFeature.media_details.height,
          url: mediaFeature.source_url,
          alt_text: mediaFeature.alt_text,
          slug: mediaFeature.slug
        });
      }
    }

    //cleanup
    return () => {
      shouldSetQSMAttr = false;
    };
  }, [mediaFeature]);
  function onDropFiles(filesList) {
    mediaUpload({
      allowedTypes: ['image'],
      filesList,
      onFileChange([image]) {
        if ((0,_wordpress_blob__WEBPACK_IMPORTED_MODULE_4__.isBlobURL)(image?.url)) {
          setIsLoading(true);
          return;
        }
        onUpdateImage(image);
        setIsLoading(false);
      },
      onError(message) {
        createNotice('error', message, {
          isDismissible: true,
          type: 'snackbar'
        });
      }
    });
  }
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "editor-post-featured-image"
  }, media && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    id: `editor-post-featured-image-${featureImageID}-describedby`,
    className: "hidden"
  }, media.alt_text && (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.sprintf)(
  // Translators: %s: The selected image alt text.
  (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Current image: %s'), media.alt_text), !media.alt_text && (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.sprintf)(
  // Translators: %s: The selected image filename.
  (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('The current image has no alternative text. The file name is: %s'), media.slug)), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_7__.MediaUploadCheck, {
    fallback: instructions
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_7__.MediaUpload, {
    title: DEFAULT_FEATURE_IMAGE_LABEL,
    onSelect: media => {
      setMedia(media);
      onUpdateImage(media);
    },
    unstableFeaturedImageFlow: true,
    allowedTypes: ALLOWED_MEDIA_TYPES,
    modalClass: "editor-post-featured-image__media-modal",
    render: ({
      open
    }) => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "editor-post-featured-image__container"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Button, {
      ref: toggleRef,
      className: !featureImageID ? 'editor-post-featured-image__toggle' : 'editor-post-featured-image__preview',
      onClick: open,
      "aria-label": !featureImageID ? null : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Edit or replace the image'),
      "aria-describedby": !featureImageID ? null : `editor-post-featured-image-${featureImageID}-describedby`
    }, !!featureImageID && media && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.ResponsiveWrapper, {
      naturalWidth: media.width,
      naturalHeight: media.height,
      isInline: true
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
      src: media.url,
      alt: media.alt_text
    })), isLoading && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Spinner, null), !featureImageID && !isLoading && DEFAULT_SET_FEATURE_IMAGE_LABEL), !!featureImageID && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.__experimentalHStack, {
      className: "editor-post-featured-image__actions"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Button, {
      className: "editor-post-featured-image__action",
      onClick: open
      // Prefer that screen readers use the .editor-post-featured-image__preview button.
      ,
      "aria-hidden": "true"
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Replace')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Button, {
      className: "editor-post-featured-image__action",
      onClick: () => {
        onRemoveImage();
        toggleRef.current.focus();
      }
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Remove'))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.DropZone, {
      onFilesDrop: onDropFiles
    })),
    value: featureImageID
  })));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (FeaturedImage);

/***/ }),

/***/ "./src/component/SelectAddCategory.js":
/*!********************************************!*\
  !*** ./src/component/SelectAddCategory.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _helper__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../helper */ "./src/helper.js");

/**
 * Select or add a category
 */





const SelectAddCategory = ({
  isCategorySelected,
  setUnsetCatgory
}) => {
  //whether showing add category form
  const [showForm, setShowForm] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  //new category name
  const [formCatName, setFormCatName] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
  //new category prent id
  const [formCatParent, setFormCatParent] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(0);
  //new category adding start status
  const [addingNewCategory, setAddingNewCategory] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  //error
  const [newCategoryError, setNewCategoryError] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  //category list 
  const [categories, setCategories] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(qsmBlockData?.hierarchicalCategoryList);

  //get category id-details object 
  const getCategoryIdDetailsObject = categories => {
    let catObj = {};
    categories.forEach(cat => {
      catObj[cat.id] = cat;
      if (0 < cat.children.length) {
        let childCategory = getCategoryIdDetailsObject(cat.children);
        catObj = {
          ...catObj,
          ...childCategory
        };
      }
    });
    return catObj;
  };

  //category id wise details
  const [categoryIdDetails, setCategoryIdDetails] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)((0,_helper__WEBPACK_IMPORTED_MODULE_4__.qsmIsEmpty)(qsmBlockData?.hierarchicalCategoryList) ? {} : getCategoryIdDetailsObject(qsmBlockData.hierarchicalCategoryList));
  const addNewCategoryLabel = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Add New Category ', 'quiz-master-next');
  const noParentOption = `— ${(0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Parent Category ', 'quiz-master-next')} —`;

  //Add new category
  const onAddCategory = async event => {
    event.preventDefault();
    if (newCategoryError || (0,_helper__WEBPACK_IMPORTED_MODULE_4__.qsmIsEmpty)(formCatName) || addingNewCategory) {
      return;
    }
    setAddingNewCategory(true);

    //create a page
    _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
      url: qsmBlockData.ajax_url,
      method: 'POST',
      body: (0,_helper__WEBPACK_IMPORTED_MODULE_4__.qsmFormData)({
        'action': 'save_new_category',
        'name': formCatName,
        'parent': formCatParent
      })
    }).then(res => {
      if (!(0,_helper__WEBPACK_IMPORTED_MODULE_4__.qsmIsEmpty)(res.term_id)) {
        let term_id = res.term_id;
        //console.log("save_new_category",res);
        //set category list
        _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
          path: '/quiz-survey-master/v1/quiz/hierarchical-category-list',
          method: 'POST'
        }).then(res => {
          // console.log("new categorieslist",  res);
          if ('success' == res.status) {
            setCategories(res.result);
            setCategoryIdDetails(res.result);
            //set form
            setFormCatName('');
            setFormCatParent(0);
            //set category selected
            setUnsetCatgory(term_id, getCategoryIdDetailsObject(term.id));
            setAddingNewCategory(false);
          }
        });
      }
    });
  };

  //get category name array
  const getCategoryNameArray = categories => {
    let cats = [];
    categories.forEach(cat => {
      cats.push(cat.name);
      if (0 < cat.children.length) {
        let childCategory = getCategoryNameArray(cat.children);
        cats = [...cats, ...childCategory];
      }
    });
    return cats;
  };

  //check if category name already exists and set new category name
  const checkSetNewCategory = (catName, categories) => {
    categories = getCategoryNameArray(categories);
    console.log("categories", categories);
    if (categories.includes(catName)) {
      setNewCategoryError(catName);
    } else {
      setNewCategoryError(false);
      setFormCatName(catName);
    }
    // categories.forEach( cat => {
    //     if ( cat.name == catName ) {
    //         matchName = true;
    //         return false;
    //     } else if ( 0 < cat.children.length ) {
    //         checkSetNewCategory( catName, cat.children )
    //     }
    // });

    // if ( matchName ) {
    //     setNewCategoryError( matchName );
    // } else {
    //     setNewCategoryError( matchName );
    //     setFormCatName( catName );
    // }
  };

  const renderTerms = categories => {
    return categories.map(term => {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        key: term.id,
        className: "editor-post-taxonomies__hierarchical-terms-choice"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.CheckboxControl, {
        label: term.name,
        checked: isCategorySelected(term.id),
        onChange: () => setUnsetCatgory(term.id, categoryIdDetails)
      }), !!term.children.length && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "editor-post-taxonomies__hierarchical-terms-subchoices"
      }, renderTerms(term.children)));
    });
  };
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Categories', 'quiz-master-next'),
    initialOpen: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "editor-post-taxonomies__hierarchical-terms-list",
    tabIndex: "0",
    role: "group",
    "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Categories', 'quiz-master-next')
  }, renderTerms(categories)), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "qsm-ptb-1"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Button, {
    variant: "link",
    onClick: () => setShowForm(!showForm)
  }, addNewCategoryLabel)), showForm && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("form", {
    onSubmit: onAddCategory
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Flex, {
    direction: "column",
    gap: "1"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TextControl, {
    __nextHasNoMarginBottom: true,
    className: "editor-post-taxonomies__hierarchical-terms-input",
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Category Name', 'quiz-master-next'),
    value: formCatName,
    onChange: formCatName => checkSetNewCategory(formCatName, categories),
    required: true
  }), 0 < categories.length && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TreeSelect, {
    __nextHasNoMarginBottom: true,
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Parent Category', 'quiz-master-next'),
    noOptionLabel: noParentOption,
    onChange: id => setFormCatParent(id),
    selectedId: formCatParent,
    tree: categories
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.FlexItem, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Button, {
    variant: "secondary",
    type: "submit",
    className: "editor-post-taxonomies__hierarchical-terms-submit",
    disabled: newCategoryError || addingNewCategory
  }, addNewCategoryLabel)), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.FlexItem, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    className: "qsm-error-text"
  }, false !== newCategoryError && (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Category ', 'quiz-master-next') + newCategoryError + (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)(' already exists.', 'quiz-master-next'))))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (SelectAddCategory);

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

/***/ "./src/question/edit.js":
/*!******************************!*\
  !*** ./src/question/edit.js ***!
  \******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Edit)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_escape_html__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/escape-html */ "@wordpress/escape-html");
/* harmony import */ var _wordpress_escape_html__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_escape_html__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_notices__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/notices */ "@wordpress/notices");
/* harmony import */ var _wordpress_notices__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_notices__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__);
/* harmony import */ var _component_FeaturedImage__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ../component/FeaturedImage */ "./src/component/FeaturedImage.js");
/* harmony import */ var _component_SelectAddCategory__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ../component/SelectAddCategory */ "./src/component/SelectAddCategory.js");
/* harmony import */ var _component_icon__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ../component/icon */ "./src/component/icon.js");
/* harmony import */ var _helper__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ../helper */ "./src/helper.js");















//check for duplicate questionID attr
const isQuestionIDReserved = (questionIDCheck, clientIdCheck) => {
  const blocksClientIds = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_6__.select)('core/block-editor').getClientIdsWithDescendants();
  return (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmIsEmpty)(blocksClientIds) ? false : blocksClientIds.some(blockClientId => {
    const {
      questionID
    } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_6__.select)('core/block-editor').getBlockAttributes(blockClientId);
    //different Client Id but same questionID attribute means duplicate
    return clientIdCheck !== blockClientId && questionID === questionIDCheck;
  });
};

/**
 * https://github.com/WordPress/gutenberg/blob/HEAD/packages/block-editor/src/components/rich-text/README.md#allowedformats-array
 *
 */
function Edit(props) {
  var _settings$file_upload;
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
  const {
    quiz_name,
    post_id,
    rest_nonce
  } = context['quiz-master-next/quizAttr'];
  const pageID = context['quiz-master-next/pageID'];
  const {
    createNotice
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_6__.useDispatch)(_wordpress_notices__WEBPACK_IMPORTED_MODULE_5__.store);

  //Get finstion to find index of blocks
  const {
    getBlockRootClientId,
    getBlockIndex
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_6__.useSelect)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.store);

  //Get funstion to insert block
  const {
    insertBlock
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_6__.useDispatch)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.store);
  const {
    isChanged = false,
    //use in editor only to detect if any change occur in this block
    questionID,
    type,
    description,
    title,
    correctAnswerInfo,
    commentBox,
    category,
    multicategories = [],
    hint,
    featureImageID,
    featureImageSrc,
    answers,
    answerEditor,
    matchAnswer,
    required,
    isPublished,
    settings = {}
  } = attributes;

  //Variable to decide if correct answer info input field should be available
  const [enableCorrectAnsInfo, setEnableCorrectAnsInfo] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(!(0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmIsEmpty)(correctAnswerInfo));
  //Advance Question modal
  const [isOpenAdvanceQModal, setIsOpenAdvanceQModal] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const proActivated = '1' == qsmBlockData.is_pro_activated;
  const isAdvanceQuestionType = qtype => 14 < parseInt(qtype);

  //Available file types
  const fileTypes = qsmBlockData.file_upload_type.options;

  //Get selected file types
  const selectedFileTypes = () => {
    let file_types = settings?.file_upload_type || qsmBlockData.file_upload_type.default;
    return (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmIsEmpty)(file_types) ? [] : file_types.split(',');
  };

  //Is file type checked
  const isCheckedFileType = fileType => selectedFileTypes().includes(fileType);

  //Set file type
  const setFileTypes = fileType => {
    let file_types = selectedFileTypes();
    if (file_types.includes(fileType)) {
      file_types = file_types.filter(file_type => file_type != fileType);
    } else {
      file_types.push(fileType);
    }
    file_types = file_types.join(',');
    setAttributes({
      settings: {
        ...settings,
        file_upload_type: file_types
      }
    });
  };

  /**Generate question id if not set or in case duplicate questionID ***/
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    let shouldSetID = true;
    if (shouldSetID) {
      if ((0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmIsEmpty)(questionID) || '0' == questionID || !(0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmIsEmpty)(questionID) && isQuestionIDReserved(questionID, clientId)) {
        //create a question
        let newQuestion = (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmFormData)({
          "id": null,
          "rest_nonce": rest_nonce,
          "quizID": quizID,
          "quiz_name": quiz_name,
          "postID": post_id,
          "answerEditor": (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmValueOrDefault)(answerEditor, 'text'),
          "type": (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmValueOrDefault)(type, '0'),
          "name": (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmDecodeHtml)((0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmValueOrDefault)(description)),
          "question_title": (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmValueOrDefault)(title),
          "answerInfo": (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmDecodeHtml)((0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmValueOrDefault)(correctAnswerInfo)),
          "comments": (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmValueOrDefault)(commentBox, '1'),
          "hint": (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmValueOrDefault)(hint),
          "category": (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmValueOrDefault)(category),
          "multicategories": [],
          "required": (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmValueOrDefault)(required, 0),
          "isPublished": (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmValueOrDefault)(isPublished, 1),
          "answers": answers,
          "page": 0,
          "featureImageID": featureImageID,
          "featureImageSrc": featureImageSrc,
          "matchAnswer": null
        });

        //AJAX call
        _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_3___default()({
          path: '/quiz-survey-master/v1/questions',
          method: 'POST',
          body: newQuestion
        }).then(response => {
          if ('success' == response.status) {
            let question_id = response.id;
            setAttributes({
              questionID: question_id
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
    }

    //cleanup
    return () => {
      shouldSetID = false;
    };
  }, []);

  //detect change in question
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    let shouldSetChanged = true;
    if (shouldSetChanged && isSelected && false === isChanged) {
      setAttributes({
        isChanged: true
      });
    }

    //cleanup
    return () => {
      shouldSetChanged = false;
    };
  }, [questionID, isPublished, type, description, title, correctAnswerInfo, commentBox, category, multicategories, hint, featureImageID, featureImageSrc, answers, answerEditor, matchAnswer, required, settings]);

  //add classes
  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.useBlockProps)({
    className: isParentOfSelectedBlock ? ' in-editing-mode is-highlighted ' : ''
  });
  const QUESTION_TEMPLATE = [['qsm/quiz-answer-option', {
    optionID: '0'
  }], ['qsm/quiz-answer-option', {
    optionID: '1'
  }]];

  //Get category ancestor
  const getCategoryAncestors = (termId, categories) => {
    let parents = [];
    if (!(0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmIsEmpty)(categories[termId]) && '0' != categories[termId]['parent']) {
      termId = categories[termId]['parent'];
      parents.push(termId);
      if (!(0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmIsEmpty)(categories[termId]) && '0' != categories[termId]['parent']) {
        let ancestor = getCategoryAncestors(termId, categories);
        parents = [...parents, ...ancestor];
      }
    }
    return (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmUniqueArray)(parents);
  };

  //check if a category is selected
  const isCategorySelected = termId => multicategories.includes(termId);

  //set or unset category
  const setUnsetCatgory = (termId, categories) => {
    let multiCat = (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmIsEmpty)(multicategories) || 0 === multicategories.length ? (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmIsEmpty)(category) ? [] : [category] : multicategories;

    //Case: category unselected
    if (multiCat.includes(termId)) {
      //remove category if already set
      multiCat = multiCat.filter(catID => catID != termId);
      let children = [];
      //check for if any child is selcted
      multiCat.forEach(childCatID => {
        //get ancestors of category
        let ancestorIds = getCategoryAncestors(childCatID, categories);
        //given unselected category is an ancestor of selected category
        if (ancestorIds.includes(termId)) {
          //remove category if already set
          multiCat = multiCat.filter(catID => catID != childCatID);
        }
      });
    } else {
      //add category if not set
      multiCat.push(termId);
      //get ancestors of category
      let ancestorIds = getCategoryAncestors(termId, categories);
      //select all ancestor
      multiCat = [...multiCat, ...ancestorIds];
    }
    multiCat = (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmUniqueArray)(multiCat);
    setAttributes({
      category: '',
      multicategories: [...multiCat]
    });
  };

  //Notes relation to question type
  const notes = ['12', '7', '3', '5', '14'].includes(type) ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Note: Add only correct answer options with their respective points score.', 'quiz-master-next') : '';

  //set Question type
  const setQuestionType = qtype => {
    if (!(0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmIsEmpty)(MicroModal) && !proActivated && ['15', '16', '17'].includes(qtype)) {
      //Show modal for advance question type
      let modalEl = document.getElementById('modal-advanced-question-type');
      if (!(0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmIsEmpty)(modalEl)) {
        MicroModal.show('modal-advanced-question-type');
      }
    } else if (proActivated && isAdvanceQuestionType(qtype)) {
      setIsOpenAdvanceQModal(true);
    } else {
      setAttributes({
        type: qtype
      });
    }
  };

  //insert new Question
  const insertNewQuestion = () => {
    if ((0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmIsEmpty)(props?.name)) {
      console.log("block name not found");
      return true;
    }
    const blockToInsert = (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_7__.createBlock)(props.name);
    const selectBlockOnInsert = true;
    insertBlock(blockToInsert, getBlockIndex(clientId) + 1, getBlockRootClientId(clientId), selectBlockOnInsert);
  };

  //insert new Question
  const insertNewPage = () => {
    const blockToInsert = (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_7__.createBlock)('qsm/quiz-page');
    const currentPageClientID = getBlockRootClientId(clientId);
    const newPageIndex = getBlockIndex(currentPageClientID) + 1;
    const qsmBlockClientID = getBlockRootClientId(currentPageClientID);
    const selectBlockOnInsert = true;
    insertBlock(blockToInsert, newPageIndex, qsmBlockClientID, selectBlockOnInsert);
  };
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.BlockControls, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.ToolbarGroup, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.ToolbarButton, {
    icon: "plus-alt2",
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Add New Question', 'quiz-master-next'),
    onClick: () => insertNewQuestion()
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.ToolbarButton, {
    icon: "welcome-add-page",
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Add New Page', 'quiz-master-next'),
    onClick: () => insertNewPage()
  }))), isOpenAdvanceQModal && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.Modal, {
    contentLabel: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Use QSM Editor for Advanced Question', 'quiz-master-next'),
    className: "qsm-advance-q-modal",
    isDismissible: false,
    size: "small",
    __experimentalHideHeader: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "qsm-modal-body"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h3", {
    className: "qsm-title"
  }, (0,_component_icon__WEBPACK_IMPORTED_MODULE_11__.warningIcon)(), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("br", null), (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Use QSM editor for Advanced Question', 'quiz-master-next')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    className: "qsm-description"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Currently, the block editor doesn't support advanced question type. We are working on it. Alternatively, you can add advanced questions from your QSM's quiz editor.", "quiz-master-next")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "qsm-modal-btn-wrapper"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.Button, {
    variant: "secondary",
    onClick: () => setIsOpenAdvanceQModal(false)
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Cancel', 'quiz-master-next')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.Button, {
    variant: "primary",
    onClick: () => {}
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.ExternalLink, {
    href: qsmBlockData.quiz_settings_url + '&quiz_id=' + quizID
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Add Question from quiz editor', 'quiz-master-next')))))), isAdvanceQuestionType(type) ? (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.InspectorControls, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Question settings', 'quiz-master-next'),
    initialOpen: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", {
    className: "block-editor-block-card__title"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('ID', 'quiz-master-next') + ': ' + questionID), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h3", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Advanced Question Type', 'quiz-master-next')))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    ...blockProps
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h4", {
    className: 'qsm-question-title qsm-error-text'
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Advanced Question Type : ', 'quiz-master-next') + title), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Edit question in QSM ', 'quiz-master-next'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.ExternalLink, {
    href: qsmBlockData.quiz_settings_url + '&quiz_id=' + quizID
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('editor', 'quiz-master-next'))))) : (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.InspectorControls, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Question settings', 'quiz-master-next'),
    initialOpen: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", {
    className: "block-editor-block-card__title"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('ID', 'quiz-master-next') + ': ' + questionID), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.ToggleControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Publish', 'quiz-master-next'),
    checked: !(0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmIsEmpty)(isPublished) && '1' == isPublished,
    onChange: () => setAttributes({
      isPublished: !(0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmIsEmpty)(isPublished) && '1' == isPublished ? 0 : 1
    })
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.SelectControl, {
    label: qsmBlockData.question_type.label,
    value: type || qsmBlockData.question_type.default,
    onChange: type => setQuestionType(type),
    help: (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmIsEmpty)(qsmBlockData.question_type_description[type]) ? '' : qsmBlockData.question_type_description[type] + ' ' + notes,
    __nextHasNoMarginBottom: true
  }, !(0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmIsEmpty)(qsmBlockData.question_type.options) && qsmBlockData.question_type.options.map(qtypes => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("optgroup", {
    label: qtypes.category,
    key: "qtypes" + qtypes.category
  }, qtypes.types.map(qtype => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: qtype.slug,
    key: "qtype" + qtype.slug
  }, qtype.name))))), ['0', '4', '1', '10', '13'].includes(type) && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.SelectControl, {
    label: qsmBlockData.answerEditor.label,
    value: answerEditor || qsmBlockData.answerEditor.default,
    options: qsmBlockData.answerEditor.options,
    onChange: answerEditor => setAttributes({
      answerEditor
    }),
    __nextHasNoMarginBottom: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.ToggleControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Required', 'quiz-master-next'),
    checked: !(0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmIsEmpty)(required) && '1' == required,
    onChange: () => setAttributes({
      required: !(0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmIsEmpty)(required) && '1' == required ? 0 : 1
    })
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.ToggleControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Show Correct Answer Info', 'quiz-master-next'),
    checked: enableCorrectAnsInfo,
    onChange: () => setEnableCorrectAnsInfo(!enableCorrectAnsInfo)
  })), '11' == type && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('File Settings', 'quiz-master-next'),
    initialOpen: false
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.TextControl, {
    type: "number",
    label: qsmBlockData.file_upload_limit.heading,
    value: (_settings$file_upload = settings?.file_upload_limit) !== null && _settings$file_upload !== void 0 ? _settings$file_upload : qsmBlockData.file_upload_limit.default,
    onChange: limit => setAttributes({
      settings: {
        ...settings,
        file_upload_limit: limit
      }
    })
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    className: "qsm-inspector-label"
  }, qsmBlockData.file_upload_type.heading), Object.keys(qsmBlockData.file_upload_type.options).map(filetype => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.CheckboxControl, {
    key: 'filetype-' + filetype,
    label: fileTypes[filetype],
    checked: isCheckedFileType(filetype),
    onChange: () => setFileTypes(filetype)
  }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_component_SelectAddCategory__WEBPACK_IMPORTED_MODULE_10__["default"], {
    isCategorySelected: isCategorySelected,
    setUnsetCatgory: setUnsetCatgory
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Hint', 'quiz-master-next'),
    initialOpen: false
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.TextControl, {
    label: "",
    value: hint,
    onChange: hint => setAttributes({
      hint: (0,_wordpress_escape_html__WEBPACK_IMPORTED_MODULE_2__.escapeAttribute)(hint)
    })
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.PanelBody, {
    title: qsmBlockData.commentBox.heading,
    initialOpen: false
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.SelectControl, {
    label: qsmBlockData.commentBox.label,
    value: commentBox || qsmBlockData.commentBox.default,
    options: qsmBlockData.commentBox.options,
    onChange: commentBox => setAttributes({
      commentBox
    }),
    __nextHasNoMarginBottom: true
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Featured image', 'quiz-master-next'),
    initialOpen: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_component_FeaturedImage__WEBPACK_IMPORTED_MODULE_9__["default"], {
    featureImageID: featureImageID,
    onUpdateImage: mediaDetails => {
      setAttributes({
        featureImageID: mediaDetails.id,
        featureImageSrc: mediaDetails.url
      });
    },
    onRemoveImage: id => {
      setAttributes({
        featureImageID: undefined,
        featureImageSrc: undefined
      });
    }
  }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    ...blockProps
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.RichText, {
    tagName: "h4",
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Question title', 'quiz-master-next'),
    "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Question title', 'quiz-master-next'),
    placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Type your question here', 'quiz-master-next'),
    value: (0,_wordpress_escape_html__WEBPACK_IMPORTED_MODULE_2__.escapeAttribute)(title),
    onChange: title => setAttributes({
      title: (0,_wordpress_escape_html__WEBPACK_IMPORTED_MODULE_2__.escapeAttribute)(title)
    }),
    allowedFormats: [],
    withoutInteractiveFormatting: true,
    className: 'qsm-question-title'
  }), isParentOfSelectedBlock && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.RichText, {
    tagName: "p",
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Question description', 'quiz-master-next'),
    "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Question description', 'quiz-master-next'),
    placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Description goes here... (optional)', 'quiz-master-next'),
    value: (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmDecodeHtml)(description),
    onChange: description => setAttributes({
      description
    }),
    className: 'qsm-question-description',
    __unstableEmbedURLOnPaste: true,
    __unstableAllowPrefixTransformations: true
  }), !['8', '11', '6', '9'].includes(type) && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.InnerBlocks, {
    allowedBlocks: ['qsm/quiz-answer-option'],
    template: QUESTION_TEMPLATE
  }), enableCorrectAnsInfo && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.RichText, {
    tagName: "p",
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Correct Answer Info', 'quiz-master-next'),
    "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Correct Answer Info', 'quiz-master-next'),
    placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Correct answer info goes here', 'quiz-master-next'),
    value: (0,_helper__WEBPACK_IMPORTED_MODULE_12__.qsmDecodeHtml)(correctAnswerInfo),
    onChange: correctAnswerInfo => setAttributes({
      correctAnswerInfo
    }),
    className: 'qsm-question-correct-answer-info',
    __unstableEmbedURLOnPaste: true,
    __unstableAllowPrefixTransformations: true
  }), isParentOfSelectedBlock && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "block-editor-block-list__insertion-point-inserter qsm-add-new-ques-wrapper"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.Button, {
    icon: _component_icon__WEBPACK_IMPORTED_MODULE_11__.plusIcon,
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Add New Question', 'quiz-master-next'),
    tooltipPosition: "bottom",
    onClick: () => insertNewQuestion(),
    variant: "secondary",
    className: "add-new-question-btn block-editor-inserter__toggle"
  }))))));
}

/***/ }),

/***/ "@wordpress/api-fetch":
/*!**********************************!*\
  !*** external ["wp","apiFetch"] ***!
  \**********************************/
/***/ ((module) => {

module.exports = window["wp"]["apiFetch"];

/***/ }),

/***/ "@wordpress/blob":
/*!******************************!*\
  !*** external ["wp","blob"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["blob"];

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

/***/ "@wordpress/core-data":
/*!**********************************!*\
  !*** external ["wp","coreData"] ***!
  \**********************************/
/***/ ((module) => {

module.exports = window["wp"]["coreData"];

/***/ }),

/***/ "@wordpress/data":
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["data"];

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

/***/ "@wordpress/hooks":
/*!*******************************!*\
  !*** external ["wp","hooks"] ***!
  \*******************************/
/***/ ((module) => {

module.exports = window["wp"]["hooks"];

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

/***/ "./src/question/block.json":
/*!*********************************!*\
  !*** ./src/question/block.json ***!
  \*********************************/
/***/ ((module) => {

module.exports = JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"qsm/quiz-question","version":"0.1.0","title":"Question","category":"widgets","parent":["qsm/quiz-page"],"icon":"move","description":"QSM Quiz Question","attributes":{"isChanged":{"type":"string","default":false},"questionID":{"type":"string","default":"0"},"type":{"type":"string","default":"0"},"description":{"type":"string","source":"html","selector":"p","default":""},"title":{"type":"string","default":""},"correctAnswerInfo":{"type":"string","source":"html","selector":"p","default":""},"commentBox":{"type":"string","default":"1"},"category":{"type":"number"},"multicategories":{"type":"array"},"hint":{"type":"string"},"featureImageID":{"type":"number"},"featureImageSrc":{"type":"string"},"answers":{"type":"array"},"answerEditor":{"type":"string","default":"text"},"matchAnswer":{"type":"string","default":"random"},"required":{"type":"string","default":"0"},"isPublished":{"type":"number","default":1},"linked_question":{"type":"string"},"media":{"type":"object"},"settings":{"type":"object"}},"usesContext":["quiz-master-next/quizID","quiz-master-next/pageID","quiz-master-next/quizAttr"],"providesContext":{"quiz-master-next/questionID":"questionID","quiz-master-next/questionType":"type","quiz-master-next/answerType":"answerEditor","quiz-master-next/questionChanged":"isChanged"},"example":{},"supports":{"html":false,"anchor":true,"className":true,"interactivity":{"clientNavigation":true}},"textdomain":"main-block","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css"}');

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
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!*******************************!*\
  !*** ./src/question/index.js ***!
  \*******************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./edit */ "./src/question/edit.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./block.json */ "./src/question/block.json");
/* harmony import */ var _component_icon__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../component/icon */ "./src/component/icon.js");




(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_2__.name, {
  icon: _component_icon__WEBPACK_IMPORTED_MODULE_3__.questionBlockIcon,
  /**
   * @see ./edit.js
   */
  edit: _edit__WEBPACK_IMPORTED_MODULE_1__["default"],
  __experimentalLabel(attributes, {
    context
  }) {
    const {
      title
    } = attributes;
    const customName = attributes?.metadata?.name;
    const hasContent = title?.length > 0;

    // In the list view, use the question title as the label.
    // If the title is empty, fall back to the default label.
    if (context === 'list-view' && (customName || hasContent)) {
      return customName || title;
    }
  }
});
})();

/******/ })()
;
//# sourceMappingURL=index.js.map