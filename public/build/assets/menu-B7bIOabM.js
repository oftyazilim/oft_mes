import{V as l}from"./VMenu-Nfu2PsQC.js";import{V as m,a as L}from"./VList-C_aYqxAR.js";import{V as y}from"./VTooltip-BABwUCJh.js";import{d as _,g as B,f as e,b as t,e as p,T as u,aS as c,aT as d,ai as T,aF as i,o as V,r as k,l as v,aE as C,a as h,c as b,F as D,ay as P,p as S}from"./main-CC0S-MzY.js";import{a as I}from"./avatar-1-mrBiEG2D.js";import{V as A,b as $}from"./VCard-XhBRIBq8.js";import{V as j}from"./VDivider-BBoiUOj5.js";import{V as F}from"./VCardText-C_q08Dmo.js";import{_ as E}from"./AppCardCode.vue_vue_type_style_index_0_lang-DquBEQX-.js";import{V as R}from"./VRow-BiJFK6vz.js";import{V as O}from"./VCol-BvCN_tSW.js";import"./VOverlay-DbQ1vHif.js";import"./easing-Bybner-F.js";import"./delay-CICNbzHL.js";import"./lazy-DVIh6mwp.js";import"./scopeId-DkPTWbgx.js";import"./index-B4A6uSHN.js";import"./forwardRefs-C-GTDzx5.js";import"./dialog-transition-Cr-FaDmH.js";import"./ssrBoot-CNOVlBo_.js";import"./createSimpleFunctional-Dz-Ql0QD.js";import"./VAvatar-BHWqmz1z.js";import"./VImg-D4hgsP19.js";/* empty css              */const G=_({__name:"DemoMenuActivatorAndTooltip",setup(f){const a=[{title:"Option 1",value:"Option 1"},{title:"Option 2",value:"Option 2"},{title:"Option 3",value:"Option 3"}];return(s,o)=>(V(),B(l,{location:"top"},{activator:e(({props:n})=>[t(y,{location:"top"},{activator:e(({props:r})=>[t(u,c(d(T(n,r))),{default:e(()=>o[0]||(o[0]=[i(" Dropdown w/ Tooltip ")])),_:2,__:[0]},1040)]),default:e(()=>[o[1]||(o[1]=p("span",null,"I am a Tooltip",-1))]),_:2,__:[1]},1024)]),default:e(()=>[t(m,{items:a})]),_:1}))}}),H=_({__name:"DemoMenuPopover",setup(f){const a=k(!1);return(s,o)=>{const n=h("IconBtn");return V(),B(l,{modelValue:v(a),"onUpdate:modelValue":o[0]||(o[0]=r=>C(a)?a.value=r:null),location:"top"},{activator:e(({props:r})=>[t(u,c(d(r)),{default:e(()=>o[1]||(o[1]=[i(" Menu as Popover ")])),_:2,__:[1]},1040)]),default:e(()=>[t(A,{"max-width":"300"},{default:e(()=>[t(m,null,{default:e(()=>[t(L,{"prepend-avatar":v(I),title:"John Leider",subtitle:"Founder of Vuetify",class:"mx-0"},null,8,["prepend-avatar"])]),_:1}),t(j),t(F,null,{default:e(()=>o[2]||(o[2]=[i(" Gingerbread bear claw cake. Soufflé candy sesame snaps chocolate ice cream cake. Dessert candy canes oat cake pudding cupcake. Bear claw sweet wafer bonbon dragée toffee. ")])),_:1,__:[2]}),t($,null,{default:e(()=>[t(n,{icon:"tabler-heart"}),t(n,{icon:"tabler-bookmark"}),t(n,{icon:"tabler-thumb-down"})]),_:1})]),_:1})]),_:1},8,["modelValue"])}}}),J=_({__name:"DemoMenuOpenOnHover",setup(f){const a=[{title:"Option 1",value:"Option 1"},{title:"Option 2",value:"Option 2"},{title:"Option 3",value:"Option 3"}];return(s,o)=>(V(),B(l,{"open-on-hover":""},{activator:e(({props:n})=>[t(u,c(d(n)),{default:e(()=>o[0]||(o[0]=[i(" On hover ")])),_:2,__:[0]},1040)]),default:e(()=>[t(m,{items:a})]),_:1}))}}),N={class:"demo-space-x"},X=_({__name:"DemoMenuLocation",setup(f){const a=[{title:"Option 1",value:"Option 1"},{title:"Option 2",value:"Option 2"},{title:"Option 3",value:"Option 3"}];return(s,o)=>(V(),b("div",N,[t(l,{location:"top"},{activator:e(({props:n})=>[t(u,c(d(n)),{default:e(()=>o[0]||(o[0]=[i(" Top ")])),_:2,__:[0]},1040)]),default:e(()=>[t(m,{items:a})]),_:1}),t(l,{location:"bottom"},{activator:e(({props:n})=>[t(u,c(d(n)),{default:e(()=>o[1]||(o[1]=[i(" Bottom ")])),_:2,__:[1]},1040)]),default:e(()=>[t(m,{items:a})]),_:1}),t(l,{location:"start"},{activator:e(({props:n})=>[t(u,c(d(n)),{default:e(()=>o[2]||(o[2]=[i(" Start ")])),_:2,__:[2]},1040)]),default:e(()=>[t(m,{items:a})]),_:1}),t(l,{location:"end"},{activator:e(({props:n})=>[t(u,c(d(n)),{default:e(()=>o[3]||(o[3]=[i(" End ")])),_:2,__:[3]},1040)]),default:e(()=>[t(m,{items:a})]),_:1})]))}}),Y={class:"demo-space-x"},U=_({__name:"DemoMenuCustomTransitions",setup(f){const a=[{title:"Option 1",value:"Option 1"},{title:"Option 2",value:"Option 2"},{title:"Option 3",value:"Option 3"}];return(s,o)=>(V(),b("div",Y,[t(l,{transition:"scale-transition"},{activator:e(({props:n})=>[t(u,c(d(n)),{default:e(()=>o[0]||(o[0]=[i(" Scale Transition ")])),_:2,__:[0]},1040)]),default:e(()=>[t(m,{items:a})]),_:1}),t(l,{transition:"slide-x-transition"},{activator:e(({props:n})=>[t(u,c(d(n)),{default:e(()=>o[1]||(o[1]=[i(" Slide X Transition ")])),_:2,__:[1]},1040)]),default:e(()=>[t(m,{items:a})]),_:1}),t(l,{transition:"slide-y-transition"},{activator:e(({props:n})=>[t(u,c(d(n)),{default:e(()=>o[2]||(o[2]=[i(" Slide Y Transition ")])),_:2,__:[2]},1040)]),default:e(()=>[t(m,{items:a})]),_:1})]))}}),z={class:"demo-space-x"},W=_({__name:"DemoMenuBasic",setup(f){const a=["primary","secondary","success","info","warning","error"],s=[{title:"Option 1",value:"Option 1"},{title:"Option 2",value:"Option 2"},{title:"Option 3",value:"Option 3"}];return(o,n)=>(V(),b("div",z,[(V(),b(D,null,P(a,r=>t(l,{key:r},{activator:e(({props:M})=>[t(u,T({color:r,ref_for:!0},M),{default:e(()=>[i(S(r),1)]),_:2},1040,["color"])]),default:e(()=>[t(m,{items:s})]),_:2},1024)),64))]))}}),q={ts:`<script lang="ts" setup>
import { mergeProps } from 'vue'

const items = [{ title: 'Option 1', value: 'Option 1' }, { title: 'Option 2', value: 'Option 2' }, { title: 'Option 3', value: 'Option 3' }]
<\/script>

<template>
  <VMenu location="top">
    <template #activator="{ props: menuProps }">
      <VTooltip location="top">
        <template #activator="{ props: tooltipProps }">
          <VBtn v-bind="mergeProps(menuProps, tooltipProps)">
            Dropdown w/ Tooltip
          </VBtn>
        </template>
        <span>I am a Tooltip</span>
      </VTooltip>
    </template>

    <VList :items="items" />
  </VMenu>
</template>
`,js:`<script setup>
import { mergeProps } from 'vue'

const items = [
  {
    title: 'Option 1',
    value: 'Option 1',
  },
  {
    title: 'Option 2',
    value: 'Option 2',
  },
  {
    title: 'Option 3',
    value: 'Option 3',
  },
]
<\/script>

<template>
  <VMenu location="top">
    <template #activator="{ props: menuProps }">
      <VTooltip location="top">
        <template #activator="{ props: tooltipProps }">
          <VBtn v-bind="mergeProps(menuProps, tooltipProps)">
            Dropdown w/ Tooltip
          </VBtn>
        </template>
        <span>I am a Tooltip</span>
      </VTooltip>
    </template>

    <VList :items="items" />
  </VMenu>
</template>
`},K={ts:`<script lang="ts" setup>
const menusVariant = ['primary', 'secondary', 'success', 'info', 'warning', 'error']
const items = [{ title: 'Option 1', value: 'Option 1' }, { title: 'Option 2', value: 'Option 2' }, { title: 'Option 3', value: 'Option 3' }]
<\/script>

<template>
  <div class="demo-space-x">
    <VMenu
      v-for="menu in menusVariant"
      :key="menu"
    >
      <template #activator="{ props }">
        <VBtn
          :color="menu"
          v-bind="props"
        >
          {{ menu }}
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>
  </div>
</template>
`,js:`<script setup>
const menusVariant = [
  'primary',
  'secondary',
  'success',
  'info',
  'warning',
  'error',
]

const items = [
  {
    title: 'Option 1',
    value: 'Option 1',
  },
  {
    title: 'Option 2',
    value: 'Option 2',
  },
  {
    title: 'Option 3',
    value: 'Option 3',
  },
]
<\/script>

<template>
  <div class="demo-space-x">
    <VMenu
      v-for="menu in menusVariant"
      :key="menu"
    >
      <template #activator="{ props }">
        <VBtn
          :color="menu"
          v-bind="props"
        >
          {{ menu }}
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>
  </div>
</template>
`},Q={ts:`<script lang="ts" setup>
const items = [{ title: 'Option 1', value: 'Option 1' }, { title: 'Option 2', value: 'Option 2' }, { title: 'Option 3', value: 'Option 3' }]
<\/script>

<template>
  <div class="demo-space-x">
    <VMenu transition="scale-transition">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Scale Transition
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu transition="slide-x-transition">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Slide X Transition
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu transition="slide-y-transition">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Slide Y Transition
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>
  </div>
</template>
`,js:`<script setup>
const items = [
  {
    title: 'Option 1',
    value: 'Option 1',
  },
  {
    title: 'Option 2',
    value: 'Option 2',
  },
  {
    title: 'Option 3',
    value: 'Option 3',
  },
]
<\/script>

<template>
  <div class="demo-space-x">
    <VMenu transition="scale-transition">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Scale Transition
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu transition="slide-x-transition">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Slide X Transition
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu transition="slide-y-transition">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Slide Y Transition
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>
  </div>
</template>
`},Z={ts:`<script lang="ts" setup>
const items = [{ title: 'Option 1', value: 'Option 1' }, { title: 'Option 2', value: 'Option 2' }, { title: 'Option 3', value: 'Option 3' }]
<\/script>

<template>
  <div class="demo-space-x">
    <VMenu location="top">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Top
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu location="bottom">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Bottom
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu location="start">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Start
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu location="end">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          End
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>
  </div>
</template>
`,js:`<script setup>
const items = [
  {
    title: 'Option 1',
    value: 'Option 1',
  },
  {
    title: 'Option 2',
    value: 'Option 2',
  },
  {
    title: 'Option 3',
    value: 'Option 3',
  },
]
<\/script>

<template>
  <div class="demo-space-x">
    <VMenu location="top">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Top
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu location="bottom">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Bottom
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu location="start">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Start
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu location="end">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          End
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>
  </div>
</template>
`},tt={ts:`<script lang="ts" setup>
const items = [{ title: 'Option 1', value: 'Option 1' }, { title: 'Option 2', value: 'Option 2' }, { title: 'Option 3', value: 'Option 3' }]
<\/script>

<template>
  <VMenu open-on-hover>
    <template #activator="{ props }">
      <VBtn v-bind="props">
        On hover
      </VBtn>
    </template>

    <VList :items="items" />
  </VMenu>
</template>
`,js:`<script setup>
const items = [
  {
    title: 'Option 1',
    value: 'Option 1',
  },
  {
    title: 'Option 2',
    value: 'Option 2',
  },
  {
    title: 'Option 3',
    value: 'Option 3',
  },
]
<\/script>

<template>
  <VMenu open-on-hover>
    <template #activator="{ props }">
      <VBtn v-bind="props">
        On hover
      </VBtn>
    </template>

    <VList :items="items" />
  </VMenu>
</template>
`},et={ts:`<script lang="ts" setup>
import avatar1 from '@images/avatars/avatar-1.png'

const menu = ref(false)
<\/script>

<template>
  <VMenu
    v-model="menu"
    location="top"
  >
    <template #activator="{ props }">
      <VBtn v-bind="props">
        Menu as Popover
      </VBtn>
    </template>

    <VCard max-width="300">
      <VList>
        <VListItem
          :prepend-avatar="avatar1"
          title="John Leider"
          subtitle="Founder of Vuetify"
          class="mx-0"
        />
      </VList>

      <VDivider />

      <VCardText>
        Gingerbread bear claw cake. Soufflé candy sesame snaps chocolate ice cream cake.
        Dessert candy canes oat cake pudding cupcake. Bear claw sweet wafer bonbon dragée toffee.
      </VCardText>

      <VCardActions>
        <IconBtn icon="tabler-heart" />
        <IconBtn icon="tabler-bookmark" />
        <IconBtn icon="tabler-thumb-down" />
      </VCardActions>
    </VCard>
  </VMenu>
</template>
`,js:`<script setup>
import avatar1 from '@images/avatars/avatar-1.png'

const menu = ref(false)
<\/script>

<template>
  <VMenu
    v-model="menu"
    location="top"
  >
    <template #activator="{ props }">
      <VBtn v-bind="props">
        Menu as Popover
      </VBtn>
    </template>

    <VCard max-width="300">
      <VList>
        <VListItem
          :prepend-avatar="avatar1"
          title="John Leider"
          subtitle="Founder of Vuetify"
          class="mx-0"
        />
      </VList>

      <VDivider />

      <VCardText>
        Gingerbread bear claw cake. Soufflé candy sesame snaps chocolate ice cream cake.
        Dessert candy canes oat cake pudding cupcake. Bear claw sweet wafer bonbon dragée toffee.
      </VCardText>

      <VCardActions>
        <IconBtn icon="tabler-heart" />
        <IconBtn icon="tabler-bookmark" />
        <IconBtn icon="tabler-thumb-down" />
      </VCardActions>
    </VCard>
  </VMenu>
</template>
`},Lt=_({__name:"menu",setup(f){return(a,s)=>{const o=W,n=E,r=U,M=X,g=J,w=H,x=G;return V(),B(R,{class:"match-height"},{default:e(()=>[t(O,{cols:"12",md:"6"},{default:e(()=>[t(n,{title:"Basic",code:v(K)},{default:e(()=>[s[0]||(s[0]=p("p",null," Remember to put the element that activates the menu in the activator slot. ",-1)),t(o)]),_:1,__:[0]},8,["code"])]),_:1}),t(O,{cols:"12",md:"6"},{default:e(()=>[t(n,{title:"Custom transitions",code:v(Q)},{default:e(()=>[s[1]||(s[1]=p("p",null,[i("Vuetify comes with 3 standard transitions, "),p("code",null,"scale"),i(", "),p("code",null,"slide-x"),i(" and "),p("code",null,"slide-y"),i(". Use "),p("code",null,"transition"),i(" prop to add transition to a menu.")],-1)),t(r)]),_:1,__:[1]},8,["code"])]),_:1}),t(O,{cols:"12",md:"6"},{default:e(()=>[t(n,{title:"Location",code:v(Z)},{default:e(()=>[s[2]||(s[2]=p("p",null,[i("Menu can be offset relative to the activator by using the "),p("code",null,"location"),i(" prop.")],-1)),t(M)]),_:1,__:[2]},8,["code"])]),_:1}),t(O,{cols:"12",md:"6"},{default:e(()=>[t(n,{title:"Open on hover",code:v(tt)},{default:e(()=>[s[3]||(s[3]=p("p",null,[i("Menus can be accessed using hover instead of clicking with the "),p("code",null,"open-on-hover"),i(" prop.")],-1)),t(g)]),_:1,__:[3]},8,["code"])]),_:1}),t(O,{cols:"12",md:"6"},{default:e(()=>[t(n,{title:"Popover",code:v(et)},{default:e(()=>[s[4]||(s[4]=p("p",null,"A menu can be configured to be static when opened, allowing it to function as a popover. This can be useful when there are multiple interactive items within the menu contents.",-1)),t(w)]),_:1,__:[4]},8,["code"])]),_:1}),t(O,{cols:"12",md:"6"},{default:e(()=>[t(n,{title:"Activator and tooltip",code:v(q)},{default:e(()=>[s[5]||(s[5]=p("p",null,[i("With the new "),p("code",null,"v-slot"),i(" syntax, nested activators such as those seen with a "),p("code",null,"v-menu"),i(" and "),p("code",null,"v-tooltip"),i(" attached to the same activator button, need a particular setup in order to function correctly")],-1)),t(x)]),_:1,__:[5]},8,["code"])]),_:1})]),_:1})}}});export{Lt as default};
