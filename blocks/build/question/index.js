!function(){"use strict";var e={n:function(t){var a=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(a,{a:a}),a},d:function(t,a){for(var n in a)e.o(a,n)&&!e.o(t,n)&&Object.defineProperty(t,n,{enumerable:!0,get:a[n]})},o:function(e,t){return Object.prototype.hasOwnProperty.call(e,t)}},t=window.wp.blocks,a=window.wp.element,n=window.wp.i18n,r=window.wp.apiFetch,l=e.n(r),i=window.wp.blockEditor,o=window.wp.notices,s=window.wp.data,c=window.wp.components,m=(window.wp.hooks,window.wp.blob),u=window.wp.coreData;const d=e=>null==e||""===e,_=e=>d(e)||!Array.isArray(e)?e:e.filter(((e,t,a)=>a.indexOf(e)===t)),p=e=>{var t=document.createElement("textarea");return t.innerHTML=e,t.value},g=e=>{let t=document.createElement("div");return t.innerHTML=p(e),t.innerText},h=(e=!1)=>{let t=new FormData;if(t.append("qsm_block_api_call","1"),!1!==e)for(let a in e)e.hasOwnProperty(a)&&t.append(a,e[a]);return t},q=(e,t="")=>d(e)?t:e,f=["image"],E=(0,n.__)("Featured image"),x=(0,n.__)("Set featured image"),C=(0,a.createElement)("p",null,(0,n.__)("To edit the featured image, you need permission to upload media."));var w=({featureImageID:e,onUpdateImage:t,onRemoveImage:r})=>{const{createNotice:l}=(0,s.useDispatch)(o.store),_=(0,a.useRef)(),[p,g]=(0,a.useState)(!1),[h,q]=(0,a.useState)(void 0),{mediaFeature:w,mediaUpload:y}=(0,s.useSelect)((t=>{const{getMedia:a}=t(u.store);return{mediaFeature:d(h)&&!d(e)&&a(e),mediaUpload:t(i.store).getSettings().mediaUpload}}),[]);function b(e){y({allowedTypes:["image"],filesList:e,onFileChange([e]){(0,m.isBlobURL)(e?.url)?g(!0):(t(e),g(!1))},onError(e){l("error",e,{isDismissible:!0,type:"snackbar"})}})}return(0,a.useEffect)((()=>{let t=!0;return t&&(d(w)||"object"!=typeof w||q({id:e,width:w.media_details.width,height:w.media_details.height,url:w.source_url,alt_text:w.alt_text,slug:w.slug})),()=>{t=!1}}),[w]),(0,a.createElement)("div",{className:"editor-post-featured-image"},h&&(0,a.createElement)("div",{id:`editor-post-featured-image-${e}-describedby`,className:"hidden"},h.alt_text&&(0,n.sprintf)(
// Translators: %s: The selected image alt text.
(0,n.__)("Current image: %s"),h.alt_text),!h.alt_text&&(0,n.sprintf)(
// Translators: %s: The selected image filename.
(0,n.__)("The current image has no alternative text. The file name is: %s"),h.slug)),(0,a.createElement)(i.MediaUploadCheck,{fallback:C},(0,a.createElement)(i.MediaUpload,{title:E,onSelect:e=>{q(e),t(e)},unstableFeaturedImageFlow:!0,allowedTypes:f,modalClass:"editor-post-featured-image__media-modal",render:({open:t})=>(0,a.createElement)("div",{className:"editor-post-featured-image__container"},(0,a.createElement)(c.Button,{ref:_,className:e?"editor-post-featured-image__preview":"editor-post-featured-image__toggle",onClick:t,"aria-label":e?(0,n.__)("Edit or replace the image"):null,"aria-describedby":e?`editor-post-featured-image-${e}-describedby`:null},!!e&&h&&(0,a.createElement)(c.ResponsiveWrapper,{naturalWidth:h.width,naturalHeight:h.height,isInline:!0},(0,a.createElement)("img",{src:h.url,alt:h.alt_text})),p&&(0,a.createElement)(c.Spinner,null),!e&&!p&&x),!!e&&(0,a.createElement)(c.__experimentalHStack,{className:"editor-post-featured-image__actions"},(0,a.createElement)(c.Button,{className:"editor-post-featured-image__action",onClick:t,"aria-hidden":"true"},(0,n.__)("Replace")),(0,a.createElement)(c.Button,{className:"editor-post-featured-image__action",onClick:()=>{r(),_.current.focus()}},(0,n.__)("Remove"))),(0,a.createElement)(c.DropZone,{onFilesDrop:b})),value:e})))},y=({isCategorySelected:e,setUnsetCatgory:t})=>{const[r,i]=(0,a.useState)(!1),[o,s]=(0,a.useState)(""),[m,u]=(0,a.useState)(0),[_,p]=(0,a.useState)(!1),[g,q]=(0,a.useState)(!1),[f,E]=(0,a.useState)(qsmBlockData?.hierarchicalCategoryList),x=e=>{let t={};return e.forEach((e=>{if(t[e.id]=e,0<e.children.length){let a=x(e.children);t={...t,...a}}})),t},[C,w]=(0,a.useState)(d(qsmBlockData?.hierarchicalCategoryList)?{}:x(qsmBlockData.hierarchicalCategoryList)),y=(0,n.__)("Add New Category ","quiz-master-next"),b=`— ${(0,n.__)("Parent Category ","quiz-master-next")} —`,k=e=>{let t=[];return e.forEach((e=>{if(t.push(e.name),0<e.children.length){let a=k(e.children);t=[...t,...a]}})),t},v=n=>n.map((n=>(0,a.createElement)("div",{key:n.id,className:"editor-post-taxonomies__hierarchical-terms-choice"},(0,a.createElement)(c.CheckboxControl,{label:n.name,checked:e(n.id),onChange:()=>t(n.id,C)}),!!n.children.length&&(0,a.createElement)("div",{className:"editor-post-taxonomies__hierarchical-terms-subchoices"},v(n.children)))));return(0,a.createElement)(c.PanelBody,{title:(0,n.__)("Categories","quiz-master-next"),initialOpen:!0},(0,a.createElement)("div",{className:"editor-post-taxonomies__hierarchical-terms-list",tabIndex:"0",role:"group","aria-label":(0,n.__)("Categories","quiz-master-next")},v(f)),(0,a.createElement)("div",{className:"qsm-ptb-1"},(0,a.createElement)(c.Button,{variant:"link",onClick:()=>i(!r)},y)),r&&(0,a.createElement)("form",{onSubmit:async e=>{e.preventDefault(),g||d(o)||_||(p(!0),l()({url:qsmBlockData.ajax_url,method:"POST",body:h({action:"save_new_category",name:o,parent:m})}).then((e=>{if(!d(e.term_id)){let a=e.term_id;l()({path:"/quiz-survey-master/v1/quiz/hierarchical-category-list",method:"POST"}).then((e=>{"success"==e.status&&(E(e.result),w(e.result),s(""),u(0),t(a,x(term.id)),p(!1))}))}})))}},(0,a.createElement)(c.Flex,{direction:"column",gap:"1"},(0,a.createElement)(c.TextControl,{__nextHasNoMarginBottom:!0,className:"editor-post-taxonomies__hierarchical-terms-input",label:(0,n.__)("Category Name","quiz-master-next"),value:o,onChange:e=>((e,t)=>{t=k(t),console.log("categories",t),t.includes(e)?q(e):(q(!1),s(e))})(e,f),required:!0}),0<f.length&&(0,a.createElement)(c.TreeSelect,{__nextHasNoMarginBottom:!0,label:(0,n.__)("Parent Category","quiz-master-next"),noOptionLabel:b,onChange:e=>u(e),selectedId:m,tree:f}),(0,a.createElement)(c.FlexItem,null,(0,a.createElement)(c.Button,{variant:"secondary",type:"submit",className:"editor-post-taxonomies__hierarchical-terms-submit",disabled:g||_},y)),(0,a.createElement)(c.FlexItem,null,(0,a.createElement)("p",{className:"qsm-error-text"},!1!==g&&(0,n.__)("Category ","quiz-master-next")+g+(0,n.__)(" already exists.","quiz-master-next"))))))},b=JSON.parse('{"u2":"qsm/quiz-question"}');(0,t.registerBlockType)(b.u2,{icon:()=>(0,a.createElement)(c.Icon,{icon:()=>(0,a.createElement)("svg",{width:"25",height:"25",viewBox:"0 0 25 25",fill:"none",xmlns:"http://www.w3.org/2000/svg"},(0,a.createElement)("rect",{x:"0.102539",y:"0.101562",width:"24",height:"24",rx:"4.68852",fill:"#ADADAD"}),(0,a.createElement)("path",{d:"M17.0475 17.191C17.2367 17.3683 17.3313 17.5752 17.3313 17.8117C17.3313 18.06 17.2426 18.2787 17.0653 18.4679C16.8879 18.6453 16.6751 18.734 16.4268 18.734C16.1667 18.734 15.9538 18.6512 15.7883 18.4857L14.937 17.6521C13.8492 18.4088 12.5959 18.7872 11.177 18.7872C10.0301 18.7872 9.01325 18.533 8.12646 18.0245C7.2515 17.5161 6.57163 16.8126 6.08685 15.914C5.6139 15.0035 5.37742 13.9631 5.37742 12.7925C5.37742 11.5273 5.64937 10.41 6.19327 9.44044C6.74898 8.45907 7.48206 7.70234 8.3925 7.17027C9.31475 6.6382 10.308 6.37216 11.3721 6.37216C12.4481 6.37216 13.459 6.64411 14.4049 7.18801C15.3508 7.72008 16.1075 8.46498 16.6751 9.42271C17.2426 10.3804 17.5264 11.4505 17.5264 12.6329C17.5264 14.0636 17.1007 15.3287 16.2494 16.4283L17.0475 17.191ZM11.177 17.1732C12.0874 17.1732 12.9269 16.9249 13.6955 16.4283L12.5604 15.311C12.3949 15.1454 12.3121 14.9799 12.3121 14.8144C12.3121 14.6015 12.4244 14.3887 12.6491 14.1759C12.8855 13.9631 13.122 13.8566 13.3585 13.8566C13.5122 13.8566 13.6364 13.9039 13.7309 13.9985L14.9724 15.1868C15.4927 14.4183 15.7528 13.5492 15.7528 12.5797C15.7528 11.7284 15.5518 10.9539 15.1498 10.2563C14.7596 9.54686 14.2335 8.99114 13.5713 8.58913C12.9092 8.18712 12.1998 7.98611 11.443 7.98611C10.6981 7.98611 9.99462 8.18121 9.33249 8.57139C8.67036 8.94975 8.13828 9.49956 7.73627 10.2208C7.34609 10.9421 7.15099 11.7756 7.15099 12.7216C7.15099 13.6083 7.32244 14.3887 7.66533 15.0627C8.02005 15.7366 8.49891 16.2569 9.10192 16.6234C9.71676 16.99 10.4085 17.1732 11.177 17.1732Z",fill:"white"}))}),edit:function(e){var r;if("undefined"==typeof qsmBlockData)return null;const{className:m,attributes:u,setAttributes:f,isSelected:E,clientId:x,context:C}=e,b=(0,s.useSelect)((e=>E||e("core/block-editor").hasSelectedInnerBlock(x,!0))),k=C["quiz-master-next/quizID"],{quiz_name:v,post_id:B,rest_nonce:z}=C["quiz-master-next/quizAttr"],{createNotice:D}=(C["quiz-master-next/pageID"],(0,s.useDispatch)(o.store)),{getBlockRootClientId:I,getBlockIndex:N}=(0,s.useSelect)(i.store),{insertBlock:S}=(0,s.useDispatch)(i.store),{isChanged:T=!1,questionID:A,type:M,description:L,title:P,correctAnswerInfo:F,commentBox:O,category:Q,multicategories:H=[],hint:R,featureImageID:U,featureImageSrc:j,answers:W,answerEditor:Z,matchAnswer:V,required:$,settings:G={}}=u,[J,K]=(0,a.useState)(!d(F)),[X,Y]=(0,a.useState)(!1),ee="1"==qsmBlockData.is_pro_activated,te=e=>14<parseInt(e),ae=qsmBlockData.file_upload_type.options,ne=()=>{let e=G?.file_upload_type||qsmBlockData.file_upload_type.default;return d(e)?[]:e.split(",")};(0,a.useEffect)((()=>{let e=!0;if(e&&(d(A)||"0"==A||!d(A)&&((e,t)=>{const a=(0,s.select)("core/block-editor").getClientIdsWithDescendants();return!d(a)&&a.some((a=>{const{questionID:n}=(0,s.select)("core/block-editor").getBlockAttributes(a);return t!==a&&n===e}))})(A,x))){let e=h({id:null,rest_nonce:z,quizID:k,quiz_name:v,postID:B,answerEditor:q(Z,"text"),type:q(M,"0"),name:p(q(L)),question_title:q(P),answerInfo:p(q(F)),comments:q(O,"1"),hint:q(R),category:q(Q),multicategories:[],required:q($,0),answers:W,page:0,featureImageID:U,featureImageSrc:j,matchAnswer:null});l()({path:"/quiz-survey-master/v1/questions",method:"POST",body:e}).then((e=>{if("success"==e.status){let t=e.id;f({questionID:t})}})).catch((e=>{console.log("error",e),D("error",e.message,{isDismissible:!0,type:"snackbar"})}))}return()=>{e=!1}}),[]),(0,a.useEffect)((()=>{let e=!0;return e&&E&&!1===T&&f({isChanged:!0}),()=>{e=!1}}),[A,M,L,P,F,O,Q,H,R,U,j,W,Z,V,$,G]);const re=(0,i.useBlockProps)({className:b?" in-editing-mode":""}),le=(e,t)=>{let a=[];if(!d(t[e])&&"0"!=t[e].parent&&(e=t[e].parent,a.push(e),!d(t[e])&&"0"!=t[e].parent)){let n=le(e,t);a=[...a,...n]}return _(a)},ie=["12","7","3","5","14"].includes(M)?(0,n.__)("Note: Add only correct answer options with their respective points score.","quiz-master-next"):"",oe=()=>{if(d(e?.name))return console.log("block name not found"),!0;const a=(0,t.createBlock)(e.name);S(a,N(x)+1,I(x),!0)};return(0,a.createElement)(a.Fragment,null,(0,a.createElement)(i.BlockControls,null,(0,a.createElement)(c.ToolbarGroup,null,(0,a.createElement)(c.ToolbarButton,{icon:"plus-alt2",label:(0,n.__)("Add New Question","quiz-master-next"),onClick:()=>oe()}),(0,a.createElement)(c.ToolbarButton,{icon:"welcome-add-page",label:(0,n.__)("Add New Page","quiz-master-next"),onClick:()=>(()=>{const e=(0,t.createBlock)("qsm/quiz-page"),a=I(x),n=N(a)+1,r=I(a);S(e,n,r,!0)})()}))),X&&(0,a.createElement)(c.Modal,{contentLabel:(0,n.__)("Use QSM Editor for Advanced Question","quiz-master-next"),className:"qsm-advance-q-modal",isDismissible:!1,size:"small",__experimentalHideHeader:!0},(0,a.createElement)("div",{className:"qsm-modal-body"},(0,a.createElement)("h3",{className:"qsm-title"},(0,a.createElement)(c.Icon,{icon:()=>(0,a.createElement)("svg",{width:"54",height:"54",viewBox:"0 0 54 54",fill:"none",xmlns:"http://www.w3.org/2000/svg"},(0,a.createElement)("path",{d:"M27.1855 23.223V28.0626M15.1794 32.4196C14.0618 34.3554 15.4595 36.7739 17.6934 36.7739H36.6776C38.9102 36.7739 40.3079 34.3554 39.1916 32.4196L29.7008 15.9675C28.5832 14.0317 25.7878 14.0317 24.6702 15.9675L15.1794 32.4196ZM27.1855 31.9343H27.1945V31.9446H27.1855V31.9343Z",stroke:"#B45309",strokeWidth:"1.65929",strokeLinecap:"round",strokeLinejoin:"round"}))}),(0,a.createElement)("br",null),(0,n.__)("Use QSM editor for Advanced Question","quiz-master-next")),(0,a.createElement)("p",{className:"qsm-description"},(0,n.__)("Currently, the block editor doesn't support advanced question type. We are working on it. Alternatively, you can add advanced questions from your QSM's quiz editor.","quiz-master-next")),(0,a.createElement)("div",{className:"qsm-modal-btn-wrapper"},(0,a.createElement)(c.Button,{variant:"secondary",onClick:()=>Y(!1)},(0,n.__)("Cancel","quiz-master-next")),(0,a.createElement)(c.Button,{variant:"primary",onClick:()=>{}},(0,a.createElement)(c.ExternalLink,{href:qsmBlockData.quiz_settings_url+"&quiz_id="+k},(0,n.__)("Add Question from quiz editor","quiz-master-next")))))),te(M)?(0,a.createElement)(a.Fragment,null,(0,a.createElement)(i.InspectorControls,null,(0,a.createElement)(c.PanelBody,{title:(0,n.__)("Question settings","quiz-master-next"),initialOpen:!0},(0,a.createElement)("h2",{className:"block-editor-block-card__title"},(0,n.__)("ID","quiz-master-next")+": "+A),(0,a.createElement)("h3",null,(0,n.__)("Advanced Question Type","quiz-master-next")))),(0,a.createElement)("div",{...re},(0,a.createElement)("h4",{className:"qsm-question-title qsm-error-text"},(0,n.__)("Advanced Question Type : ","quiz-master-next")+P),(0,a.createElement)("p",null,(0,n.__)("Edit question in QSM ","quiz-master-next"),(0,a.createElement)(c.ExternalLink,{href:qsmBlockData.quiz_settings_url+"&quiz_id="+k},(0,n.__)("editor","quiz-master-next"))))):(0,a.createElement)(a.Fragment,null,(0,a.createElement)(i.InspectorControls,null,(0,a.createElement)(c.PanelBody,{title:(0,n.__)("Question settings","quiz-master-next"),initialOpen:!0},(0,a.createElement)("h2",{className:"block-editor-block-card__title"},(0,n.__)("ID","quiz-master-next")+": "+A),(0,a.createElement)(c.SelectControl,{label:qsmBlockData.question_type.label,value:M||qsmBlockData.question_type.default,onChange:e=>(e=>{if(d(MicroModal)||ee||!["15","16","17"].includes(e))ee&&te(e)?Y(!0):f({type:e});else{let e=document.getElementById("modal-advanced-question-type");d(e)||MicroModal.show("modal-advanced-question-type")}})(e),help:d(qsmBlockData.question_type_description[M])?"":qsmBlockData.question_type_description[M]+" "+ie,__nextHasNoMarginBottom:!0},!d(qsmBlockData.question_type.options)&&qsmBlockData.question_type.options.map((e=>(0,a.createElement)("optgroup",{label:e.category,key:"qtypes"+e.category},e.types.map((e=>(0,a.createElement)("option",{value:e.slug,key:"qtype"+e.slug},e.name))))))),["0","4","1","10","13"].includes(M)&&(0,a.createElement)(c.SelectControl,{label:qsmBlockData.answerEditor.label,value:Z||qsmBlockData.answerEditor.default,options:qsmBlockData.answerEditor.options,onChange:e=>f({answerEditor:e}),__nextHasNoMarginBottom:!0}),(0,a.createElement)(c.ToggleControl,{label:(0,n.__)("Required","quiz-master-next"),checked:!d($)&&"1"==$,onChange:()=>f({required:d($)||"1"!=$?1:0})}),(0,a.createElement)(c.ToggleControl,{label:(0,n.__)("Show Correct Answer Info","quiz-master-next"),checked:J,onChange:()=>K(!J)})),"11"==M&&(0,a.createElement)(c.PanelBody,{title:(0,n.__)("File Settings","quiz-master-next"),initialOpen:!1},(0,a.createElement)(c.TextControl,{type:"number",label:qsmBlockData.file_upload_limit.heading,value:null!==(r=G?.file_upload_limit)&&void 0!==r?r:qsmBlockData.file_upload_limit.default,onChange:e=>f({settings:{...G,file_upload_limit:e}})}),(0,a.createElement)("label",{className:"qsm-inspector-label"},qsmBlockData.file_upload_type.heading),Object.keys(qsmBlockData.file_upload_type.options).map((e=>{return(0,a.createElement)(c.CheckboxControl,{key:"filetype-"+e,label:ae[e],checked:(t=e,ne().includes(t)),onChange:()=>(e=>{let t=ne();t.includes(e)?t=t.filter((t=>t!=e)):t.push(e),t=t.join(","),f({settings:{...G,file_upload_type:t}})})(e)});var t}))),(0,a.createElement)(y,{isCategorySelected:e=>H.includes(e),setUnsetCatgory:(e,t)=>{let a=d(H)||0===H.length?d(Q)?[]:[Q]:H;if(a.includes(e))a=a.filter((t=>t!=e)),a.forEach((n=>{le(n,t).includes(e)&&(a=a.filter((e=>e!=n)))}));else{a.push(e);let n=le(e,t);a=[...a,...n]}a=_(a),f({category:"",multicategories:[...a]})}}),(0,a.createElement)(c.PanelBody,{title:(0,n.__)("Hint","quiz-master-next"),initialOpen:!1},(0,a.createElement)(c.TextControl,{label:"",value:R,onChange:e=>f({hint:g(e)})})),(0,a.createElement)(c.PanelBody,{title:qsmBlockData.commentBox.heading,initialOpen:!1},(0,a.createElement)(c.SelectControl,{label:qsmBlockData.commentBox.label,value:O||qsmBlockData.commentBox.default,options:qsmBlockData.commentBox.options,onChange:e=>f({commentBox:e}),__nextHasNoMarginBottom:!0})),(0,a.createElement)(c.PanelBody,{title:(0,n.__)("Featured image","quiz-master-next"),initialOpen:!0},(0,a.createElement)(w,{featureImageID:U,onUpdateImage:e=>{f({featureImageID:e.id,featureImageSrc:e.url})},onRemoveImage:e=>{f({featureImageID:void 0,featureImageSrc:void 0})}}))),(0,a.createElement)("div",{...re},(0,a.createElement)(i.RichText,{tagName:"h4",title:(0,n.__)("Question title","quiz-master-next"),"aria-label":(0,n.__)("Question title","quiz-master-next"),placeholder:(0,n.__)("Type your question here","quiz-master-next"),value:P,onChange:e=>f({title:g(e)}),allowedFormats:[],withoutInteractiveFormatting:!0,className:"qsm-question-title"}),b&&(0,a.createElement)(a.Fragment,null,(0,a.createElement)(i.RichText,{tagName:"p",title:(0,n.__)("Question description","quiz-master-next"),"aria-label":(0,n.__)("Question description","quiz-master-next"),placeholder:(0,n.__)("Description goes here... (optional)","quiz-master-next"),value:p(L),onChange:e=>f({description:e}),className:"qsm-question-description",__unstableEmbedURLOnPaste:!0,__unstableAllowPrefixTransformations:!0}),!["8","11","6","9"].includes(M)&&(0,a.createElement)(i.InnerBlocks,{allowedBlocks:["qsm/quiz-answer-option"],template:[["qsm/quiz-answer-option",{optionID:"0"}],["qsm/quiz-answer-option",{optionID:"1"}]]}),J&&(0,a.createElement)(i.RichText,{tagName:"p",title:(0,n.__)("Correct Answer Info","quiz-master-next"),"aria-label":(0,n.__)("Correct Answer Info","quiz-master-next"),placeholder:(0,n.__)("Correct answer info goes here","quiz-master-next"),value:p(F),onChange:e=>f({correctAnswerInfo:e}),className:"qsm-question-correct-answer-info",__unstableEmbedURLOnPaste:!0,__unstableAllowPrefixTransformations:!0}),(0,a.createElement)(c.Button,{icon:"plus-alt2",onClick:()=>oe(),variant:"secondary",className:"add-new-question-btn"},(0,n.__)("Add New Question","quiz-master-next"))))))},__experimentalLabel(e,{context:t}){const{title:a}=e,n=e?.metadata?.name;if("list-view"===t&&(n||a?.length>0))return n||a}})}();