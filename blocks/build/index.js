!function(){"use strict";var e,t={101:function(e,t,n){var i=window.wp.blocks,a=window.wp.element,s=window.wp.i18n,r=window.wp.apiFetch,o=n.n(r),u=window.wp.htmlEntities,l=window.wp.blockEditor,c=window.wp.notices,m=window.wp.data,p=window.wp.editor,q=window.wp.components;const _=e=>null==e||""===e,d=e=>{var t=document.createElement("textarea");return t.innerHTML=e,t.value},g=(e=!1)=>{let t=new FormData;if(t.append("qsm_block_api_call","1"),!1!==e)for(let n in e)e.hasOwnProperty(n)&&t.append(n,e[n]);return t},h=(e,t="")=>_(e)?t:e;(0,i.registerBlockType)("qsm/quiz",{edit:function(e){if("undefined"==typeof qsmBlockData)return null;const{className:t,attributes:n,setAttributes:i,isSelected:r,clientId:z}=e,{createNotice:f}=(0,m.useDispatch)(c.store),w=qsmBlockData.globalQuizsetting,{quizID:b,quizAttr:v=w}=n,[y,k]=(0,a.useState)(qsmBlockData.QSMQuizList),[E,D]=(0,a.useState)({error:!1,msg:""}),[I,B]=(0,a.useState)(!1),[x,S]=(0,a.useState)(!1),[C,O]=(0,a.useState)(!1),[P,N]=(0,a.useState)([]),A=qsmBlockData.quizOptions,T=(0,m.useSelect)((e=>{const{isAutosavingPost:t,isSavingPost:n}=e(p.store);return n()&&!t()}),[]),{getBlock:M}=(0,m.useSelect)(l.store);(0,a.useEffect)((()=>{let e=!0;return e&&!_(b)&&0<b&&Q(b),()=>{e=!1}}),[]);const Q=e=>{!_(e)&&0<e&&o()({path:"/quiz-survey-master/v1/quiz/structure",method:"POST",data:{quizID:e}}).then((t=>{if(console.log("quiz render data",t),"success"==t.status){let n=t.result;if(i({quizID:parseInt(e),quizAttr:{...v,...n}}),!_(n.qpages)){let e=[];n.qpages.forEach((t=>{let n=[];_(t.question_arr)||t.question_arr.forEach((e=>{if(!_(e)){let t=[];!_(e.answers)&&0<e.answers.length&&e.answers.forEach(((e,n)=>{t.push(["qsm/quiz-answer-option",{optionID:n,content:e[0],points:e[1],isCorrect:e[2]}])})),n.push(["qsm/quiz-question",{questionID:e.question_id,type:e.question_type_new,answerEditor:e.settings.answerEditor,title:e.settings.question_title,description:e.question_name,required:e.settings.required,hint:e.hints,answers:e.answers,correctAnswerInfo:e.question_answer_info,category:e.category,multicategories:e.multicategories,commentBox:e.comments,matchAnswer:e.settings.matchAnswer,featureImageID:e.settings.featureImageID,featureImageSrc:e.settings.featureImageSrc,settings:e.settings},t])}})),e.push(["qsm/quiz-page",{pageID:t.id,pageKey:t.pagekey,hidePrevBtn:t.hide_prevbtn,quizID:t.quizID},n])})),N(e)}}else console.log("error "+t.msg)}))},j=(e,t)=>{let n=v;n[t]=e,i({quizAttr:{...n}})};(0,a.useEffect)((()=>{if(T){let e=(()=>{let e=M(z);if(_(e))return!1;e=e.innerBlocks;let t={quiz_id:v.quiz_id,post_id:v.post_id,quiz:{},pages:[],qpages:[],questions:[]},n=0;return e.forEach((e=>{if("qsm/quiz-page"===e.name){let i=e.attributes.pageID,a=[];!_(e.innerBlocks)&&0<e.innerBlocks.length&&e.innerBlocks.forEach((e=>{if("qsm/quiz-question"!==e.name)return!0;let i=e.attributes,s=h(i?.answerEditor,"text"),r=[];!_(e.innerBlocks)&&0<e.innerBlocks.length&&e.innerBlocks.forEach((e=>{if("qsm/quiz-answer-option"!==e.name)return!0;let t=e.attributes,n=h(t?.content);_(i?.answerEditor)||"rich"!==i.answerEditor||(n=d((0,u.decodeEntities)(n)));let a=[n,h(t?.points),h(t?.isCorrect)];"image"!==s||_(t?.caption)||a.push(t?.caption),r.push(a)})),a.push(i.questionID),i.isChanged&&t.questions.push({id:i.questionID,quizID:v.quiz_id,postID:v.post_id,answerEditor:s,type:h(i?.type,"0"),name:d(h(i?.description)),question_title:h(i?.title),answerInfo:d(h(i?.correctAnswerInfo)),comments:h(i?.commentBox,"1"),hint:h(i?.hint),category:h(i?.category),multicategories:h(i?.multicategories,[]),required:h(i?.required,1),answers:r,featureImageID:h(i?.featureImageID),featureImageSrc:h(i?.featureImageSrc),page:n})})),t.pages.push(a),t.qpages.push({id:i,quizID:v.quiz_id,pagekey:e.attributes.pageKey,hide_prevbtn:e.attributes.hidePrevBtn,questions:a}),n++}})),t.quiz={quiz_name:v.quiz_name,quiz_id:v.quiz_id,post_id:v.post_id},C&&["form_type","system","timer_limit","pagination","enable_contact_form","enable_pagination_quiz","show_question_featured_image_in_result","progress_bar","require_log_in","disable_first_page","comment_section"].forEach((e=>{void 0!==v[e]&&null!==v[e]&&(t.quiz[e]=v[e])})),t})();console.log("quizData",e),S(!0),e=g({save_entire_quiz:"1",quizData:JSON.stringify(e),qsm_block_quiz_nonce:qsmBlockData.nonce,nonce:qsmBlockData.saveNonce}),o()({path:"/quiz-survey-master/v1/quiz/save_quiz",method:"POST",body:e}).then((e=>{f(e.status,e.msg,{isDismissible:!0,type:"snackbar"})})).catch((e=>{console.log("error",e),f("error",e.message,{isDismissible:!0,type:"snackbar"})}))}}),[T]);const F=(0,l.useBlockProps)(),H=(0,l.useInnerBlocksProps)(F,{template:P,allowedBlocks:["qsm/quiz-page"]});return(0,a.createElement)(a.Fragment,null,(0,a.createElement)(l.InspectorControls,null,(0,a.createElement)(q.PanelBody,{title:(0,s.__)("Quiz settings","quiz-master-next"),initialOpen:!0},(0,a.createElement)(q.TextControl,{label:(0,s.__)("Quiz Name *","quiz-master-next"),help:(0,s.__)("Enter a name for this Quiz","quiz-master-next"),value:v?.quiz_name||"",onChange:e=>j(e,"quiz_name")}))),_(b)||"0"==b?(0,a.createElement)(q.Placeholder,{icon:()=>(0,a.createElement)(q.Icon,{icon:"vault",size:"36"}),label:(0,s.__)("Quiz And Survey Master","quiz-master-next"),instructions:(0,s.__)("Easily and quickly add quizzes and surveys inside the block editor.","quiz-master-next")},(0,a.createElement)(a.Fragment,null,!_(y)&&0<y.length&&(0,a.createElement)("div",{className:"qsm-placeholder-select-create-quiz"},(0,a.createElement)(q.SelectControl,{label:(0,s.__)("","quiz-master-next"),value:b,options:y,onChange:e=>Q(e),disabled:I,__nextHasNoMarginBottom:!0}),(0,a.createElement)("span",null,(0,s.__)("OR","quiz-master-next")),(0,a.createElement)(q.Button,{variant:"link",onClick:()=>B(!I)},(0,s.__)("Add New","quiz-master-next"))),(_(y)||I)&&(0,a.createElement)(q.__experimentalVStack,{spacing:"3",className:"qsm-placeholder-quiz-create-form"},(0,a.createElement)(q.TextControl,{label:(0,s.__)("Quiz Name *","quiz-master-next"),help:(0,s.__)("Enter a name for this Quiz","quiz-master-next"),value:v?.quiz_name||"",onChange:e=>j(e,"quiz_name")}),(0,a.createElement)(q.Button,{variant:"link",onClick:()=>O(!C)},(0,s.__)("Advance options","quiz-master-next")),C&&(0,a.createElement)(a.Fragment,null,(0,a.createElement)(q.SelectControl,{label:A?.form_type?.label,value:v?.form_type,options:A?.form_type?.options,onChange:e=>j(e,"form_type"),__nextHasNoMarginBottom:!0}),(0,a.createElement)(q.SelectControl,{label:A?.system?.label,value:v?.system,options:A?.system?.options,onChange:e=>j(e,"system"),help:A?.system?.help,__nextHasNoMarginBottom:!0}),["timer_limit","pagination"].map((e=>(0,a.createElement)(q.TextControl,{key:"quiz-create-text-"+e,type:"number",label:A?.[e]?.label,help:A?.[e]?.help,value:_(v[e])?0:v[e],onChange:t=>j(t,e)}))),["enable_contact_form","enable_pagination_quiz","show_question_featured_image_in_result","progress_bar","require_log_in","disable_first_page","comment_section"].map((e=>(0,a.createElement)(q.ToggleControl,{key:"quiz-create-toggle-"+e,label:A?.[e]?.label,help:A?.[e]?.help,checked:!_(v[e])&&"1"==v[e],onChange:()=>j(_(v[e])||"1"!=v[e]?1:0,e)})))),(0,a.createElement)(q.Button,{variant:"primary",disabled:x||_(v.quiz_name),onClick:()=>(()=>{if(_(v.quiz_name))return void console.log("empty quiz_name");S(!0);let e=g({quiz_name:v.quiz_name,qsm_new_quiz_nonce:qsmBlockData.qsm_new_quiz_nonce});["form_type","system","timer_limit","pagination","enable_contact_form","enable_pagination_quiz","show_question_featured_image_in_result","progress_bar","require_log_in","disable_first_page","comment_section"].forEach((t=>void 0===v[t]||null===v[t]?"":e.append(t,v[t]))),o()({path:"/quiz-survey-master/v1/quiz/create_quiz",method:"POST",body:e}).then((e=>{if(console.log(e),S(!1),"success"==e.status){let t=g({id:null,quizID:e.quizID,answerEditor:"text",type:"0",name:"",question_title:"",answerInfo:"",comments:"1",hint:"",category:"",required:1,answers:[],page:0});o()({path:"/quiz-survey-master/v1/questions",method:"POST",body:t}).then((t=>{if(console.log("question response",t),"success"==t.status){let n=t.id,i=g({action:qsmBlockData.save_pages_action,quiz_id:e.quizID,nonce:qsmBlockData.saveNonce,post_id:e.quizPostID});i.append("pages[0][]",n),i.append("qpages[0][id]",1),i.append("qpages[0][quizID]",e.quizID),i.append("qpages[0][pagekey]",((e="",t=!1)=>`${e}${(1e3*Date.now()+1e3*Math.random()).toString(16).replace(/\./g,"").padEnd(8,"0")}${t?`.${Math.trunc(1e8*Math.random())}`:""}`)()),i.append("qpages[0][hide_prevbtn]",0),i.append("qpages[0][questions][]",n),o()({url:qsmBlockData.ajax_url,method:"POST",body:i}).then((t=>{console.log("pageResponse",t),"success"==t.status&&Q(e.quizID)}))}})).catch((e=>{console.log("error",e),f("error",e.message,{isDismissible:!0,type:"snackbar"})}))}f(e.status,e.msg,{isDismissible:!0,type:"snackbar"})})).catch((e=>{console.log("error",e),f("error",e.message,{isDismissible:!0,type:"snackbar"})}))})()},(0,s.__)("Create Quiz","quiz-master-next"))))):(0,a.createElement)("div",{...H}))},save:e=>null})}},n={};function i(e){var a=n[e];if(void 0!==a)return a.exports;var s=n[e]={exports:{}};return t[e](s,s.exports,i),s.exports}i.m=t,e=[],i.O=function(t,n,a,s){if(!n){var r=1/0;for(c=0;c<e.length;c++){n=e[c][0],a=e[c][1],s=e[c][2];for(var o=!0,u=0;u<n.length;u++)(!1&s||r>=s)&&Object.keys(i.O).every((function(e){return i.O[e](n[u])}))?n.splice(u--,1):(o=!1,s<r&&(r=s));if(o){e.splice(c--,1);var l=a();void 0!==l&&(t=l)}}return t}s=s||0;for(var c=e.length;c>0&&e[c-1][2]>s;c--)e[c]=e[c-1];e[c]=[n,a,s]},i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,{a:t}),t},i.d=function(e,t){for(var n in t)i.o(t,n)&&!i.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:t[n]})},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},function(){var e={826:0,431:0};i.O.j=function(t){return 0===e[t]};var t=function(t,n){var a,s,r=n[0],o=n[1],u=n[2],l=0;if(r.some((function(t){return 0!==e[t]}))){for(a in o)i.o(o,a)&&(i.m[a]=o[a]);if(u)var c=u(i)}for(t&&t(n);l<r.length;l++)s=r[l],i.o(e,s)&&e[s]&&e[s][0](),e[s]=0;return i.O(c)},n=self.webpackChunkqsm=self.webpackChunkqsm||[];n.forEach(t.bind(null,0)),n.push=t.bind(null,n.push.bind(n))}();var a=i.O(void 0,[431],(function(){return i(101)}));a=i.O(a)}();