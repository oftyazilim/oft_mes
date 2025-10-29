import{V as $}from"./VMenu-Nfu2PsQC.js";import{V as w,a as z,b as L,d as A}from"./VList-C_aYqxAR.js";import{V as M}from"./VListItemAction-0RFAsNxd.js";import{d as x,r as V,g as C,l as o,aE as k,f as l,b as e,aF as t,T,y as v,aS as J,aT as j,o as c,x as y,c as h,e as n,m as g}from"./main-CC0S-MzY.js";import{V as r}from"./VChip-BJQNnCkB.js";import{_ as N}from"./AppCombobox.vue_vue_type_script_setup_true_lang-DhYCdFkA.js";import{a as B}from"./avatar-1-mrBiEG2D.js";import{a as R}from"./avatar-2-J3iDMrW6.js";import{a as F}from"./avatar-3-CCfT4M2Q.js";import{a as U}from"./avatar-4-CkT0aFRW.js";import{V as I}from"./VAvatar-BHWqmz1z.js";import{_ as Y}from"./AppCardCode.vue_vue_type_style_index_0_lang-DquBEQX-.js";import{V as O}from"./VRow-BiJFK6vz.js";import{V as d}from"./VCol-BvCN_tSW.js";import"./VOverlay-DbQ1vHif.js";import"./easing-Bybner-F.js";import"./delay-CICNbzHL.js";import"./lazy-DVIh6mwp.js";import"./scopeId-DkPTWbgx.js";import"./index-B4A6uSHN.js";import"./forwardRefs-C-GTDzx5.js";import"./dialog-transition-Cr-FaDmH.js";import"./ssrBoot-CNOVlBo_.js";import"./createSimpleFunctional-Dz-Ql0QD.js";import"./VDivider-BBoiUOj5.js";import"./VSlideGroup-qSVJZc4J.js";import"./form-kZDJ4E4v.js";import"./VCombobox-CiJRPnj8.js";import"./VSelect-CjrUJam9.js";import"./VTextField-BnwXvZKs.js";/* empty css                   */import"./VCounter-CnFz9zRk.js";import"./VField-Cjbo2t6i.js";import"./VInput-CE-nSsDz.js";import"./VCheckboxBtn-BozgCNkb.js";import"./VSelectionControl-Cx3tW_P7.js";import"./filter-BUR4TqDn.js";import"./VImg-D4hgsP19.js";import"./VCard-XhBRIBq8.js";import"./VCardText-C_q08Dmo.js";/* empty css              */const q=x({__name:"DemoChipExpandable",setup(u){const i=V(!1);return(a,m)=>(c(),C($,{modelValue:o(i),"onUpdate:modelValue":m[1]||(m[1]=p=>k(i)?i.value=p:null),transition:"scale-transition"},{activator:l(({props:p})=>[e(r,J(j(p)),{default:l(()=>m[2]||(m[2]=[t(" VueJS ")])),_:2,__:[2]},1040)]),default:l(()=>[e(w,null,{default:l(()=>[e(z,null,{append:l(()=>[e(M,{class:"ms-3"},{default:l(()=>[e(T,{icon:"",variant:"text",size:"x-small",color:"default",onClick:m[0]||(m[0]=p=>i.value=!1)},{default:l(()=>[e(v,{size:"20",icon:"tabler-x"})]),_:1})]),_:1})]),default:l(()=>[e(L,{class:"mb-2"},{default:l(()=>m[3]||(m[3]=[t(" VueJS ")])),_:1,__:[3]}),e(A,null,{default:l(()=>m[4]||(m[4]=[t("The Progressive JavaScript Framework")])),_:1,__:[4]})]),_:1})]),_:1})]),_:1},8,["modelValue"]))}}),G=x({__name:"DemoChipInSelects",setup(u){const i=V(["Programming","Playing games","Sleeping"]),a=V(["Streaming","Eating","Programming","Playing games","Sleeping"]);return(m,p)=>{const _=N;return c(),C(_,{modelValue:o(i),"onUpdate:modelValue":p[0]||(p[0]=b=>k(i)?i.value=b:null),chips:"",clearable:"",multiple:"","closable-chips":"","clear-icon":"tabler-circle-x",items:o(a),label:"Your favorite hobbies","prepend-icon":"tabler-filter"},null,8,["modelValue","items"])}}}),H={},K={class:"demo-space-x"};function Q(u,i){return c(),h("div",K,[e(r,{size:"x-small"},{default:l(()=>i[0]||(i[0]=[t(" x-small chip ")])),_:1,__:[0]}),e(r,{size:"small"},{default:l(()=>i[1]||(i[1]=[t(" small chip ")])),_:1,__:[1]}),e(r,{size:"default"},{default:l(()=>i[2]||(i[2]=[t(" Default ")])),_:1,__:[2]}),e(r,{size:"large"},{default:l(()=>i[3]||(i[3]=[t(" large chip ")])),_:1,__:[3]}),e(r,{size:"x-large"},{default:l(()=>i[4]||(i[4]=[t(" x-large chip ")])),_:1,__:[4]})])}const X=y(H,[["render",Q]]),Z={class:"demo-space-x"},ii=x({__name:"DemoChipWithAvatar",setup(u){return(i,a)=>(c(),h("div",Z,[e(r,null,{default:l(()=>[e(I,{start:"",image:o(B)},null,8,["image"]),a[0]||(a[0]=n("span",null,"John Doe",-1))]),_:1,__:[0]}),e(r,null,{default:l(()=>[e(I,{start:"",image:o(R)},null,8,["image"]),a[1]||(a[1]=n("span",null,"Darcy Nooser",-1))]),_:1,__:[1]}),e(r,{pill:"",label:!1,"prepend-avatar":o(F)},{default:l(()=>a[2]||(a[2]=[n("span",null,"Felicia Risker",-1)])),_:1,__:[2]},8,["prepend-avatar"]),e(r,{pill:"",label:!1},{default:l(()=>[e(I,{start:"",image:o(U)},null,8,["image"]),a[3]||(a[3]=n("span",null,"Minnie Mostly",-1))]),_:1,__:[3]})]))}}),ei={},li={class:"demo-space-x"};function ti(u,i){return c(),h("div",li,[e(r,null,{default:l(()=>[e(v,{start:"",icon:"tabler-user"}),i[0]||(i[0]=t(" Account "))]),_:1,__:[0]}),e(r,{color:"primary"},{default:l(()=>[e(v,{start:"",icon:"tabler-star"}),i[1]||(i[1]=t(" Premium "))]),_:1,__:[1]}),e(r,{color:"secondary"},{default:l(()=>[e(v,{start:"",icon:"tabler-cake"}),i[2]||(i[2]=t(" 1 Year "))]),_:1,__:[2]}),e(r,{color:"success"},{default:l(()=>[e(v,{start:"",icon:"tabler-bell"}),i[3]||(i[3]=t(" Notification "))]),_:1,__:[3]}),e(r,{color:"info"},{default:l(()=>[e(v,{start:"",icon:"tabler-messages"}),i[4]||(i[4]=t(" Message "))]),_:1,__:[4]}),e(r,{color:"warning"},{default:l(()=>[e(v,{start:"",icon:"tabler-alert-triangle"}),i[5]||(i[5]=t(" Warning "))]),_:1,__:[5]}),e(r,{color:"error"},{default:l(()=>[e(v,{start:"",icon:"tabler-alert-circle"}),i[6]||(i[6]=t(" Error "))]),_:1,__:[6]})])}const ri=y(ei,[["render",ti]]),ai={class:"demo-space-x"},oi=x({__name:"DemoChipClosable",setup(u){const i=V(!0),a=V(!0),m=V(!0),p=V(!0),_=V(!0),b=V(!0),S=V(!0);return(D,s)=>(c(),h("div",ai,[o(i)?(c(),C(r,{key:0,closable:"","onClick:close":s[0]||(s[0]=f=>i.value=!o(i))},{default:l(()=>s[7]||(s[7]=[t(" Default ")])),_:1,__:[7]})):g("",!0),o(a)?(c(),C(r,{key:1,closable:"",color:"primary","onClick:close":s[1]||(s[1]=f=>a.value=!o(a))},{default:l(()=>s[8]||(s[8]=[t(" Primary ")])),_:1,__:[8]})):g("",!0),o(m)?(c(),C(r,{key:2,closable:"",color:"secondary","onClick:close":s[2]||(s[2]=f=>m.value=!o(m))},{default:l(()=>s[9]||(s[9]=[t(" Secondary ")])),_:1,__:[9]})):g("",!0),o(p)?(c(),C(r,{key:3,closable:"",color:"success","onClick:close":s[3]||(s[3]=f=>p.value=!o(p))},{default:l(()=>s[10]||(s[10]=[t(" Success ")])),_:1,__:[10]})):g("",!0),o(_)?(c(),C(r,{key:4,closable:"",color:"info","onClick:close":s[4]||(s[4]=f=>_.value=!o(_))},{default:l(()=>s[11]||(s[11]=[t(" Info ")])),_:1,__:[11]})):g("",!0),o(b)?(c(),C(r,{key:5,closable:"",color:"warning","onClick:close":s[5]||(s[5]=f=>b.value=!o(b))},{default:l(()=>s[12]||(s[12]=[t(" Warning ")])),_:1,__:[12]})):g("",!0),o(S)?(c(),C(r,{key:6,closable:"",color:"error","onClick:close":s[6]||(s[6]=f=>S.value=!o(S))},{default:l(()=>s[13]||(s[13]=[t(" Error ")])),_:1,__:[13]})):g("",!0)]))}}),si={},ni={class:"demo-space-x"};function pi(u,i){return c(),h("div",ni,[e(r,{label:!1},{default:l(()=>i[0]||(i[0]=[t(" Default ")])),_:1,__:[0]}),e(r,{label:!1,color:"primary"},{default:l(()=>i[1]||(i[1]=[t(" Primary ")])),_:1,__:[1]}),e(r,{label:!1,color:"secondary"},{default:l(()=>i[2]||(i[2]=[t(" Secondary ")])),_:1,__:[2]}),e(r,{label:!1,color:"success"},{default:l(()=>i[3]||(i[3]=[t(" Success ")])),_:1,__:[3]}),e(r,{label:!1,color:"info"},{default:l(()=>i[4]||(i[4]=[t(" Info ")])),_:1,__:[4]}),e(r,{label:!1,color:"warning"},{default:l(()=>i[5]||(i[5]=[t(" Warning ")])),_:1,__:[5]}),e(r,{label:!1,color:"error"},{default:l(()=>i[6]||(i[6]=[t(" Error ")])),_:1,__:[6]})])}const ci=y(si,[["render",pi]]),mi={},ui={class:"demo-space-x"};function di(u,i){return c(),h("div",ui,[e(r,{variant:"outlined"},{default:l(()=>i[0]||(i[0]=[t(" Default ")])),_:1,__:[0]}),e(r,{color:"primary",variant:"outlined"},{default:l(()=>i[1]||(i[1]=[t(" Primary ")])),_:1,__:[1]}),e(r,{color:"secondary",variant:"outlined"},{default:l(()=>i[2]||(i[2]=[t(" Secondary ")])),_:1,__:[2]}),e(r,{color:"success",variant:"outlined"},{default:l(()=>i[3]||(i[3]=[t(" Success ")])),_:1,__:[3]}),e(r,{color:"info",variant:"outlined"},{default:l(()=>i[4]||(i[4]=[t(" Info ")])),_:1,__:[4]}),e(r,{color:"warning",variant:"outlined"},{default:l(()=>i[5]||(i[5]=[t(" Warning ")])),_:1,__:[5]}),e(r,{color:"error",variant:"outlined"},{default:l(()=>i[6]||(i[6]=[t(" Error ")])),_:1,__:[6]})])}const Vi=y(mi,[["render",di]]),Ci={},fi={class:"demo-space-x"};function vi(u,i){return c(),h("div",fi,[e(r,{variant:"elevated"},{default:l(()=>i[0]||(i[0]=[t(" Default ")])),_:1,__:[0]}),e(r,{color:"primary",variant:"elevated"},{default:l(()=>i[1]||(i[1]=[t(" Primary ")])),_:1,__:[1]}),e(r,{color:"secondary",variant:"elevated"},{default:l(()=>i[2]||(i[2]=[t(" Secondary ")])),_:1,__:[2]}),e(r,{color:"success",variant:"elevated"},{default:l(()=>i[3]||(i[3]=[t(" Success ")])),_:1,__:[3]}),e(r,{color:"info",variant:"elevated"},{default:l(()=>i[4]||(i[4]=[t(" Info ")])),_:1,__:[4]}),e(r,{color:"warning",variant:"elevated"},{default:l(()=>i[5]||(i[5]=[t(" Warning ")])),_:1,__:[5]}),e(r,{color:"error",variant:"elevated"},{default:l(()=>i[6]||(i[6]=[t(" Error ")])),_:1,__:[6]})])}const hi=y(Ci,[["render",vi]]),_i={},bi={class:"demo-space-x"};function gi(u,i){return c(),h("div",bi,[e(r,null,{default:l(()=>i[0]||(i[0]=[t(" Default ")])),_:1,__:[0]}),e(r,{color:"primary"},{default:l(()=>i[1]||(i[1]=[t(" Primary ")])),_:1,__:[1]}),e(r,{color:"secondary"},{default:l(()=>i[2]||(i[2]=[t(" Secondary ")])),_:1,__:[2]}),e(r,{color:"success"},{default:l(()=>i[3]||(i[3]=[t(" Success ")])),_:1,__:[3]}),e(r,{color:"info"},{default:l(()=>i[4]||(i[4]=[t(" Info ")])),_:1,__:[4]}),e(r,{color:"warning"},{default:l(()=>i[5]||(i[5]=[t(" Warning ")])),_:1,__:[5]}),e(r,{color:"error"},{default:l(()=>i[6]||(i[6]=[t(" Error ")])),_:1,__:[6]})])}const yi=y(_i,[["render",gi]]),Si={ts:`<script lang="ts" setup>
const isDefaultChipVisible = ref(true)
const isPrimaryChipVisible = ref(true)
const isSecondaryChipVisible = ref(true)
const isSuccessChipVisible = ref(true)
const isInfoChipVisible = ref(true)
const isWarningChipVisible = ref(true)
const isErrorChipVisible = ref(true)
<\/script>

<template>
  <div class="demo-space-x">
    <VChip
      v-if="isDefaultChipVisible"
      closable
      @click:close="isDefaultChipVisible = !isDefaultChipVisible"
    >
      Default
    </VChip>

    <VChip
      v-if="isPrimaryChipVisible"
      closable
      color="primary"
      @click:close="isPrimaryChipVisible = !isPrimaryChipVisible"
    >
      Primary
    </VChip>

    <VChip
      v-if="isSecondaryChipVisible"
      closable
      color="secondary"
      @click:close="isSecondaryChipVisible = !isSecondaryChipVisible"
    >
      Secondary
    </VChip>

    <VChip
      v-if="isSuccessChipVisible"
      closable
      color="success"
      @click:close="isSuccessChipVisible = !isSuccessChipVisible"
    >
      Success
    </VChip>

    <VChip
      v-if="isInfoChipVisible"
      closable
      color="info"
      @click:close="isInfoChipVisible = !isInfoChipVisible"
    >
      Info
    </VChip>

    <VChip
      v-if="isWarningChipVisible"
      closable
      color="warning"
      @click:close="isWarningChipVisible = !isWarningChipVisible"
    >
      Warning
    </VChip>

    <VChip
      v-if="isErrorChipVisible"
      closable
      color="error"
      @click:close="isErrorChipVisible = !isErrorChipVisible"
    >
      Error
    </VChip>
  </div>
</template>
`,js:`<script setup>
const isDefaultChipVisible = ref(true)
const isPrimaryChipVisible = ref(true)
const isSecondaryChipVisible = ref(true)
const isSuccessChipVisible = ref(true)
const isInfoChipVisible = ref(true)
const isWarningChipVisible = ref(true)
const isErrorChipVisible = ref(true)
<\/script>

<template>
  <div class="demo-space-x">
    <VChip
      v-if="isDefaultChipVisible"
      closable
      @click:close="isDefaultChipVisible = !isDefaultChipVisible"
    >
      Default
    </VChip>

    <VChip
      v-if="isPrimaryChipVisible"
      closable
      color="primary"
      @click:close="isPrimaryChipVisible = !isPrimaryChipVisible"
    >
      Primary
    </VChip>

    <VChip
      v-if="isSecondaryChipVisible"
      closable
      color="secondary"
      @click:close="isSecondaryChipVisible = !isSecondaryChipVisible"
    >
      Secondary
    </VChip>

    <VChip
      v-if="isSuccessChipVisible"
      closable
      color="success"
      @click:close="isSuccessChipVisible = !isSuccessChipVisible"
    >
      Success
    </VChip>

    <VChip
      v-if="isInfoChipVisible"
      closable
      color="info"
      @click:close="isInfoChipVisible = !isInfoChipVisible"
    >
      Info
    </VChip>

    <VChip
      v-if="isWarningChipVisible"
      closable
      color="warning"
      @click:close="isWarningChipVisible = !isWarningChipVisible"
    >
      Warning
    </VChip>

    <VChip
      v-if="isErrorChipVisible"
      closable
      color="error"
      @click:close="isErrorChipVisible = !isErrorChipVisible"
    >
      Error
    </VChip>
  </div>
</template>
`},xi={ts:`<template>
  <div class="demo-space-x">
    <VChip>
      Default
    </VChip>

    <VChip color="primary">
      Primary
    </VChip>

    <VChip color="secondary">
      Secondary
    </VChip>

    <VChip color="success">
      Success
    </VChip>

    <VChip color="info">
      Info
    </VChip>

    <VChip color="warning">
      Warning
    </VChip>

    <VChip color="error">
      Error
    </VChip>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VChip>
      Default
    </VChip>

    <VChip color="primary">
      Primary
    </VChip>

    <VChip color="secondary">
      Secondary
    </VChip>

    <VChip color="success">
      Success
    </VChip>

    <VChip color="info">
      Info
    </VChip>

    <VChip color="warning">
      Warning
    </VChip>

    <VChip color="error">
      Error
    </VChip>
  </div>
</template>
`},Ii={ts:`<template>
  <div class="demo-space-x">
    <VChip variant="elevated">
      Default
    </VChip>

    <VChip
      color="primary"
      variant="elevated"
    >
      Primary
    </VChip>

    <VChip
      color="secondary"
      variant="elevated"
    >
      Secondary
    </VChip>

    <VChip
      color="success"
      variant="elevated"
    >
      Success
    </VChip>

    <VChip
      color="info"
      variant="elevated"
    >
      Info
    </VChip>

    <VChip
      color="warning"
      variant="elevated"
    >
      Warning
    </VChip>

    <VChip
      color="error"
      variant="elevated"
    >
      Error
    </VChip>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VChip variant="elevated">
      Default
    </VChip>

    <VChip
      color="primary"
      variant="elevated"
    >
      Primary
    </VChip>

    <VChip
      color="secondary"
      variant="elevated"
    >
      Secondary
    </VChip>

    <VChip
      color="success"
      variant="elevated"
    >
      Success
    </VChip>

    <VChip
      color="info"
      variant="elevated"
    >
      Info
    </VChip>

    <VChip
      color="warning"
      variant="elevated"
    >
      Warning
    </VChip>

    <VChip
      color="error"
      variant="elevated"
    >
      Error
    </VChip>
  </div>
</template>
`},Di={ts:`<script lang="ts" setup>
const isMenuVisible = ref(false)
<\/script>

<template>
  <VMenu
    v-model="isMenuVisible"
    transition="scale-transition"
  >
    <!-- v-menu activator -->
    <template #activator="{ props }">
      <VChip v-bind="props">
        VueJS
      </VChip>
    </template>

    <!-- v-menu list -->
    <VList>
      <VListItem>
        <VListItemTitle class="mb-2">
          VueJS
        </VListItemTitle>
        <VListItemSubtitle>The Progressive JavaScript Framework</VListItemSubtitle>

        <template #append>
          <VListItemAction class="ms-3">
            <VBtn
              icon
              variant="text"
              size="x-small"
              color="default"
              @click="isMenuVisible = false"
            >
              <VIcon
                size="20"
                icon="tabler-x"
              />
            </VBtn>
          </VListItemAction>
        </template>
      </VListItem>
    </VList>
  </VMenu>
</template>
`,js:`<script setup>
const isMenuVisible = ref(false)
<\/script>

<template>
  <VMenu
    v-model="isMenuVisible"
    transition="scale-transition"
  >
    <!-- v-menu activator -->
    <template #activator="{ props }">
      <VChip v-bind="props">
        VueJS
      </VChip>
    </template>

    <!-- v-menu list -->
    <VList>
      <VListItem>
        <VListItemTitle class="mb-2">
          VueJS
        </VListItemTitle>
        <VListItemSubtitle>The Progressive JavaScript Framework</VListItemSubtitle>

        <template #append>
          <VListItemAction class="ms-3">
            <VBtn
              icon
              variant="text"
              size="x-small"
              color="default"
              @click="isMenuVisible = false"
            >
              <VIcon
                size="20"
                icon="tabler-x"
              />
            </VBtn>
          </VListItemAction>
        </template>
      </VListItem>
    </VList>
  </VMenu>
</template>
`},ki={ts:`<script lang="ts" setup>
const chips = ref(['Programming', 'Playing games', 'Sleeping'])
const items = ref(['Streaming', 'Eating', 'Programming', 'Playing games', 'Sleeping'])
<\/script>

<template>
  <AppCombobox
    v-model="chips"
    chips
    clearable
    multiple
    closable-chips
    clear-icon="tabler-circle-x"
    :items="items"
    label="Your favorite hobbies"
    prepend-icon="tabler-filter"
  />
</template>
`,js:`<script setup>
const chips = ref([
  'Programming',
  'Playing games',
  'Sleeping',
])

const items = ref([
  'Streaming',
  'Eating',
  'Programming',
  'Playing games',
  'Sleeping',
])
<\/script>

<template>
  <AppCombobox
    v-model="chips"
    chips
    clearable
    multiple
    closable-chips
    clear-icon="tabler-circle-x"
    :items="items"
    label="Your favorite hobbies"
    prepend-icon="tabler-filter"
  />
</template>
`},Pi={ts:`<template>
  <div class="demo-space-x">
    <VChip variant="outlined">
      Default
    </VChip>

    <VChip
      color="primary"
      variant="outlined"
    >
      Primary
    </VChip>

    <VChip
      color="secondary"
      variant="outlined"
    >
      Secondary
    </VChip>

    <VChip
      color="success"
      variant="outlined"
    >
      Success
    </VChip>

    <VChip
      color="info"
      variant="outlined"
    >
      Info
    </VChip>

    <VChip
      color="warning"
      variant="outlined"
    >
      Warning
    </VChip>

    <VChip
      color="error"
      variant="outlined"
    >
      Error
    </VChip>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VChip variant="outlined">
      Default
    </VChip>

    <VChip
      color="primary"
      variant="outlined"
    >
      Primary
    </VChip>

    <VChip
      color="secondary"
      variant="outlined"
    >
      Secondary
    </VChip>

    <VChip
      color="success"
      variant="outlined"
    >
      Success
    </VChip>

    <VChip
      color="info"
      variant="outlined"
    >
      Info
    </VChip>

    <VChip
      color="warning"
      variant="outlined"
    >
      Warning
    </VChip>

    <VChip
      color="error"
      variant="outlined"
    >
      Error
    </VChip>
  </div>
</template>
`},Ei={ts:`<template>
  <div class="demo-space-x">
    <VChip :label="false">
      Default
    </VChip>

    <VChip
      :label="false"
      color="primary"
    >
      Primary
    </VChip>

    <VChip
      :label="false"
      color="secondary"
    >
      Secondary
    </VChip>

    <VChip
      :label="false"
      color="success"
    >
      Success
    </VChip>

    <VChip
      :label="false"
      color="info"
    >
      Info
    </VChip>

    <VChip
      :label="false"
      color="warning"
    >
      Warning
    </VChip>

    <VChip
      :label="false"
      color="error"
    >
      Error
    </VChip>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VChip :label="false">
      Default
    </VChip>

    <VChip
      :label="false"
      color="primary"
    >
      Primary
    </VChip>

    <VChip
      :label="false"
      color="secondary"
    >
      Secondary
    </VChip>

    <VChip
      :label="false"
      color="success"
    >
      Success
    </VChip>

    <VChip
      :label="false"
      color="info"
    >
      Info
    </VChip>

    <VChip
      :label="false"
      color="warning"
    >
      Warning
    </VChip>

    <VChip
      :label="false"
      color="error"
    >
      Error
    </VChip>
  </div>
</template>
`},Wi={ts:`<template>
  <div class="demo-space-x">
    <VChip size="x-small">
      x-small chip
    </VChip>

    <VChip size="small">
      small chip
    </VChip>

    <VChip size="default">
      Default
    </VChip>

    <VChip size="large">
      large chip
    </VChip>

    <VChip size="x-large">
      x-large chip
    </VChip>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VChip size="x-small">
      x-small chip
    </VChip>

    <VChip size="small">
      small chip
    </VChip>

    <VChip size="default">
      Default
    </VChip>

    <VChip size="large">
      large chip
    </VChip>

    <VChip size="x-large">
      x-large chip
    </VChip>
  </div>
</template>
`},$i={ts:`<script setup lang="ts">
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar3 from '@images/avatars/avatar-3.png'
import avatar4 from '@images/avatars/avatar-4.png'
<\/script>

<template>
  <div class="demo-space-x">
    <VChip>
      <VAvatar
        start
        :image="avatar1"
      />
      <span>John Doe</span>
    </VChip>

    <VChip>
      <VAvatar
        start
        :image="avatar2"
      />
      <span>Darcy Nooser</span>
    </VChip>

    <VChip
      pill
      :label="false"
      :prepend-avatar="avatar3"
    >
      <span>Felicia Risker</span>
    </VChip>

    <VChip
      pill
      :label="false"
    >
      <VAvatar
        start
        :image="avatar4"
      />
      <span>Minnie Mostly</span>
    </VChip>
  </div>
</template>
`,js:`<script setup>
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar3 from '@images/avatars/avatar-3.png'
import avatar4 from '@images/avatars/avatar-4.png'
<\/script>

<template>
  <div class="demo-space-x">
    <VChip>
      <VAvatar
        start
        :image="avatar1"
      />
      <span>John Doe</span>
    </VChip>

    <VChip>
      <VAvatar
        start
        :image="avatar2"
      />
      <span>Darcy Nooser</span>
    </VChip>

    <VChip
      pill
      :label="false"
      :prepend-avatar="avatar3"
    >
      <span>Felicia Risker</span>
    </VChip>

    <VChip
      pill
      :label="false"
    >
      <VAvatar
        start
        :image="avatar4"
      />
      <span>Minnie Mostly</span>
    </VChip>
  </div>
</template>
`},wi={ts:`<template>
  <div class="demo-space-x">
    <VChip>
      <VIcon
        start
        icon="tabler-user"
      />
      Account
    </VChip>

    <VChip color="primary">
      <VIcon
        start
        icon="tabler-star"
      />
      Premium
    </VChip>

    <VChip color="secondary">
      <VIcon
        start
        icon="tabler-cake"
      />
      1 Year
    </VChip>

    <VChip color="success">
      <VIcon
        start
        icon="tabler-bell"
      />
      Notification
    </VChip>

    <VChip color="info">
      <VIcon
        start
        icon="tabler-messages"
      />
      Message
    </VChip>

    <VChip color="warning">
      <VIcon
        start
        icon="tabler-alert-triangle"
      />
      Warning
    </VChip>

    <VChip color="error">
      <VIcon
        start
        icon="tabler-alert-circle"
      />
      Error
    </VChip>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VChip>
      <VIcon
        start
        icon="tabler-user"
      />
      Account
    </VChip>

    <VChip color="primary">
      <VIcon
        start
        icon="tabler-star"
      />
      Premium
    </VChip>

    <VChip color="secondary">
      <VIcon
        start
        icon="tabler-cake"
      />
      1 Year
    </VChip>

    <VChip color="success">
      <VIcon
        start
        icon="tabler-bell"
      />
      Notification
    </VChip>

    <VChip color="info">
      <VIcon
        start
        icon="tabler-messages"
      />
      Message
    </VChip>

    <VChip color="warning">
      <VIcon
        start
        icon="tabler-alert-triangle"
      />
      Warning
    </VChip>

    <VChip color="error">
      <VIcon
        start
        icon="tabler-alert-circle"
      />
      Error
    </VChip>
  </div>
</template>
`},be=x({__name:"chip",setup(u){return(i,a)=>{const m=yi,p=Y,_=hi,b=Vi,S=ci,D=oi,s=ri,f=ii,P=X,E=G,W=q;return c(),C(O,{class:"match-height"},{default:l(()=>[e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Color",code:o(xi)},{default:l(()=>[a[0]||(a[0]=n("p",null,[t("Use "),n("code",null,"color"),t(" prop to change the background color of chips.")],-1)),e(m)]),_:1,__:[0]},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Elevated",code:o(Ii)},{default:l(()=>[a[1]||(a[1]=n("p",null,[t("Use "),n("code",null,"elevated"),t(" variant option to create filled chips.")],-1)),e(_)]),_:1,__:[1]},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Outlined",code:o(Pi)},{default:l(()=>[a[2]||(a[2]=n("p",null,[t("Use "),n("code",null,"outlined"),t(" variant option to create outline border chips.")],-1)),e(b)]),_:1,__:[2]},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Rounded",code:o(Ei)},{default:l(()=>[a[3]||(a[3]=n("p",null,[t("To use the rounded chip, set "),n("code",null,"label"),t(" props value to "),n("strong",null,"false"),t(".")],-1)),e(S)]),_:1,__:[3]},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Closable",code:o(Si)},{default:l(()=>[a[4]||(a[4]=n("p",null,[t("Closable chips can be controlled with a "),n("code",null,"v-model"),t(".")],-1)),e(D)]),_:1,__:[4]},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"With Icon",code:o(wi)},{default:l(()=>[a[5]||(a[5]=n("p",null,"Chips can use text or any icon available in the Material Icons font library.",-1)),e(s)]),_:1,__:[5]},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"With Avatar",code:o($i)},{default:l(()=>[a[6]||(a[6]=n("p",null,[t("Use "),n("code",null,"pill"),t(" prop to remove the "),n("code",null,"v-avatar"),t(" padding.")],-1)),e(f)]),_:1,__:[6]},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Sizes",code:o(Wi)},{default:l(()=>[a[7]||(a[7]=n("p",null,[t("The "),n("code",null,"v-chip"),t(" component can have various sizes from "),n("code",null,"x-small"),t(" to "),n("code",null,"x-large"),t(".")],-1)),e(P)]),_:1,__:[7]},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"In Selects",code:o(ki)},{default:l(()=>[a[8]||(a[8]=n("p",null,[t("Selects can use "),n("code",null,"chips"),t(" to display the selected data. Try adding your own tags below.")],-1)),e(E)]),_:1,__:[8]},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Expandable",code:o(Di)},{default:l(()=>[a[9]||(a[9]=n("p",null,[t("Chips can be combined with "),n("code",null,"v-menu"),t(" to enable a specific set of actions for a chip.")],-1)),e(W)]),_:1,__:[9]},8,["code"])]),_:1})]),_:1})}}});export{be as default};
