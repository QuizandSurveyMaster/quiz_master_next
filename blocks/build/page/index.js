!function(){"use strict";var e=window.wp.blocks,t=window.wp.element,n=window.wp.i18n,l=(window.wp.apiFetch,window.wp.blockEditor),a=window.wp.components;const i=e=>null==e||""===e;var o=JSON.parse('{"u2":"qsm/quiz-page"}');(0,e.registerBlockType)(o.u2,{edit:function(e){if("undefined"==typeof qsmBlockData)return null;const{className:o,attributes:r,setAttributes:s,isSelected:c,clientId:u,context:m}=e,{pageID:d,pageKey:w,hidePrevBtn:p}=(m["quiz-master-next/quizID"],r),[g,q]=(0,t.useState)(qsmBlockData.globalQuizsetting),B=(0,l.useBlockProps)();return(0,t.createElement)(t.Fragment,null,(0,t.createElement)(l.InspectorControls,null,(0,t.createElement)(a.PanelBody,{title:(0,n.__)("Page settings","quiz-master-next"),initialOpen:!0},(0,t.createElement)(a.TextControl,{label:(0,n.__)("Page Name","quiz-master-next"),value:w,onChange:e=>s({pageKey:e})}),(0,t.createElement)(a.ToggleControl,{label:(0,n.__)("Hide Previous Button?","quiz-master-next"),checked:!i(p)&&"1"==p,onChange:()=>s({hidePrevBtn:i(p)||"1"!=p?1:0})}))),(0,t.createElement)("div",{...B},(0,t.createElement)(l.InnerBlocks,{allowedBlocks:["qsm/quiz-question"]})))}})}();