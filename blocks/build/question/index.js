!function(){"use strict";var e={n:function(t){var a=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(a,{a:a}),a},d:function(t,a){for(var n in a)e.o(a,n)&&!e.o(t,n)&&Object.defineProperty(t,n,{enumerable:!0,get:a[n]})},o:function(e,t){return Object.prototype.hasOwnProperty.call(e,t)}},t=window.wp.blocks,a=window.wp.element,n=window.wp.i18n,r=window.wp.apiFetch,o=e.n(r),i=window.wp.blockEditor,l=window.wp.notices,s=window.wp.data,c=window.wp.components,m=(window.wp.hooks,window.wp.blob),u=window.wp.coreData;const d=e=>null==e||""===e,p=e=>d(e)||!Array.isArray(e)?e:e.filter(((e,t,a)=>a.indexOf(e)===t)),_=e=>{var t=document.createElement("textarea");return t.innerHTML=e,t.value},g=e=>{let t=document.createElement("div");return t.innerHTML=_(e),t.innerText},h=(e=!1)=>{let t=new FormData;if(t.append("qsm_block_api_call","1"),!1!==e)for(let a in e)e.hasOwnProperty(a)&&t.append(a,e[a]);return t},q=(e,t="")=>d(e)?t:e,f=["image"],E=(0,n.__)("Featured image"),w=(0,n.__)("Set featured image"),x=(0,a.createElement)("p",null,(0,n.__)("To edit the featured image, you need permission to upload media."));var C=({featureImageID:e,onUpdateImage:t,onRemoveImage:r})=>{const{createNotice:o}=(0,s.useDispatch)(l.store),p=(0,a.useRef)(),[_,g]=(0,a.useState)(!1),[h,q]=(0,a.useState)(void 0),{mediaFeature:C,mediaUpload:y}=(0,s.useSelect)((t=>{const{getMedia:a}=t(u.store);return{mediaFeature:d(h)&&!d(e)&&a(e),mediaUpload:t(i.store).getSettings().mediaUpload}}),[]);function b(e){y({allowedTypes:["image"],filesList:e,onFileChange([e]){(0,m.isBlobURL)(e?.url)?g(!0):(t(e),g(!1))},onError(e){o("error",e,{isDismissible:!0,type:"snackbar"})}})}return(0,a.useEffect)((()=>{let t=!0;return t&&(d(C)||"object"!=typeof C||q({id:e,width:C.media_details.width,height:C.media_details.height,url:C.source_url,alt_text:C.alt_text,slug:C.slug})),()=>{t=!1}}),[C]),(0,a.createElement)("div",{className:"editor-post-featured-image"},h&&(0,a.createElement)("div",{id:`editor-post-featured-image-${e}-describedby`,className:"hidden"},h.alt_text&&(0,n.sprintf)(
// Translators: %s: The selected image alt text.
(0,n.__)("Current image: %s"),h.alt_text),!h.alt_text&&(0,n.sprintf)(
// Translators: %s: The selected image filename.
(0,n.__)("The current image has no alternative text. The file name is: %s"),h.slug)),(0,a.createElement)(i.MediaUploadCheck,{fallback:x},(0,a.createElement)(i.MediaUpload,{title:E,onSelect:e=>{q(e),t(e)},unstableFeaturedImageFlow:!0,allowedTypes:f,modalClass:"editor-post-featured-image__media-modal",render:({open:t})=>(0,a.createElement)("div",{className:"editor-post-featured-image__container"},(0,a.createElement)(c.Button,{ref:p,className:e?"editor-post-featured-image__preview":"editor-post-featured-image__toggle",onClick:t,"aria-label":e?(0,n.__)("Edit or replace the image"):null,"aria-describedby":e?`editor-post-featured-image-${e}-describedby`:null},!!e&&h&&(0,a.createElement)(c.ResponsiveWrapper,{naturalWidth:h.width,naturalHeight:h.height,isInline:!0},(0,a.createElement)("img",{src:h.url,alt:h.alt_text})),_&&(0,a.createElement)(c.Spinner,null),!e&&!_&&w),!!e&&(0,a.createElement)(c.__experimentalHStack,{className:"editor-post-featured-image__actions"},(0,a.createElement)(c.Button,{className:"editor-post-featured-image__action",onClick:t,"aria-hidden":"true"},(0,n.__)("Replace")),(0,a.createElement)(c.Button,{className:"editor-post-featured-image__action",onClick:()=>{r(),p.current.focus()}},(0,n.__)("Remove"))),(0,a.createElement)(c.DropZone,{onFilesDrop:b})),value:e})))},y=({isCategorySelected:e,setUnsetCatgory:t})=>{const[r,i]=(0,a.useState)(!1),[l,s]=(0,a.useState)(""),[m,u]=(0,a.useState)(0),[p,_]=(0,a.useState)(!1),[g,q]=(0,a.useState)(!1),[f,E]=(0,a.useState)(qsmBlockData?.hierarchicalCategoryList),w=e=>{let t={};return e.forEach((e=>{if(t[e.id]=e,0<e.children.length){let a=w(e.children);t={...t,...a}}})),t},[x,C]=(0,a.useState)(d(qsmBlockData?.hierarchicalCategoryList)?{}:w(qsmBlockData.hierarchicalCategoryList)),y=(0,n.__)("Add New Category ","quiz-master-next"),b=`— ${(0,n.__)("Parent Category ","quiz-master-next")} —`,k=e=>{let t=[];return e.forEach((e=>{if(t.push(e.name),0<e.children.length){let a=k(e.children);t=[...t,...a]}})),t},B=n=>n.map((n=>(0,a.createElement)("div",{key:n.id,className:"editor-post-taxonomies__hierarchical-terms-choice"},(0,a.createElement)(c.CheckboxControl,{label:n.name,checked:e(n.id),onChange:()=>t(n.id,x)}),!!n.children.length&&(0,a.createElement)("div",{className:"editor-post-taxonomies__hierarchical-terms-subchoices"},B(n.children)))));return(0,a.createElement)(c.PanelBody,{title:(0,n.__)("Categories","quiz-master-next"),initialOpen:!0},(0,a.createElement)("div",{className:"editor-post-taxonomies__hierarchical-terms-list",tabIndex:"0",role:"group","aria-label":(0,n.__)("Categories","quiz-master-next")},B(f)),(0,a.createElement)("div",{className:"qsm-ptb-1"},(0,a.createElement)(c.Button,{variant:"link",onClick:()=>i(!r)},y)),r&&(0,a.createElement)("form",{onSubmit:async e=>{e.preventDefault(),g||d(l)||p||(_(!0),o()({url:qsmBlockData.ajax_url,method:"POST",body:h({action:"save_new_category",name:l,parent:m})}).then((e=>{if(!d(e.term_id)){let a=e.term_id;o()({path:"/quiz-survey-master/v1/quiz/hierarchical-category-list",method:"POST"}).then((e=>{"success"==e.status&&(E(e.result),C(e.result),s(""),u(0),t(a,w(term.id)),_(!1))}))}})))}},(0,a.createElement)(c.Flex,{direction:"column",gap:"1"},(0,a.createElement)(c.TextControl,{__nextHasNoMarginBottom:!0,className:"editor-post-taxonomies__hierarchical-terms-input",label:(0,n.__)("Category Name","quiz-master-next"),value:l,onChange:e=>((e,t)=>{t=k(t),console.log("categories",t),t.includes(e)?q(e):(q(!1),s(e))})(e,f),required:!0}),0<f.length&&(0,a.createElement)(c.TreeSelect,{__nextHasNoMarginBottom:!0,label:(0,n.__)("Parent Category","quiz-master-next"),noOptionLabel:b,onChange:e=>u(e),selectedId:m,tree:f}),(0,a.createElement)(c.FlexItem,null,(0,a.createElement)(c.Button,{variant:"secondary",type:"submit",className:"editor-post-taxonomies__hierarchical-terms-submit",disabled:g||p},y)),(0,a.createElement)(c.FlexItem,null,(0,a.createElement)("p",{className:"qsm-error-text"},!1!==g&&(0,n.__)("Category ","quiz-master-next")+g+(0,n.__)(" already exists.","quiz-master-next"))))))},b=JSON.parse('{"u2":"qsm/quiz-question"}');(0,t.registerBlockType)(b.u2,{icon:()=>(0,a.createElement)(c.Icon,{icon:()=>(0,a.createElement)("svg",{width:"25",height:"25",viewBox:"0 0 25 25",fill:"none",xmlns:"http://www.w3.org/2000/svg"},(0,a.createElement)("rect",{x:"0.102539",y:"0.101562",width:"24",height:"24",rx:"4.68852",fill:"#ADADAD"}),(0,a.createElement)("path",{d:"M17.0475 17.191C17.2367 17.3683 17.3313 17.5752 17.3313 17.8117C17.3313 18.06 17.2426 18.2787 17.0653 18.4679C16.8879 18.6453 16.6751 18.734 16.4268 18.734C16.1667 18.734 15.9538 18.6512 15.7883 18.4857L14.937 17.6521C13.8492 18.4088 12.5959 18.7872 11.177 18.7872C10.0301 18.7872 9.01325 18.533 8.12646 18.0245C7.2515 17.5161 6.57163 16.8126 6.08685 15.914C5.6139 15.0035 5.37742 13.9631 5.37742 12.7925C5.37742 11.5273 5.64937 10.41 6.19327 9.44044C6.74898 8.45907 7.48206 7.70234 8.3925 7.17027C9.31475 6.6382 10.308 6.37216 11.3721 6.37216C12.4481 6.37216 13.459 6.64411 14.4049 7.18801C15.3508 7.72008 16.1075 8.46498 16.6751 9.42271C17.2426 10.3804 17.5264 11.4505 17.5264 12.6329C17.5264 14.0636 17.1007 15.3287 16.2494 16.4283L17.0475 17.191ZM11.177 17.1732C12.0874 17.1732 12.9269 16.9249 13.6955 16.4283L12.5604 15.311C12.3949 15.1454 12.3121 14.9799 12.3121 14.8144C12.3121 14.6015 12.4244 14.3887 12.6491 14.1759C12.8855 13.9631 13.122 13.8566 13.3585 13.8566C13.5122 13.8566 13.6364 13.9039 13.7309 13.9985L14.9724 15.1868C15.4927 14.4183 15.7528 13.5492 15.7528 12.5797C15.7528 11.7284 15.5518 10.9539 15.1498 10.2563C14.7596 9.54686 14.2335 8.99114 13.5713 8.58913C12.9092 8.18712 12.1998 7.98611 11.443 7.98611C10.6981 7.98611 9.99462 8.18121 9.33249 8.57139C8.67036 8.94975 8.13828 9.49956 7.73627 10.2208C7.34609 10.9421 7.15099 11.7756 7.15099 12.7216C7.15099 13.6083 7.32244 14.3887 7.66533 15.0627C8.02005 15.7366 8.49891 16.2569 9.10192 16.6234C9.71676 16.99 10.4085 17.1732 11.177 17.1732Z",fill:"white"}))}),edit:function(e){if("undefined"==typeof qsmBlockData)return null;const{className:r,attributes:m,setAttributes:u,isSelected:f,clientId:E,context:w}=e,x=(0,s.useSelect)((e=>f||e("core/block-editor").hasSelectedInnerBlock(E,!0))),b=w["quiz-master-next/quizID"],{quiz_name:k,post_id:B,rest_nonce:I}=w["quiz-master-next/quizAttr"],{createNotice:v}=(w["quiz-master-next/pageID"],(0,s.useDispatch)(l.store)),{getBlockRootClientId:D,getBlockIndex:z}=(0,s.useSelect)(i.store),{insertBlock:N}=(0,s.useDispatch)(i.store),{isChanged:S=!1,questionID:T,type:A,description:F,title:P,correctAnswerInfo:M,commentBox:O,category:R,multicategories:L=[],hint:U,featureImageID:H,featureImageSrc:Q,answers:j,answerEditor:W,matchAnswer:Z,required:$}=m,G="1"==qsmBlockData.is_pro_activated,J=e=>14<parseInt(e);(0,a.useEffect)((()=>{let e=!0;if(e&&(d(T)||"0"==T||!d(T)&&((e,t)=>{const a=(0,s.select)("core/block-editor").getClientIdsWithDescendants();return!d(a)&&a.some((a=>{const{questionID:n}=(0,s.select)("core/block-editor").getBlockAttributes(a);return t!==a&&n===e}))})(T,E))){let e=h({id:null,rest_nonce:I,quizID:b,quiz_name:k,postID:B,answerEditor:q(W,"text"),type:q(A,"0"),name:_(q(F)),question_title:q(P),answerInfo:_(q(M)),comments:q(O,"1"),hint:q(U),category:q(R),multicategories:[],required:q($,0),answers:j,page:0,featureImageID:H,featureImageSrc:Q,matchAnswer:null});o()({path:"/quiz-survey-master/v1/questions",method:"POST",body:e}).then((e=>{if("success"==e.status){let t=e.id;u({questionID:t})}})).catch((e=>{console.log("error",e),v("error",e.message,{isDismissible:!0,type:"snackbar"})}))}return()=>{e=!1}}),[]),(0,a.useEffect)((()=>{let e=!0;return e&&f&&!1===S&&u({isChanged:!0}),()=>{e=!1}}),[T,A,F,P,M,O,R,L,U,H,Q,j,W,Z,$]);const K=(0,i.useBlockProps)({className:x?" in-editing-mode":""}),V=(e,t)=>{let a=[];if(!d(t[e])&&"0"!=t[e].parent&&(e=t[e].parent,a.push(e),!d(t[e])&&"0"!=t[e].parent)){let n=V(e,t);a=[...a,...n]}return p(a)},X=["12","7","3","5","14"].includes(A)?(0,n.__)("Note: Add only correct answer options with their respective points score.","quiz-master-next"):"";return(0,a.createElement)(a.Fragment,null,(0,a.createElement)(i.BlockControls,null,(0,a.createElement)(c.ToolbarGroup,null,(0,a.createElement)(c.ToolbarButton,{icon:"plus-alt2",label:(0,n.__)("Add New Question","quiz-master-next"),onClick:()=>(()=>{if(d(e?.name))return console.log("block name not found"),!0;const a=(0,t.createBlock)(e.name);N(a,z(E)+1,D(E),!0)})()}))),J(A)?(0,a.createElement)(a.Fragment,null,(0,a.createElement)(i.InspectorControls,null,(0,a.createElement)(c.PanelBody,{title:(0,n.__)("Question settings","quiz-master-next"),initialOpen:!0},(0,a.createElement)("h2",{className:"block-editor-block-card__title"},(0,n.__)("ID","quiz-master-next")+": "+T),(0,a.createElement)("h3",null,(0,n.__)("Advanced Question Type","quiz-master-next")))),(0,a.createElement)("div",{...K},(0,a.createElement)("h4",{className:"qsm-question-title qsm-error-text"},(0,n.__)("Advanced Question Type : ","quiz-master-next")+P))):(0,a.createElement)(a.Fragment,null,(0,a.createElement)(i.InspectorControls,null,(0,a.createElement)(c.PanelBody,{title:(0,n.__)("Question settings","quiz-master-next"),initialOpen:!0},(0,a.createElement)("h2",{className:"block-editor-block-card__title"},(0,n.__)("ID","quiz-master-next")+": "+T),(0,a.createElement)(c.SelectControl,{label:qsmBlockData.question_type.label,value:A||qsmBlockData.question_type.default,onChange:e=>(e=>{if(d(MicroModal)||G||!["15","16","17"].includes(e))u({type:e});else{let e=document.getElementById("modal-advanced-question-type");d(e)||MicroModal.show("modal-advanced-question-type")}})(e),help:d(qsmBlockData.question_type_description[A])?"":qsmBlockData.question_type_description[A]+" "+X,__nextHasNoMarginBottom:!0},!d(qsmBlockData.question_type.options)&&qsmBlockData.question_type.options.map((e=>(0,a.createElement)("optgroup",{label:e.category,key:"qtypes"+e.category},e.types.map((e=>(0,a.createElement)("option",{value:e.slug,key:"qtype"+e.slug,disabled:G&&J(e.slug)},e.name))))))),["0","4","1","10","13"].includes(A)&&(0,a.createElement)(c.SelectControl,{label:qsmBlockData.answerEditor.label,value:W||qsmBlockData.answerEditor.default,options:qsmBlockData.answerEditor.options,onChange:e=>u({answerEditor:e}),__nextHasNoMarginBottom:!0}),(0,a.createElement)(c.ToggleControl,{label:(0,n.__)("Required","quiz-master-next"),checked:!d($)&&"1"==$,onChange:()=>u({required:d($)||"1"!=$?1:0})})),(0,a.createElement)(y,{isCategorySelected:e=>L.includes(e),setUnsetCatgory:(e,t)=>{let a=d(L)||0===L.length?d(R)?[]:[R]:L;if(a.includes(e))a=a.filter((t=>t!=e)),a.forEach((n=>{V(n,t).includes(e)&&(a=a.filter((e=>e!=n)))}));else{a.push(e);let n=V(e,t);a=[...a,...n]}a=p(a),u({category:"",multicategories:[...a]})}}),(0,a.createElement)(c.PanelBody,{title:(0,n.__)("Featured image","quiz-master-next"),initialOpen:!0},(0,a.createElement)(C,{featureImageID:H,onUpdateImage:e=>{u({featureImageID:e.id,featureImageSrc:e.url})},onRemoveImage:e=>{u({featureImageID:void 0,featureImageSrc:void 0})}})),(0,a.createElement)(c.PanelBody,{title:qsmBlockData.commentBox.heading},(0,a.createElement)(c.SelectControl,{label:qsmBlockData.commentBox.label,value:O||qsmBlockData.commentBox.default,options:qsmBlockData.commentBox.options,onChange:e=>u({commentBox:e}),__nextHasNoMarginBottom:!0}))),(0,a.createElement)("div",{...K},(0,a.createElement)(i.RichText,{tagName:"h4",title:(0,n.__)("Question title","quiz-master-next"),"aria-label":(0,n.__)("Question title","quiz-master-next"),placeholder:(0,n.__)("Type your question here","quiz-master-next"),value:P,onChange:e=>u({title:g(e)}),allowedFormats:[],withoutInteractiveFormatting:!0,className:"qsm-question-title"}),x&&(0,a.createElement)(a.Fragment,null,(0,a.createElement)(i.RichText,{tagName:"p",title:(0,n.__)("Question description","quiz-master-next"),"aria-label":(0,n.__)("Question description","quiz-master-next"),placeholder:(0,n.__)("Description goes here","quiz-master-next"),value:_(F),onChange:e=>u({description:e}),className:"qsm-question-description",__unstableEmbedURLOnPaste:!0,__unstableAllowPrefixTransformations:!0}),!["8","11","6","9"].includes(A)&&(0,a.createElement)(i.InnerBlocks,{allowedBlocks:["qsm/quiz-answer-option"],template:[["qsm/quiz-answer-option",{optionID:"0"}],["qsm/quiz-answer-option",{optionID:"1"}]]}),(0,a.createElement)(i.RichText,{tagName:"p",title:(0,n.__)("Correct Answer Info","quiz-master-next"),"aria-label":(0,n.__)("Correct Answer Info","quiz-master-next"),placeholder:(0,n.__)("Correct answer info goes here","quiz-master-next"),value:_(M),onChange:e=>u({correctAnswerInfo:e}),className:"qsm-question-correct-answer-info",__unstableEmbedURLOnPaste:!0,__unstableAllowPrefixTransformations:!0}),(0,a.createElement)(i.RichText,{tagName:"p",title:(0,n.__)("Hint","quiz-master-next"),"aria-label":(0,n.__)("Hint","quiz-master-next"),placeholder:(0,n.__)("hint goes here","quiz-master-next"),value:U,onChange:e=>u({hint:g(e)}),allowedFormats:[],withoutInteractiveFormatting:!0,className:"qsm-question-hint"})))))}})}();