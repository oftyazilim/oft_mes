import{u as ee,a as le,m as ae,g as Y,V as te,b as q}from"./VSliderTrack-DJ52Nvdp.js";import{V as G,m as se}from"./VInput-BqpuYsFQ.js";import{u as ue,m as oe,V as ne}from"./form-B1zQy7mP.js";import{a1 as re,B as ie,r as g,al as de,ak as ce,D as H,I as me,b as t,ai as pe,F as ve,d as h,g as R,l as f,aE as x,o as k,f as c,y as fe,e as d,aF as v}from"./main-DutK4zgt.js";import{_ as Ve}from"./AppCardCode.vue_vue_type_style_index_0_lang-DXXyypI_.js";import{V as be}from"./VRow-MyJ-KYMf.js";import{V as $}from"./VCol-008_lOYC.js";import"./index-C0EN1ist.js";import"./VCard-BEwag10s.js";import"./createSimpleFunctional-CGq-ys4F.js";import"./VAvatar-CzcOFsPX.js";import"./VImg-BLt3cdJ-.js";import"./VCardText-CYfYjjc9.js";import"./VDivider-Bd4h0ODD.js";/* empty css              */const _e=ie({...oe(),...se(),...ae(),strict:Boolean,modelValue:{type:Array,default:()=>[0,0]}},"VRangeSlider"),y=re()({name:"VRangeSlider",props:_e(),emits:{"update:focused":e=>!0,"update:modelValue":e=>!0,end:e=>!0,start:e=>!0},setup(e,u){let{slots:l,emit:o}=u;const a=g(),i=g(),S=g(),{rtlClasses:j}=de();function z(m){if(!a.value||!i.value)return;const p=Y(m,a.value.$el,e.direction),r=Y(m,i.value.$el,e.direction),n=Math.abs(p),V=Math.abs(r);return n<V||n===V&&p<0?a.value.$el:i.value.$el}const U=ee(e),s=ce(e,"modelValue",void 0,m=>m!=null&&m.length?m.map(p=>U.roundValue(p)):[0,0]),{activeThumbRef:b,hasLabels:J,max:I,min:w,mousePressed:K,onSliderMousedown:Q,onSliderTouchstart:X,position:L,trackContainerRef:Z,readonly:A}=le({props:e,steps:U,onSliderStart:()=>{o("start",s.value)},onSliderEnd:m=>{var n;let{value:p}=m;const r=b.value===((n=a.value)==null?void 0:n.$el)?[p,s.value[1]]:[s.value[0],p];!e.strict&&r[0]<r[1]&&(s.value=r),o("end",s.value)},onSliderMove:m=>{var V,D,T,_;let{value:p}=m;const[r,n]=s.value;!e.strict&&r===n&&r!==w.value&&(b.value=p>r?(V=i.value)==null?void 0:V.$el:(D=a.value)==null?void 0:D.$el,(T=b.value)==null||T.focus()),b.value===((_=a.value)==null?void 0:_.$el)?s.value=[Math.min(p,n),n]:s.value=[r,Math.max(r,p)]},getActiveThumb:z}),{isFocused:M,focus:N,blur:O}=ue(e),W=H(()=>L(s.value[0])),E=H(()=>L(s.value[1]));return me(()=>{const m=G.filterProps(e),p=!!(e.label||l.label||l.prepend);return t(G,pe({class:["v-slider","v-range-slider",{"v-slider--has-labels":!!l["tick-label"]||J.value,"v-slider--focused":M.value,"v-slider--pressed":K.value,"v-slider--disabled":e.disabled},j.value,e.class],style:e.style,ref:S},m,{focused:M.value}),{...l,prepend:p?r=>{var n,V;return t(ve,null,[((n=l.label)==null?void 0:n.call(l,r))??(e.label?t(ne,{class:"v-slider__label",text:e.label},null):void 0),(V=l.prepend)==null?void 0:V.call(l,r)])}:void 0,default:r=>{var D,T;let{id:n,messagesId:V}=r;return t("div",{class:"v-slider__container",onMousedown:A.value?void 0:Q,onTouchstartPassive:A.value?void 0:X},[t("input",{id:`${n.value}_start`,name:e.name||n.value,disabled:!!e.disabled,readonly:!!e.readonly,tabindex:"-1",value:s.value[0]},null),t("input",{id:`${n.value}_stop`,name:e.name||n.value,disabled:!!e.disabled,readonly:!!e.readonly,tabindex:"-1",value:s.value[1]},null),t(te,{ref:Z,start:W.value,stop:E.value},{"tick-label":l["tick-label"]}),t(q,{ref:a,"aria-describedby":V.value,focused:M&&b.value===((D=a.value)==null?void 0:D.$el),modelValue:s.value[0],"onUpdate:modelValue":_=>s.value=[_,s.value[1]],onFocus:_=>{var F,C,B,P;N(),b.value=(F=a.value)==null?void 0:F.$el,I.value!==w.value&&s.value[0]===s.value[1]&&s.value[1]===w.value&&_.relatedTarget!==((C=i.value)==null?void 0:C.$el)&&((B=a.value)==null||B.$el.blur(),(P=i.value)==null||P.$el.focus())},onBlur:()=>{O(),b.value=void 0},min:w.value,max:s.value[1],position:W.value,ripple:e.ripple},{"thumb-label":l["thumb-label"]}),t(q,{ref:i,"aria-describedby":V.value,focused:M&&b.value===((T=i.value)==null?void 0:T.$el),modelValue:s.value[1],"onUpdate:modelValue":_=>s.value=[s.value[0],_],onFocus:_=>{var F,C,B,P;N(),b.value=(F=i.value)==null?void 0:F.$el,I.value!==w.value&&s.value[0]===s.value[1]&&s.value[0]===I.value&&_.relatedTarget!==((C=a.value)==null?void 0:C.$el)&&((B=i.value)==null||B.$el.blur(),(P=a.value)==null||P.$el.focus())},onBlur:()=>{O(),b.value=void 0},min:s.value[0],max:I.value,position:E.value,ripple:e.ripple},{"thumb-label":l["thumb-label"]})])}})}),{}}}),ge=h({__name:"DemoRangeSliderVertical",setup(e){const u=g([20,40]);return(l,o)=>(k(),R(y,{modelValue:f(u),"onUpdate:modelValue":o[0]||(o[0]=a=>x(u)?u.value=a:null),direction:"vertical"},null,8,["modelValue"]))}}),Se=h({__name:"DemoRangeSliderThumbLabel",setup(e){const u=["Winter","Spring","Summer","Fall"],l=["tabler-snowflake","tabler-leaf","tabler-flame","tabler-droplet"],o=g([1,2]);return(a,i)=>(k(),R(y,{modelValue:f(o),"onUpdate:modelValue":i[0]||(i[0]=S=>x(o)?o.value=S:null),tick:u,min:"0",max:"3",step:1,"show-ticks":"always","thumb-label":"","tick-size":"4"},{"thumb-label":c(({modelValue:S})=>[t(fe,{icon:l[S]},null,8,["icon"])]),_:1},8,["modelValue"]))}}),he=h({__name:"DemoRangeSliderStep",setup(e){const u=g([20,40]);return(l,o)=>(k(),R(y,{modelValue:f(u),"onUpdate:modelValue":o[0]||(o[0]=a=>x(u)?u.value=a:null),step:"10"},null,8,["modelValue"]))}}),Re=h({__name:"DemoRangeSliderColor",setup(e){const u=g([10,60]);return(l,o)=>(k(),R(y,{modelValue:f(u),"onUpdate:modelValue":o[0]||(o[0]=a=>x(u)?u.value=a:null),color:"success"},null,8,["modelValue"]))}}),ke=h({__name:"DemoRangeSliderDisabled",setup(e){const u=g([30,60]);return(l,o)=>(k(),R(y,{modelValue:f(u),"onUpdate:modelValue":o[0]||(o[0]=a=>x(u)?u.value=a:null),disabled:"",label:"Disabled"},null,8,["modelValue"]))}}),$e=h({__name:"DemoRangeSliderBasic",setup(e){const u=g([10,60]);return(l,o)=>(k(),R(y,{modelValue:f(u),"onUpdate:modelValue":o[0]||(o[0]=a=>x(u)?u.value=a:null)},null,8,["modelValue"]))}}),xe={ts:`<script setup lang="ts">
const sliderValues = ref([10, 60])
<\/script>

<template>
  <VRangeSlider v-model="sliderValues" />
</template>
`,js:`<script setup>
const sliderValues = ref([
  10,
  60,
])
<\/script>

<template>
  <VRangeSlider v-model="sliderValues" />
</template>
`},ye={ts:`<script lang="ts" setup>
const sliderValues = ref([10, 60])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    color="success"
  />
</template>
`,js:`<script setup>
const sliderValues = ref([
  10,
  60,
])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    color="success"
  />
</template>
`},we={ts:`<script lang="ts" setup>
const slidersValues = ref([30, 60])
<\/script>

<template>
  <VRangeSlider
    v-model="slidersValues"
    disabled
    label="Disabled"
  />
</template>
`,js:`<script setup>
const slidersValues = ref([
  30,
  60,
])
<\/script>

<template>
  <VRangeSlider
    v-model="slidersValues"
    disabled
    label="Disabled"
  />
</template>
`},De={ts:`<script lang="ts" setup>
const sliderValues = ref([20, 40])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    step="10"
  />
</template>
`,js:`<script setup>
const sliderValues = ref([
  20,
  40,
])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    step="10"
  />
</template>
`},Te={ts:`<script lang="ts" setup>
const seasons = ['Winter', 'Spring', 'Summer', 'Fall']
const icons = ['tabler-snowflake', 'tabler-leaf', 'tabler-flame', 'tabler-droplet']
const sliderValues = ref([1, 2])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    :tick="seasons"
    min="0"
    max="3"
    :step="1"
    show-ticks="always"
    thumb-label
    tick-size="4"
  >
    <template #thumb-label="{ modelValue }">
      <VIcon :icon="icons[modelValue]" />
    </template>
  </VRangeSlider>
</template>
`,js:`<script setup>
const seasons = [
  'Winter',
  'Spring',
  'Summer',
  'Fall',
]

const icons = [
  'tabler-snowflake',
  'tabler-leaf',
  'tabler-flame',
  'tabler-droplet',
]

const sliderValues = ref([
  1,
  2,
])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    :tick="seasons"
    min="0"
    max="3"
    :step="1"
    show-ticks="always"
    thumb-label
    tick-size="4"
  >
    <template #thumb-label="{ modelValue }">
      <VIcon :icon="icons[modelValue]" />
    </template>
  </VRangeSlider>
</template>
`},Fe={ts:`<script lang="ts" setup>
const sliderValues = ref([20, 40])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    direction="vertical"
  />
</template>
`,js:`<script setup>
const sliderValues = ref([
  20,
  40,
])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    direction="vertical"
  />
</template>
`},qe=h({__name:"range-slider",setup(e){return(u,l)=>{const o=$e,a=Ve,i=ke,S=Re,j=he,z=Se,U=ge;return k(),R(be,null,{default:c(()=>[t($,{cols:"12",md:"6"},{default:c(()=>[t(a,{title:"Basic",code:f(xe)},{default:c(()=>[l[0]||(l[0]=d("p",null,[v("The "),d("code",null,"v-slider"),v(" component is a better visualization of the number input.")],-1)),t(o)]),_:1,__:[0]},8,["code"])]),_:1}),t($,{cols:"12",md:"6"},{default:c(()=>[t(a,{title:"Disabled",code:f(we)},{default:c(()=>[l[1]||(l[1]=d("p",null,[v("You cannot interact with "),d("code",null,"disabled"),v(" sliders.")],-1)),t(i)]),_:1,__:[1]},8,["code"])]),_:1}),t($,{cols:"12",md:"6"},{default:c(()=>[t(a,{title:"Color",code:f(ye)},{default:c(()=>[l[2]||(l[2]=d("p",null,[v("Use "),d("code",null,"color"),v(" prop to the sets the slider color. "),d("code",null,"track-color"),v(" prop to sets the color of slider's unfilled track.")],-1)),t(S)]),_:1,__:[2]},8,["code"])]),_:1}),t($,{cols:"12",md:"6"},{default:c(()=>[t(a,{title:"Step",code:f(De)},{default:c(()=>[l[3]||(l[3]=d("p",null,[d("code",null,"v-range-slider"),v(" can have steps other than 1. This can be helpful for some applications where you need to adjust values with more or less accuracy.")],-1)),t(j)]),_:1,__:[3]},8,["code"])]),_:1}),t($,{cols:"12",md:"6"},{default:c(()=>[t(a,{title:"Thumb label",code:f(Te)},{default:c(()=>[l[4]||(l[4]=d("p",null,[v(" Using the "),d("code",null,"tick-labels"),v(" prop along with the "),d("code",null,"thumb-label"),v(" slot, you can create a very customized solution. ")],-1)),t(z)]),_:1,__:[4]},8,["code"])]),_:1}),t($,{cols:"12",md:"6"},{default:c(()=>[t(a,{title:"Vertical",code:f(Fe)},{default:c(()=>[l[5]||(l[5]=d("p",null,[v("You can use the "),d("code",null,"vertical"),v(" prop to switch sliders to a vertical orientation. If you need to change the height of the slider, use css.")],-1)),t(U)]),_:1,__:[5]},8,["code"])]),_:1})]),_:1})}}});export{qe as default};
