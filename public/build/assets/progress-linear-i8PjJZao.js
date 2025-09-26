import{_}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{c as g,o as m,b as e,b8 as s,d as y,r as f,aU as V,m as n,f as t,e as o,v as P,g as L,G as k,bh as $,w as M,t as l}from"./main-C24QGA__.js";import{_ as x}from"./AppCardCode.vue_vue_type_style_index_0_lang-C0ErupE8.js";import{V as I}from"./VRow-CnlSE1X1.js";import{V as v}from"./VCol-CHpJWJYA.js";import"./vue3-perfect-scrollbar-BkJYoTfA.js";import"./VCard-C8sLDXkB.js";import"./VAvatar-WkJj3DOW.js";import"./VImg-5W6f0-Xa.js";import"./VCardText-DYhoWOiZ.js";import"./VDivider-CZR96FOy.js";/* empty css              */const D={},U={class:"demo-space-y"};function T(p,a){return m(),g("div",U,[e(s,{color:"rgb(var(--v-theme-primary))","model-value":"75",striped:""}),e(s,{color:"rgb(var(--v-theme-success))","model-value":"55",striped:""}),e(s,{color:"rgb(var(--v-theme-warning))","model-value":"35",striped:""})])}const j=_(D,[["render",T]]),C={class:"demo-space-y"},R=y({__name:"DemoProgressLinearSlots",setup(p){const a=f(20),r=f(33),u=f(78);return(i,c)=>(m(),g("div",C,[e(s,{modelValue:n(u),"onUpdate:modelValue":c[0]||(c[0]=d=>V(u)?u.value=d:null),color:"primary",height:"8"},null,8,["modelValue"]),e(s,{modelValue:n(a),"onUpdate:modelValue":c[1]||(c[1]=d=>V(a)?a.value=d:null),color:"primary",height:"20"},{default:t(({value:d})=>[o("span",null,P(Math.ceil(d))+"%",1)]),_:1},8,["modelValue"]),e(s,{modelValue:n(r),"onUpdate:modelValue":c[2]||(c[2]=d=>V(r)?r.value=d:null),height:"20",color:"primary"},{default:t(()=>[o("span",null,P(Math.ceil(n(r)))+"%",1)]),_:1},8,["modelValue"])]))}}),S={},N={class:"demo-space-y"};function Y(p,a){return m(),g("div",N,[e(s,{"model-value":"78",height:"8",color:"primary",rounded:!1}),e(s,{"model-value":"20",color:"primary",height:"20",rounded:!1}),e(s,{"model-value":"33",height:"20",color:"primary",rounded:!1})])}const A=_(S,[["render",Y]]),E={};function G(p,a){return m(),L(s,{color:"primary",indeterminate:"",reverse:""})}const q=_(E,[["render",G]]),z={};function F(p,a){return m(),L(s,{indeterminate:"",color:"primary"})}const H=_(z,[["render",F]]),J={class:"demo-space-y"},K=y({__name:"DemoProgressLinearBuffering",setup(p){const a=f(10),r=f(20),u=f(),i=()=>{clearInterval(u.value),u.value=setInterval(()=>{a.value+=Math.random()*10+5,r.value+=Math.random()*10+6},2e3)};return k(i),$(()=>{clearInterval(u.value)}),M(a,()=>{if(a.value<100)return!1;a.value=0,r.value=10,i()}),(c,d)=>(m(),g("div",J,[e(s,{modelValue:n(a),"onUpdate:modelValue":d[0]||(d[0]=h=>V(a)?a.value=h:null),color:"primary","buffer-value":n(r)},null,8,["modelValue","buffer-value"])]))}}),O={},Q={class:"demo-space-y"};function W(p,a){return m(),g("div",Q,[e(s,{"model-value":"15",color:"primary"}),e(s,{"model-value":"30",color:"secondary"}),e(s,{"model-value":"45",color:"success"})])}const X=_(O,[["render",W]]),Z={ts:`<script setup lang="ts">
const modelValue = ref(10)
const bufferValue = ref(20)
const interval = ref()

const startBuffer = () => {
  clearInterval(interval.value)

  interval.value = setInterval(() => {
    modelValue.value += Math.random() * (15 - 5) + 5
    bufferValue.value += Math.random() * (15 - 5) + 6
  }, 2000)
}

onMounted(startBuffer)

onBeforeUnmount(() => {
  clearInterval(interval.value)
})

watch(modelValue, () => {
  if (modelValue.value < 100)
    return false

  modelValue.value = 0
  bufferValue.value = 10
  startBuffer()
})
<\/script>

<template>
  <div class="demo-space-y">
    <VProgressLinear
      v-model="modelValue"
      color="primary"
      :buffer-value="bufferValue"
    />
  </div>
</template>
`,js:`<script setup>
const modelValue = ref(10)
const bufferValue = ref(20)
const interval = ref()

const startBuffer = () => {
  clearInterval(interval.value)
  interval.value = setInterval(() => {
    modelValue.value += Math.random() * (15 - 5) + 5
    bufferValue.value += Math.random() * (15 - 5) + 6
  }, 2000)
}

onMounted(startBuffer)
onBeforeUnmount(() => {
  clearInterval(interval.value)
})
watch(modelValue, () => {
  if (modelValue.value < 100)
    return false
  modelValue.value = 0
  bufferValue.value = 10
  startBuffer()
})
<\/script>

<template>
  <div class="demo-space-y">
    <VProgressLinear
      v-model="modelValue"
      color="primary"
      :buffer-value="bufferValue"
    />
  </div>
</template>
`},ee={ts:`<template>
  <div class="demo-space-y">
    <VProgressLinear
      model-value="15"
      color="primary"
    />

    <VProgressLinear
      model-value="30"
      color="secondary"
    />

    <VProgressLinear
      model-value="45"
      color="success"
    />
  </div>
</template>
`,js:`<template>
  <div class="demo-space-y">
    <VProgressLinear
      model-value="15"
      color="primary"
    />

    <VProgressLinear
      model-value="30"
      color="secondary"
    />

    <VProgressLinear
      model-value="45"
      color="success"
    />
  </div>
</template>
`},re={ts:`<template>
  <VProgressLinear
    indeterminate
    color="primary"
  />
</template>
`,js:`<template>
  <VProgressLinear
    indeterminate
    color="primary"
  />
</template>
`},oe={ts:`<template>
  <VProgressLinear
    color="primary"
    indeterminate
    reverse
  />
</template>
`,js:`<template>
  <VProgressLinear
    color="primary"
    indeterminate
    reverse
  />
</template>
`},le={ts:`<template>
  <div class="demo-space-y">
    <VProgressLinear
      model-value="78"
      height="8"
      color="primary"
      :rounded="false"
    />

    <VProgressLinear
      model-value="20"
      color="primary"
      height="20"
      :rounded="false"
    />

    <VProgressLinear
      model-value="33"
      height="20"
      color="primary"
      :rounded="false"
    />
  </div>
</template>
`,js:`<template>
  <div class="demo-space-y">
    <VProgressLinear
      model-value="78"
      height="8"
      color="primary"
      :rounded="false"
    />

    <VProgressLinear
      model-value="20"
      color="primary"
      height="20"
      :rounded="false"
    />

    <VProgressLinear
      model-value="33"
      height="20"
      color="primary"
      :rounded="false"
    />
  </div>
</template>
`},ae={ts:`<script setup lang="ts">
const skill = ref(20)
const knowledge = ref(33)
const power = ref(78)
<\/script>

<template>
  <div class="demo-space-y">
    <VProgressLinear
      v-model="power"
      color="primary"
      height="8"
    />

    <VProgressLinear
      v-model="skill"
      color="primary"
      height="20"
    >
      <template #default="{ value }">
        <span>{{ Math.ceil(value) }}%</span>
      </template>
    </VProgressLinear>

    <VProgressLinear
      v-model="knowledge"
      height="20"
      color="primary"
    >
      <span>{{ Math.ceil(knowledge) }}%</span>
    </VProgressLinear>
  </div>
</template>
`,js:`<script setup>
const skill = ref(20)
const knowledge = ref(33)
const power = ref(78)
<\/script>

<template>
  <div class="demo-space-y">
    <VProgressLinear
      v-model="power"
      color="primary"
      height="8"
    />

    <VProgressLinear
      v-model="skill"
      color="primary"
      height="20"
    >
      <template #default="{ value }">
        <span>{{ Math.ceil(value) }}%</span>
      </template>
    </VProgressLinear>

    <VProgressLinear
      v-model="knowledge"
      height="20"
      color="primary"
    >
      <span>{{ Math.ceil(knowledge) }}%</span>
    </VProgressLinear>
  </div>
</template>
`},te={ts:`<template>
  <div class="demo-space-y">
    <VProgressLinear
      color="rgb(var(--v-theme-primary))"
      model-value="75"
      striped
    />

    <VProgressLinear
      color="rgb(var(--v-theme-success))"
      model-value="55"
      striped
    />

    <VProgressLinear
      color="rgb(var(--v-theme-warning))"
      model-value="35"
      striped
    />
  </div>
</template>
`,js:`<template>
  <div class="demo-space-y">
    <VProgressLinear
      color="rgb(var(--v-theme-primary))"
      model-value="75"
      striped
    />

    <VProgressLinear
      color="rgb(var(--v-theme-success))"
      model-value="55"
      striped
    />

    <VProgressLinear
      color="rgb(var(--v-theme-warning))"
      model-value="35"
      striped
    />
  </div>
</template>
`},Ve=y({__name:"progress-linear",setup(p){return(a,r)=>{const u=X,i=x,c=K,d=H,h=q,b=A,w=R,B=j;return m(),L(I,{class:"match-height"},{default:t(()=>[e(v,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Color",code:n(ee)},{default:t(()=>[r[0]||(r[0]=o("p",null,[l("Use the props "),o("code",null,"color"),l(" and "),o("code",null,"background-color"),l(" to set colors.")],-1)),e(u)]),_:1,__:[0]},8,["code"])]),_:1}),e(v,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Buffering",code:n(Z)},{default:t(()=>[r[1]||(r[1]=o("p",null,[l("The primary value is controlled by "),o("code",null,"v-model"),l(", whereas the buffer is controlled by the "),o("code",null,"buffer-value"),l(" prop.")],-1)),e(c)]),_:1,__:[1]},8,["code"])]),_:1}),e(v,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Indeterminate",code:n(re)},{default:t(()=>[r[2]||(r[2]=o("p",null,[l("for continuously animating progress bar,use prop "),o("code",null,"indeterminate"),l(". This indicates continuous process. ")],-1)),e(d)]),_:1,__:[2]},8,["code"])]),_:1}),e(v,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Reversed",code:n(oe)},{default:t(()=>[r[3]||(r[3]=o("p",null,[l("Use prop "),o("code",null,"reverse"),l(" to animate continuously in reverse direction. The component also has RTL support.")],-1)),e(h)]),_:1,__:[3]},8,["code"])]),_:1}),e(v,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Rounded",code:n(le)},{default:t(()=>[r[4]||(r[4]=o("p",null,[l(" The "),o("code",null," rounded "),l("prop is used to apply a border radius to the v-progress-linear component. By default we have set "),o("code",null,"rounded"),l(" prop true. You can disable it by using "),o("code",null,":rounded='false'"),l(". ")],-1)),e(b)]),_:1,__:[4]},8,["code"])]),_:1}),e(v,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Slots",code:n(ae)},{default:t(()=>[r[5]||(r[5]=o("p",null,[l("The v-progress-linear component will be responsive to user input when using "),o("code",null,"v-model"),l(". You can use the default slot or bind a local model to display inside of the progress.")],-1)),e(w)]),_:1,__:[5]},8,["code"])]),_:1}),e(v,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Striped",code:n(te)},{default:t(()=>[r[6]||(r[6]=o("p",null,[l(" The "),o("code",null,"striped"),l(" prop is used to apply striped background.")],-1)),e(B)]),_:1,__:[6]},8,["code"])]),_:1})]),_:1})}}});export{Ve as default};
