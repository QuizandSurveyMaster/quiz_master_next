!function(){"use strict";var e,t={820:function(e,t,n){var a=window.wp.blocks,i=window.wp.element,s=window.wp.i18n,r=window.wp.apiFetch,o=n.n(r),l=window.wp.htmlEntities,u=window.wp.blockEditor,c=window.wp.notices,m=window.wp.data,p=window.wp.editor,d=window.wp.components;const q=e=>null==e||""===e,g=e=>{var t=document.createElement("textarea");return t.innerHTML=e,t.value},_=e=>{let t=document.createElement("div");return t.innerHTML=g(e),t.innerText},h=(e=!1)=>{let t=new FormData;if(t.append("qsm_block_api_call","1"),!1!==e)for(let n in e)e.hasOwnProperty(n)&&t.append(n,e[n]);return t},z=e=>{let t="";const n=new Uint8Array(e);window.crypto.getRandomValues(n);for(let a=0;a<e;a++)t+="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"[n[a]%62];return t},f=(e="",t=!1)=>`${e}${z(8)}${t?`.${z(7)}`:""}`,b=(e,t="")=>q(e)?t:e,v=()=>{};function w({className:e="",quizAttr:t,setAttributes:n,data:a,onChangeFunc:s=v}){var r,o,l,u,c;const m=(()=>{if(a.defaultvalue=a.default,!q(a?.options))switch(a.type){case"checkbox":1===a.options.length&&(a.type="toggle"),a.label=a.options[0].label;break;case"radio":1==a.options.length?(a.label=a.options[0].label,a.type="toggle"):a.type="select"}return a.label=q(a.label)?"":_(a.label),a.help=q(a.help)?"":_(a.help),a})(),{id:p,label:g="",type:h,help:z="",options:f=[],defaultvalue:b}=m;return(0,i.createElement)(i.Fragment,null,"toggle"===h&&(0,i.createElement)(d.ToggleControl,{label:g,help:z,checked:!q(t[p])&&"1"==t[p],onChange:()=>s(q(t[p])||"1"!=t[p]?1:0,p)}),"select"===h&&(0,i.createElement)(d.SelectControl,{label:g,value:null!==(r=t[p])&&void 0!==r?r:b,options:f,onChange:e=>s(e,p),help:z,__nextHasNoMarginBottom:!0}),"number"===h&&(0,i.createElement)(d.TextControl,{type:"number",label:g,value:null!==(o=t[p])&&void 0!==o?o:b,onChange:e=>s(e,p),help:z,__nextHasNoMarginBottom:!0}),"text"===h&&(0,i.createElement)(d.TextControl,{type:"text",label:g,value:null!==(l=t[p])&&void 0!==l?l:b,onChange:e=>s(e,p),help:z,__nextHasNoMarginBottom:!0}),"textarea"===h&&(0,i.createElement)(d.TextareaControl,{label:g,value:null!==(u=t[p])&&void 0!==u?u:b,onChange:e=>s(e,p),help:z,__nextHasNoMarginBottom:!0}),"checkbox"===h&&(0,i.createElement)(d.CheckboxControl,{label:g,help:z,checked:!q(t[p])&&"1"==t[p],onChange:()=>s(q(t[p])||"1"!=t[p]?1:0,p)}),"radio"===h&&(0,i.createElement)(d.RadioControl,{label:g,help:z,selected:null!==(c=t[p])&&void 0!==c?c:b,options:f,onChange:e=>s(e,p)}))}const y=()=>(0,i.createElement)(d.Icon,{icon:()=>(0,i.createElement)("svg",{width:"24",height:"24",viewBox:"0 0 24 24",fill:"none",xmlns:"http://www.w3.org/2000/svg"},(0,i.createElement)("rect",{width:"24",height:"24",rx:"3",fill:"black"}),(0,i.createElement)("path",{d:"M17.8146 17.8349C19.3188 16.3426 20.25 14.2793 20.25 12C20.2485 7.44425 16.5267 3.75 11.9348 3.75C7.34282 3.75 3.62109 7.44425 3.62109 12C3.62109 16.5558 7.34282 20.25 11.9348 20.25H18.9988C19.4682 20.25 19.7074 19.7112 19.3813 19.3885L17.8146 17.8334V17.8349ZM11.8753 17.5195C8.72666 17.5195 6.17388 15.0737 6.17388 12.0569C6.17388 9.04022 8.72666 6.59442 11.8753 6.59442C15.024 6.59442 17.5768 9.04022 17.5768 12.0569C17.5768 15.0737 15.024 17.5195 11.8753 17.5195Z",fill:"white"}))});(0,a.registerBlockType)("qsm/quiz",{icon:y,edit:function(e){if("undefined"==typeof qsmBlockData)return null;const{className:t,attributes:n,setAttributes:a,isSelected:r,clientId:_}=e,{createNotice:z}=(0,m.useDispatch)(c.store),v=qsmBlockData.globalQuizsetting,{quizID:E,postID:k,quizAttr:x=v}=n,[D,I]=(0,i.useState)(qsmBlockData.QSMQuizList),[C,B]=(0,i.useState)({error:!1,msg:""}),[S,N]=(0,i.useState)(!1),[O,A]=(0,i.useState)(!1),[P,T]=(0,i.useState)(!1),[M,Q]=(0,i.useState)([]),H=qsmBlockData.quizOptions,F=(0,m.useSelect)((e=>{const{isAutosavingPost:t,isSavingPost:n}=e(p.store);return n()&&!t()}),[]),{getBlock:j}=(0,m.useSelect)(u.store);(0,i.useEffect)((()=>{let e=!0;if(e&&("0"==qsmBlockData.is_pro_activated&&setTimeout((()=>{L()}),100),!q(E)&&0<E)){let e=!1;D.forEach((t=>{if(E==t.value)return e=!0,!0})),e?K(E):(a({quizID:void 0}),B({error:!0,msg:(0,s.__)("Quiz not found. Please select an existing quiz or create a new one.","quiz-master-next")}))}return()=>{e=!1}}),[]);const L=()=>{let e=document.getElementById("modal-advanced-question-type");q(e)&&o()({path:"/quiz-survey-master/v1/quiz/advance-ques-type-upgrade-popup",method:"POST"}).then((e=>{let t=document.getElementById("wpbody-content");q(t)||"success"!=e.status||t.insertAdjacentHTML("afterbegin",e.result)})).catch((e=>{console.log("error",e)}))},K=e=>{!q(e)&&0<e&&o()({path:"/quiz-survey-master/v1/quiz/structure",method:"POST",data:{quizID:e}}).then((t=>{if("success"==t.status){B({error:!1,msg:""});let n=t.result;if(a({quizID:parseInt(e),postID:n.post_id,quizAttr:{...x,...n}}),!q(n.qpages)){let e=[];n.qpages.forEach((t=>{let n=[];q(t.question_arr)||t.question_arr.forEach((e=>{if(!q(e)){let t=[];!q(e.answers)&&0<e.answers.length&&e.answers.forEach(((e,n)=>{t.push(["qsm/quiz-answer-option",{optionID:n,content:e[0],points:e[1],isCorrect:e[2],caption:b(e[3])}])})),n.push(["qsm/quiz-question",{questionID:e.question_id,type:e.question_type_new,answerEditor:e.settings.answerEditor,title:e.settings.question_title,description:e.question_name,required:e.settings.required,hint:e.hints,answers:e.answers,correctAnswerInfo:e.question_answer_info,category:e.category,multicategories:e.multicategories,commentBox:e.comments,matchAnswer:e.settings.matchAnswer,featureImageID:e.settings.featureImageID,featureImageSrc:e.settings.featureImageSrc,settings:e.settings},t])}})),e.push(["qsm/quiz-page",{pageID:t.id,pageKey:t.pagekey,hidePrevBtn:t.hide_prevbtn,quizID:t.quizID},n])})),Q(e)}}else console.log("error "+t.msg)})).catch((e=>{console.log("error",e)}))},R=(e,t)=>{let n=x;n[t]=e,a({quizAttr:{...n}})};(0,i.useEffect)((()=>{if(F){let e=(()=>{let e=j(_);if(q(e))return!1;e=e.innerBlocks;let t={quiz_id:x.quiz_id,post_id:x.post_id,quiz:{},pages:[],qpages:[],questions:[]},n=0;return e.forEach((e=>{if("qsm/quiz-page"===e.name){let a=e.attributes.pageID,i=[];!q(e.innerBlocks)&&0<e.innerBlocks.length&&e.innerBlocks.forEach((e=>{if("qsm/quiz-question"!==e.name)return!0;let a=e.attributes,s=b(a?.answerEditor,"text"),r=[];!q(e.innerBlocks)&&0<e.innerBlocks.length&&e.innerBlocks.forEach((e=>{if("qsm/quiz-answer-option"!==e.name)return!0;let t=e.attributes,n=b(t?.content);q(a?.answerEditor)||"rich"!==a.answerEditor||(n=g((0,l.decodeEntities)(n)));let i=[n,b(t?.points),b(t?.isCorrect)];"image"!==s||q(t?.caption)||i.push(t?.caption),r.push(i)})),i.push(a.questionID),a.isChanged&&t.questions.push({id:a.questionID,quizID:x.quiz_id,postID:x.post_id,answerEditor:s,type:b(a?.type,"0"),name:g(b(a?.description)),question_title:b(a?.title),answerInfo:g(b(a?.correctAnswerInfo)),comments:b(a?.commentBox,"1"),hint:b(a?.hint),category:b(a?.category),multicategories:b(a?.multicategories,[]),required:b(a?.required,0),answers:r,featureImageID:b(a?.featureImageID),featureImageSrc:b(a?.featureImageSrc),page:n,other_settings:{...b(a?.settings,{}),required:b(a?.required,0)}})})),t.pages.push(i),t.qpages.push({id:a,quizID:x.quiz_id,pagekey:q(e.attributes.pageKey)?f():e.attributes.pageKey,hide_prevbtn:e.attributes.hidePrevBtn,questions:i}),n++}})),t.quiz={quiz_name:x.quiz_name,quiz_id:x.quiz_id,post_id:x.post_id},P&&["form_type","system","timer_limit","pagination","enable_contact_form","enable_pagination_quiz","show_question_featured_image_in_result","progress_bar","require_log_in","disable_first_page","comment_section"].forEach((e=>{void 0!==x[e]&&null!==x[e]&&(t.quiz[e]=x[e])})),t})();A(!0),e=h({save_entire_quiz:"1",quizData:JSON.stringify(e),qsm_block_quiz_nonce:qsmBlockData.nonce,nonce:qsmBlockData.saveNonce}),o()({path:"/quiz-survey-master/v1/quiz/save_quiz",method:"POST",body:e}).then((e=>{z(e.status,e.msg,{isDismissible:!0,type:"snackbar"})})).catch((e=>{console.log("error",e),z("error",e.message,{isDismissible:!0,type:"snackbar"})}))}}),[F]);const V=(0,u.useBlockProps)(),$=(0,u.useInnerBlocksProps)(V,{template:M,allowedBlocks:["qsm/quiz-page"]});return(0,i.createElement)(i.Fragment,null,(0,i.createElement)(u.InspectorControls,null,(0,i.createElement)(d.PanelBody,{title:(0,s.__)("Quiz settings","quiz-master-next"),initialOpen:!0},(0,i.createElement)("label",{className:"qsm-inspector-label"},(0,s.__)("Status","quiz-master-next")+":",(0,i.createElement)("span",{className:"qsm-inspector-label-value"},x.post_status)),(0,i.createElement)(d.TextControl,{label:(0,s.__)("Quiz Name *","quiz-master-next"),help:(0,s.__)("Enter a name for this Quiz","quiz-master-next"),value:x?.quiz_name||"",onChange:e=>R(e,"quiz_name"),className:"qsm-no-mb"}),(!q(E)||"0"!=E)&&(0,i.createElement)("p",null,(0,i.createElement)(d.ExternalLink,{href:qsmBlockData.quiz_settings_url+"&quiz_id="+E},(0,s.__)("Advance Quiz Settings","quiz-master-next"))))),q(E)||"0"==E?(0,i.createElement)(d.Placeholder,{className:"qsm-placeholder-wrapper",icon:y,label:(0,s.__)("Quiz And Survey Master","quiz-master-next"),instructions:(0,s.__)("Easily and quickly add quizzes and surveys inside the block editor.","quiz-master-next")},(0,i.createElement)(i.Fragment,null,!q(D)&&0<D.length&&(0,i.createElement)("div",{className:"qsm-placeholder-select-create-quiz"},(0,i.createElement)(d.SelectControl,{label:(0,s.__)("","quiz-master-next"),value:E,options:D,onChange:e=>K(e),disabled:S,__nextHasNoMarginBottom:!0}),(0,i.createElement)("span",null,(0,s.__)("OR","quiz-master-next")),(0,i.createElement)(d.Button,{variant:"link",onClick:()=>N(!S)},(0,s.__)("Add New","quiz-master-next"))),(q(D)||S)&&(0,i.createElement)(d.__experimentalVStack,{spacing:"3",className:"qsm-placeholder-quiz-create-form"},(0,i.createElement)(d.TextControl,{label:(0,s.__)("Quiz Name *","quiz-master-next"),help:(0,s.__)("Enter a name for this Quiz","quiz-master-next"),value:x?.quiz_name||"",onChange:e=>R(e,"quiz_name")}),(0,i.createElement)(d.Button,{variant:"link",onClick:()=>T(!P)},(0,s.__)("Advance options","quiz-master-next")),(0,i.createElement)("div",{className:"qsm-advance-settings"},P&&H.map((e=>(0,i.createElement)(w,{key:"qsm-settings"+e.id,data:e,quizAttr:x,setAttributes:a,onChangeFunc:R})))),(0,i.createElement)(d.Button,{variant:"primary",disabled:O||q(x.quiz_name),onClick:()=>(()=>{if(q(x.quiz_name))return void console.log("empty quiz_name");A(!0);let e=h({quiz_name:x.quiz_name,qsm_new_quiz_nonce:qsmBlockData.qsm_new_quiz_nonce});["form_type","system","timer_limit","pagination","enable_contact_form","enable_pagination_quiz","show_question_featured_image_in_result","progress_bar","require_log_in","disable_first_page","comment_section"].forEach((t=>void 0===x[t]||null===x[t]?"":e.append(t,x[t]))),o()({path:"/quiz-survey-master/v1/quiz/create_quiz",method:"POST",body:e}).then((e=>{if(A(!1),"success"==e.status){let t=h({id:null,quizID:e.quizID,answerEditor:"text",type:"0",name:"",question_title:"",answerInfo:"",comments:"1",hint:"",category:"",required:0,answers:[],page:0});o()({path:"/quiz-survey-master/v1/questions",method:"POST",body:t}).then((t=>{if("success"==t.status){let n=t.id,a=h({action:qsmBlockData.save_pages_action,quiz_id:e.quizID,nonce:qsmBlockData.saveNonce,post_id:e.quizPostID});a.append("pages[0][]",n),a.append("qpages[0][id]",1),a.append("qpages[0][quizID]",e.quizID),a.append("qpages[0][pagekey]",f()),a.append("qpages[0][hide_prevbtn]",0),a.append("qpages[0][questions][]",n),o()({url:qsmBlockData.ajax_url,method:"POST",body:a}).then((t=>{"success"==t.status&&K(e.quizID)}))}})).catch((e=>{console.log("error",e),z("error",e.message,{isDismissible:!0,type:"snackbar"})}))}z(e.status,e.msg,{isDismissible:!0,type:"snackbar"})})).catch((e=>{console.log("error",e),z("error",e.message,{isDismissible:!0,type:"snackbar"})}))})()},(0,s.__)("Create Quiz","quiz-master-next"))),C.error&&(0,i.createElement)("p",{className:"qsm-error-text"},C.msg))):(0,i.createElement)("div",{...$}))},save:e=>null})}},n={};function a(e){var i=n[e];if(void 0!==i)return i.exports;var s=n[e]={exports:{}};return t[e](s,s.exports,a),s.exports}a.m=t,e=[],a.O=function(t,n,i,s){if(!n){var r=1/0;for(c=0;c<e.length;c++){n=e[c][0],i=e[c][1],s=e[c][2];for(var o=!0,l=0;l<n.length;l++)(!1&s||r>=s)&&Object.keys(a.O).every((function(e){return a.O[e](n[l])}))?n.splice(l--,1):(o=!1,s<r&&(r=s));if(o){e.splice(c--,1);var u=i();void 0!==u&&(t=u)}}return t}s=s||0;for(var c=e.length;c>0&&e[c-1][2]>s;c--)e[c]=e[c-1];e[c]=[n,i,s]},a.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return a.d(t,{a:t}),t},a.d=function(e,t){for(var n in t)a.o(t,n)&&!a.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:t[n]})},a.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},function(){var e={826:0,431:0};a.O.j=function(t){return 0===e[t]};var t=function(t,n){var i,s,r=n[0],o=n[1],l=n[2],u=0;if(r.some((function(t){return 0!==e[t]}))){for(i in o)a.o(o,i)&&(a.m[i]=o[i]);if(l)var c=l(a)}for(t&&t(n);u<r.length;u++)s=r[u],a.o(e,s)&&e[s]&&e[s][0](),e[s]=0;return a.O(c)},n=self.webpackChunkqsm=self.webpackChunkqsm||[];n.forEach(t.bind(null,0)),n.push=t.bind(null,n.push.bind(n))}();var i=a.O(void 0,[431],(function(){return a(820)}));i=a.O(i)}();