import{V as $}from"./VMenu-BRhTO6H3.js";import{a as w,b as z,d as L,V as A}from"./VList-BlwPx-UL.js";import{V as M}from"./VListItemAction-CDJxc_tn.js";import{d as x,r as V,g as C,o as c,m as a,aU as k,f as l,b as e,t,ae as J,Z as v,b5 as T,b6 as j,c as h,e as n,l as g}from"./main-DF4ImMsz.js";import{V as r}from"./VChip-DESiTJfy.js";import{_ as N}from"./AppCombobox.vue_vue_type_script_setup_true_lang-CExoSgzQ.js";import{_ as y}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{a as B}from"./avatar-1-mrBiEG2D.js";import{a as R}from"./avatar-2-J3iDMrW6.js";import{a as U}from"./avatar-3-CCfT4M2Q.js";import{a as F}from"./avatar-4-CkT0aFRW.js";import{V as I}from"./VAvatar-KJW9RiSb.js";import{_ as Y}from"./AppCardCode.vue_vue_type_style_index_0_lang-ChqO5Cv2.js";import{V as O}from"./VRow-D4Fx1UEY.js";import{V as d}from"./VCol-BhJVNQ4B.js";import"./VOverlay-ni4gZrKW.js";import"./easing-Bybner-F.js";import"./delay-D6gSqbJn.js";import"./lazy-CqeV0WxL.js";import"./scopeId-DPwLlyf4.js";import"./VImg-ig1TV8qx.js";import"./forwardRefs-C-GTDzx5.js";import"./dialog-transition-LrUpPjSc.js";import"./ssrBoot-BO8PYHj1.js";import"./VDivider-BlNdpit1.js";import"./VSlideGroup-CLszu_9k.js";import"./focus-7QqV14mq.js";import"./VCombobox-CjnkVTlr.js";import"./VSelect-CvUTt36S.js";import"./VTextField-BJAtSpKA.js";/* empty css                   */import"./VCounter-NgilWqHp.js";import"./VField-2t37Axr4.js";import"./VInput-CBm1aqL_.js";import"./form-C2L6uOnP.js";import"./VCheckboxBtn-D_ABGJBy.js";import"./VSelectionControl-DaG_LmuL.js";import"./filter-B5D92psg.js";import"./vue3-perfect-scrollbar-Duu3fuSq.js";import"./VCard-pmUgujT6.js";import"./VCardText-DTflXd5N.js";/* empty css              */const Z=x({__name:"DemoChipExpandable",setup(u){const i=V(!1);return(o,m)=>(c(),C($,{modelValue:a(i),"onUpdate:modelValue":m[1]||(m[1]=p=>k(i)?i.value=p:null),transition:"scale-transition"},{activator:l(({props:p})=>[e(r,T(j(p)),{default:l(()=>m[2]||(m[2]=[t(" VueJS ")])),_:2,__:[2]},1040)]),default:l(()=>[e(A,null,{default:l(()=>[e(w,null,{append:l(()=>[e(M,{class:"ms-3"},{default:l(()=>[e(J,{icon:"",variant:"text",size:"x-small",color:"default",onClick:m[0]||(m[0]=p=>i.value=!1)},{default:l(()=>[e(v,{size:"20",icon:"tabler-x"})]),_:1})]),_:1})]),default:l(()=>[e(z,{class:"mb-2"},{default:l(()=>m[3]||(m[3]=[t(" VueJS ")])),_:1,__:[3]}),e(L,null,{default:l(()=>m[4]||(m[4]=[t("The Progressive JavaScript Framework")])),_:1,__:[4]})]),_:1})]),_:1})]),_:1},8,["modelValue"]))}}),q=x({__name:"DemoChipInSelects",setup(u){const i=V(["Programming","Playing games","Sleeping"]),o=V(["Streaming","Eating","Programming","Playing games","Sleeping"]);return(m,p)=>{const _=N;return c(),C(_,{modelValue:a(i),"onUpdate:modelValue":p[0]||(p[0]=b=>k(i)?i.value=b:null),chips:"",clearable:"",multiple:"","closable-chips":"","clear-icon":"tabler-circle-x",items:a(o),label:"Your favorite hobbies","prepend-icon":"tabler-filter"},null,8,["modelValue","items"])}}}),G={},H={class:"demo-space-x"};function K(u,i){return c(),h("div",H,[e(r,{size:"x-small"},{default:l(()=>i[0]||(i[0]=[t(" x-small chip ")])),_:1,__:[0]}),e(r,{size:"small"},{default:l(()=>i[1]||(i[1]=[t(" small chip ")])),_:1,__:[1]}),e(r,{size:"default"},{default:l(()=>i[2]||(i[2]=[t(" Default ")])),_:1,__:[2]}),e(r,{size:"large"},{default:l(()=>i[3]||(i[3]=[t(" large chip ")])),_:1,__:[3]}),e(r,{size:"x-large"},{default:l(()=>i[4]||(i[4]=[t(" x-large chip ")])),_:1,__:[4]})])}const Q=y(G,[["render",K]]),X={class:"demo-space-x"},ii=x({__name:"DemoChipWithAvatar",setup(u){return(i,o)=>(c(),h("div",X,[e(r,null,{default:l(()=>[e(I,{start:"",image:a(B)},null,8,["image"]),o[0]||(o[0]=n("span",null,"John Doe",-1))]),_:1,__:[0]}),e(r,null,{default:l(()=>[e(I,{start:"",image:a(R)},null,8,["image"]),o[1]||(o[1]=n("span",null,"Darcy Nooser",-1))]),_:1,__:[1]}),e(r,{pill:"",label:!1,"prepend-avatar":a(U)},{default:l(()=>o[2]||(o[2]=[n("span",null,"Felicia Risker",-1)])),_:1,__:[2]},8,["prepend-avatar"]),e(r,{pill:"",label:!1},{default:l(()=>[e(I,{start:"",image:a(F)},null,8,["image"]),o[3]||(o[3]=n("span",null,"Minnie Mostly",-1))]),_:1,__:[3]})]))}}),ei={},li={class:"demo-space-x"};function ti(u,i){return c(),h("div",li,[e(r,null,{default:l(()=>[e(v,{start:"",icon:"tabler-user"}),i[0]||(i[0]=t(" Account "))]),_:1,__:[0]}),e(r,{color:"primary"},{default:l(()=>[e(v,{start:"",icon:"tabler-star"}),i[1]||(i[1]=t(" Premium "))]),_:1,__:[1]}),e(r,{color:"secondary"},{default:l(()=>[e(v,{start:"",icon:"tabler-cake"}),i[2]||(i[2]=t(" 1 Year "))]),_:1,__:[2]}),e(r,{color:"success"},{default:l(()=>[e(v,{start:"",icon:"tabler-bell"}),i[3]||(i[3]=t(" Notification "))]),_:1,__:[3]}),e(r,{color:"info"},{default:l(()=>[e(v,{start:"",icon:"tabler-messages"}),i[4]||(i[4]=t(" Message "))]),_:1,__:[4]}),e(r,{color:"warning"},{default:l(()=>[e(v,{start:"",icon:"tabler-alert-triangle"}),i[5]||(i[5]=t(" Warning "))]),_:1,__:[5]}),e(r,{color:"error"},{default:l(()=>[e(v,{start:"",icon:"tabler-alert-circle"}),i[6]||(i[6]=t(" Error "))]),_:1,__:[6]})])}const ri=y(ei,[["render",ti]]),oi={class:"demo-space-x"},ai=x({__name:"DemoChipClosable",setup(u){const i=V(!0),o=V(!0),m=V(!0),p=V(!0),_=V(!0),b=V(!0),S=V(!0);return(D,s)=>(c(),h("div",oi,[a(i)?(c(),C(r,{key:0,closable:"","onClick:close":s[0]||(s[0]=f=>i.value=!a(i))},{default:l(()=>s[7]||(s[7]=[t(" Default ")])),_:1,__:[7]})):g("",!0),a(o)?(c(),C(r,{key:1,closable:"",color:"primary","onClick:close":s[1]||(s[1]=f=>o.value=!a(o))},{default:l(()=>s[8]||(s[8]=[t(" Primary ")])),_:1,__:[8]})):g("",!0),a(m)?(c(),C(r,{key:2,closable:"",color:"secondary","onClick:close":s[2]||(s[2]=f=>m.value=!a(m))},{default:l(()=>s[9]||(s[9]=[t(" Secondary ")])),_:1,__:[9]})):g("",!0),a(p)?(c(),C(r,{key:3,closable:"",color:"success","onClick:close":s[3]||(s[3]=f=>p.value=!a(p))},{default:l(()=>s[10]||(s[10]=[t(" Success ")])),_:1,__:[10]})):g("",!0),a(_)?(c(),C(r,{key:4,closable:"",color:"info","onClick:close":s[4]||(s[4]=f=>_.value=!a(_))},{default:l(()=>s[11]||(s[11]=[t(" Info ")])),_:1,__:[11]})):g("",!0),a(b)?(c(),C(r,{key:5,closable:"",color:"warning","onClick:close":s[5]||(s[5]=f=>b.value=!a(b))},{default:l(()=>s[12]||(s[12]=[t(" Warning ")])),_:1,__:[12]})):g("",!0),a(S)?(c(),C(r,{key:6,closable:"",color:"error","onClick:close":s[6]||(s[6]=f=>S.value=!a(S))},{default:l(()=>s[13]||(s[13]=[t(" Error ")])),_:1,__:[13]})):g("",!0)]))}}),si={},ni={class:"demo-space-x"};function pi(u,i){return c(),h("div",ni,[e(r,{label:!1},{default:l(()=>i[0]||(i[0]=[t(" Default ")])),_:1,__:[0]}),e(r,{label:!1,color:"primary"},{default:l(()=>i[1]||(i[1]=[t(" Primary ")])),_:1,__:[1]}),e(r,{label:!1,color:"secondary"},{default:l(()=>i[2]||(i[2]=[t(" Secondary ")])),_:1,__:[2]}),e(r,{label:!1,color:"success"},{default:l(()=>i[3]||(i[3]=[t(" Success ")])),_:1,__:[3]}),e(r,{label:!1,color:"info"},{default:l(()=>i[4]||(i[4]=[t(" Info ")])),_:1,__:[4]}),e(r,{label:!1,color:"warning"},{default:l(()=>i[5]||(i[5]=[t(" Warning ")])),_:1,__:[5]}),e(r,{label:!1,color:"error"},{default:l(()=>i[6]||(i[6]=[t(" Error ")])),_:1,__:[6]})])}const ci=y(si,[["render",pi]]),mi={},ui={class:"demo-space-x"};function di(u,i){return c(),h("div",ui,[e(r,{variant:"outlined"},{default:l(()=>i[0]||(i[0]=[t(" Default ")])),_:1,__:[0]}),e(r,{color:"primary",variant:"outlined"},{default:l(()=>i[1]||(i[1]=[t(" Primary ")])),_:1,__:[1]}),e(r,{color:"secondary",variant:"outlined"},{default:l(()=>i[2]||(i[2]=[t(" Secondary ")])),_:1,__:[2]}),e(r,{color:"success",variant:"outlined"},{default:l(()=>i[3]||(i[3]=[t(" Success ")])),_:1,__:[3]}),e(r,{color:"info",variant:"outlined"},{default:l(()=>i[4]||(i[4]=[t(" Info ")])),_:1,__:[4]}),e(r,{color:"warning",variant:"outlined"},{default:l(()=>i[5]||(i[5]=[t(" Warning ")])),_:1,__:[5]}),e(r,{color:"error",variant:"outlined"},{default:l(()=>i[6]||(i[6]=[t(" Error ")])),_:1,__:[6]})])}const Vi=y(mi,[["render",di]]),Ci={},fi={class:"demo-space-x"};function vi(u,i){return c(),h("div",fi,[e(r,{variant:"elevated"},{default:l(()=>i[0]||(i[0]=[t(" Default ")])),_:1,__:[0]}),e(r,{color:"primary",variant:"elevated"},{default:l(()=>i[1]||(i[1]=[t(" Primary ")])),_:1,__:[1]}),e(r,{color:"secondary",variant:"elevated"},{default:l(()=>i[2]||(i[2]=[t(" Secondary ")])),_:1,__:[2]}),e(r,{color:"success",variant:"elevated"},{default:l(()=>i[3]||(i[3]=[t(" Success ")])),_:1,__:[3]}),e(r,{color:"info",variant:"elevated"},{default:l(()=>i[4]||(i[4]=[t(" Info ")])),_:1,__:[4]}),e(r,{color:"warning",variant:"elevated"},{default:l(()=>i[5]||(i[5]=[t(" Warning ")])),_:1,__:[5]}),e(r,{color:"error",variant:"elevated"},{default:l(()=>i[6]||(i[6]=[t(" Error ")])),_:1,__:[6]})])}const hi=y(Ci,[["render",vi]]),_i={},bi={class:"demo-space-x"};function gi(u,i){return c(),h("div",bi,[e(r,null,{default:l(()=>i[0]||(i[0]=[t(" Default ")])),_:1,__:[0]}),e(r,{color:"primary"},{default:l(()=>i[1]||(i[1]=[t(" Primary ")])),_:1,__:[1]}),e(r,{color:"secondary"},{default:l(()=>i[2]||(i[2]=[t(" Secondary ")])),_:1,__:[2]}),e(r,{color:"success"},{default:l(()=>i[3]||(i[3]=[t(" Success ")])),_:1,__:[3]}),e(r,{color:"info"},{default:l(()=>i[4]||(i[4]=[t(" Info ")])),_:1,__:[4]}),e(r,{color:"warning"},{default:l(()=>i[5]||(i[5]=[t(" Warning ")])),_:1,__:[5]}),e(r,{color:"error"},{default:l(()=>i[6]||(i[6]=[t(" Error ")])),_:1,__:[6]})])}const yi=y(_i,[["render",gi]]),Si={ts:`<script lang="ts" setup>
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
`},ge=x({__name:"chip",setup(u){return(i,o)=>{const m=yi,p=Y,_=hi,b=Vi,S=ci,D=ai,s=ri,f=ii,P=Q,E=q,W=Z;return c(),C(O,{class:"match-height"},{default:l(()=>[e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Color",code:a(xi)},{default:l(()=>[o[0]||(o[0]=n("p",null,[t("Use "),n("code",null,"color"),t(" prop to change the background color of chips.")],-1)),e(m)]),_:1,__:[0]},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Elevated",code:a(Ii)},{default:l(()=>[o[1]||(o[1]=n("p",null,[t("Use "),n("code",null,"elevated"),t(" variant option to create filled chips.")],-1)),e(_)]),_:1,__:[1]},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Outlined",code:a(Pi)},{default:l(()=>[o[2]||(o[2]=n("p",null,[t("Use "),n("code",null,"outlined"),t(" variant option to create outline border chips.")],-1)),e(b)]),_:1,__:[2]},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Rounded",code:a(Ei)},{default:l(()=>[o[3]||(o[3]=n("p",null,[t("To use the rounded chip, set "),n("code",null,"label"),t(" props value to "),n("strong",null,"false"),t(".")],-1)),e(S)]),_:1,__:[3]},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Closable",code:a(Si)},{default:l(()=>[o[4]||(o[4]=n("p",null,[t("Closable chips can be controlled with a "),n("code",null,"v-model"),t(".")],-1)),e(D)]),_:1,__:[4]},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"With Icon",code:a(wi)},{default:l(()=>[o[5]||(o[5]=n("p",null,"Chips can use text or any icon available in the Material Icons font library.",-1)),e(s)]),_:1,__:[5]},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"With Avatar",code:a($i)},{default:l(()=>[o[6]||(o[6]=n("p",null,[t("Use "),n("code",null,"pill"),t(" prop to remove the "),n("code",null,"v-avatar"),t(" padding.")],-1)),e(f)]),_:1,__:[6]},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Sizes",code:a(Wi)},{default:l(()=>[o[7]||(o[7]=n("p",null,[t("The "),n("code",null,"v-chip"),t(" component can have various sizes from "),n("code",null,"x-small"),t(" to "),n("code",null,"x-large"),t(".")],-1)),e(P)]),_:1,__:[7]},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"In Selects",code:a(ki)},{default:l(()=>[o[8]||(o[8]=n("p",null,[t("Selects can use "),n("code",null,"chips"),t(" to display the selected data. Try adding your own tags below.")],-1)),e(E)]),_:1,__:[8]},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Expandable",code:a(Di)},{default:l(()=>[o[9]||(o[9]=n("p",null,[t("Chips can be combined with "),n("code",null,"v-menu"),t(" to enable a specific set of actions for a chip.")],-1)),e(W)]),_:1,__:[9]},8,["code"])]),_:1})]),_:1})}}});export{ge as default};
