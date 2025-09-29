import{V as s}from"./VFileInput-CU3UgpKF.js";import{d as F,r as V,w as j,g as r,u as c,aA as b,o as u,f as i,c as S,au as N,aB as t,k,F as A,l as d,b as e,e as l}from"./main-CAZR5bAJ.js";import{V as L}from"./VChip-DDZLXQFu.js";import{V as p}from"./VCol-Mzv22ct2.js";import{V as g}from"./VRow-1_PPLSl2.js";import{_ as U}from"./AppCardCode.vue_vue_type_style_index_0_lang-D-O01DPk.js";import"./VField-DEWoTdGi.js";import"./focus-CKHiS2bZ.js";import"./easing-Bybner-F.js";import"./VInput-Dcf0zN3V.js";import"./VImg-B8ri0-tY.js";import"./form-DhgEvaCe.js";import"./forwardRefs-C-GTDzx5.js";import"./VCounter-DCwwL0WD.js";import"./VSlideGroup-DYhFm4gI.js";import"./VAvatar-fzpuo2L0.js";/* empty css              */import"./VCard-D3M-RDL5.js";import"./VCardText-uZHlghNb.js";import"./VDivider-D8hhdUUU.js";const B=F({__name:"DemoFileInputLoading",setup(m){const o=V(),n=V(!0);return j(o,()=>{n.value=!o.value[0]}),(_,a)=>(u(),r(s,{modelValue:c(o),"onUpdate:modelValue":a[0]||(a[0]=f=>b(o)?o.value=f:null),loading:c(n),color:"primary",label:"File input",variant:"outlined"},null,8,["modelValue","loading"]))}}),P=F({__name:"DemoFileInputSelectionSlot",setup(m){const o=V([]);return(n,_)=>(u(),r(s,{modelValue:c(o),"onUpdate:modelValue":_[0]||(_[0]=a=>b(o)?o.value=a:null),multiple:"",placeholder:"Upload your documents",label:"File input","prepend-icon":"tabler-paperclip"},{selection:i(({fileNames:a})=>[(u(!0),S(A,null,N(a,f=>(u(),r(L,{key:f,label:"",size:"small",color:"primary",class:"me-2"},{default:i(()=>[t(k(f),1)]),_:2},1024))),128))]),_:1},8,["modelValue"]))}}),R=F({__name:"DemoFileInputValidation",setup(m){const o=[n=>!n||!n.length||n[0].size<1e6||"Avatar size should be less than 1 MB!"];return(n,_)=>(u(),r(s,{rules:o,label:"Avatar",accept:"image/png, image/jpeg, image/bmp",placeholder:"Pick an avatar","prepend-icon":"tabler-camera"}))}}),M={};function T(m,o){return u(),r(s,{"show-size":"",label:"File input"})}const O=d(M,[["render",T]]),Y={};function E(m,o){return u(),r(s,{label:"File input","prepend-icon":"tabler-camera"})}const W=d(Y,[["render",E]]),q={};function G(m,o){return u(),r(s,{multiple:"",label:"File input"})}const H=d(q,[["render",G]]),J={};function K(m,o){return u(),r(s,{"show-size":"",counter:"",multiple:"",label:"File input"})}const Q=d(J,[["render",K]]),X={};function Z(m,o){return u(),r(s,{chips:"",label:"File input w/ chips"})}const ee=d(X,[["render",Z]]),le={};function te(m,o){return u(),r(s,{accept:"image/*",label:"File input"})}const ne=d(le,[["render",te]]),ie={};function oe(m,o){return u(),r(g,null,{default:i(()=>[e(p,{cols:"12",sm:"6"},{default:i(()=>[e(s,{label:"Outlined"})]),_:1}),e(p,{cols:"12",sm:"6"},{default:i(()=>[e(s,{label:"Filled",variant:"filled"})]),_:1}),e(p,{cols:"12",sm:"6"},{default:i(()=>[e(s,{label:"Solo",variant:"solo"})]),_:1}),e(p,{cols:"12",sm:"6"},{default:i(()=>[e(s,{label:"Plain",variant:"plain"})]),_:1}),e(p,{cols:"12",sm:"6"},{default:i(()=>[e(s,{label:"Underlined",variant:"underlined",density:"default"})]),_:1})]),_:1})}const ae=d(ie,[["render",oe]]),pe={};function se(m,o){return u(),r(s,{label:"File input",density:"compact"})}const ce=d(pe,[["render",se]]),ue={};function re(m,o){return u(),r(s,{label:"File input"})}const me=d(ue,[["render",re]]),de={ts:`<template>
  <VFileInput
    accept="image/*"
    label="File input"
  />
</template>
`,js:`<template>
  <VFileInput
    accept="image/*"
    label="File input"
  />
</template>
`},_e={ts:`<template>
  <VFileInput label="File input" />
</template>
`,js:`<template>
  <VFileInput label="File input" />
</template>
`},fe={ts:`<template>
  <VFileInput
    chips
    label="File input w/ chips"
  />
</template>
`,js:`<template>
  <VFileInput
    chips
    label="File input w/ chips"
  />
</template>
`},Fe={ts:`<template>
  <VFileInput
    show-size
    counter
    multiple
    label="File input"
  />
</template>
`,js:`<template>
  <VFileInput
    show-size
    counter
    multiple
    label="File input"
  />
</template>
`},Ve={ts:`<template>
  <VFileInput
    label="File input"
    density="compact"
  />
</template>
`,js:`<template>
  <VFileInput
    label="File input"
    density="compact"
  />
</template>
`},be={ts:`<script setup lang="ts">
const file = ref()
const loading = ref(true)

watch(file, () => {
  loading.value = !file.value[0]
})
<\/script>

<template>
  <VFileInput
    v-model="file"
    :loading="loading"
    color="primary"
    label="File input"
    variant="outlined"
  />
</template>
`,js:`<script setup>
const file = ref()
const loading = ref(true)

watch(file, () => {
  loading.value = !file.value[0]
})
<\/script>

<template>
  <VFileInput
    v-model="file"
    :loading="loading"
    color="primary"
    label="File input"
    variant="outlined"
  />
</template>
`},ge={ts:`<template>
  <VFileInput
    multiple
    label="File input"
  />
</template>
`,js:`<template>
  <VFileInput
    multiple
    label="File input"
  />
</template>
`},he={ts:`<template>
  <VFileInput
    label="File input"
    prepend-icon="tabler-camera"
  />
</template>
`,js:`<template>
  <VFileInput
    label="File input"
    prepend-icon="tabler-camera"
  />
</template>
`},Ie={ts:`<script lang="ts" setup>
const files = ref<File[]>([])
<\/script>

<template>
  <VFileInput
    v-model="files"
    multiple
    placeholder="Upload your documents"
    label="File input"
    prepend-icon="tabler-paperclip"
  >
    <template #selection="{ fileNames }">
      <template
        v-for="fileName in fileNames"
        :key="fileName"
      >
        <VChip
          label
          size="small"
          color="primary"
          class="me-2"
        >
          {{ fileName }}
        </VChip>
      </template>
    </template>
  </VFileInput>
</template>
`,js:`<script setup>
const files = ref([])
<\/script>

<template>
  <VFileInput
    v-model="files"
    multiple
    placeholder="Upload your documents"
    label="File input"
    prepend-icon="tabler-paperclip"
  >
    <template #selection="{ fileNames }">
      <template
        v-for="fileName in fileNames"
        :key="fileName"
      >
        <VChip
          label
          size="small"
          color="primary"
          class="me-2"
        >
          {{ fileName }}
        </VChip>
      </template>
    </template>
  </VFileInput>
</template>
`},ve={ts:`<template>
  <VFileInput
    show-size
    label="File input"
  />
</template>
`,js:`<template>
  <VFileInput
    show-size
    label="File input"
  />
</template>
`},ye={ts:`<script lang="ts" setup>
const rules = [
  (fileList: FileList) => !fileList || !fileList.length || fileList[0].size < 1000000 || 'Avatar size should be less than 1 MB!',
]
<\/script>

<template>
  <VFileInput
    :rules="rules"
    label="Avatar"
    accept="image/png, image/jpeg, image/bmp"
    placeholder="Pick an avatar"
    prepend-icon="tabler-camera"
  />
</template>
`,js:`<script setup>
const rules = [fileList => !fileList || !fileList.length || fileList[0].size < 1000000 || 'Avatar size should be less than 1 MB!']
<\/script>

<template>
  <VFileInput
    :rules="rules"
    label="Avatar"
    accept="image/png, image/jpeg, image/bmp"
    placeholder="Pick an avatar"
    prepend-icon="tabler-camera"
  />
</template>
`},Ce={ts:`<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <VFileInput label="Outlined" />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VFileInput
        label="Filled"
        variant="filled"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VFileInput
        label="Solo"
        variant="solo"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VFileInput
        label="Plain"
        variant="plain"
      />
    </VCol>
    <VCol
      cols="12"
      sm="6"
    >
      <VFileInput
        label="Underlined"
        variant="underlined"
        density="default"
      />
    </VCol>
  </VRow>
</template>
`,js:`<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <VFileInput label="Outlined" />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VFileInput
        label="Filled"
        variant="filled"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VFileInput
        label="Solo"
        variant="solo"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VFileInput
        label="Plain"
        variant="plain"
      />
    </VCol>
    <VCol
      cols="12"
      sm="6"
    >
      <VFileInput
        label="Underlined"
        variant="underlined"
        density="default"
      />
    </VCol>
  </VRow>
</template>
`},We=F({__name:"file-input",setup(m){return(o,n)=>{const _=me,a=U,f=ce,h=ae,I=ne,v=ee,y=Q,C=H,w=W,z=O,$=R,D=P,x=B;return u(),r(g,{class:"match-height"},{default:i(()=>[e(p,{cols:"12",md:"6"},{default:i(()=>[e(a,{title:"Basic",code:c(_e)},{default:i(()=>[n[0]||(n[0]=l("p",null,[t("The "),l("code",null,"v-file-input"),t(" component is used to selecting files.")],-1)),e(_)]),_:1,__:[0]},8,["code"])]),_:1}),e(p,{cols:"12",md:"6"},{default:i(()=>[e(a,{title:"Density",code:c(Ve)},{default:i(()=>[n[1]||(n[1]=l("p",null,[t("You can reduces the file input height with "),l("code",null,"density"),t(" prop. Available options are: "),l("code",null,"default"),t(", "),l("code",null,"comfortable"),t(", and "),l("code",null,"compact"),t(".")],-1)),e(f)]),_:1,__:[1]},8,["code"])]),_:1}),e(p,{cols:"12"},{default:i(()=>[e(a,{title:"Variant",code:c(Ce)},{default:i(()=>[n[2]||(n[2]=l("p",null,[t("use "),l("code",null,"solo"),t(", "),l("code",null,"filled"),t(", "),l("code",null,"outlined"),t(", "),l("code",null,"plain"),t(" and "),l("code",null,"underlined"),t(" option of "),l("code",null,"variant"),t(" prop to change the look of file input.")],-1)),e(h)]),_:1,__:[2]},8,["code"])]),_:1}),e(p,{cols:"12",md:"6"},{default:i(()=>[e(a,{title:"Accept",code:c(de)},{default:i(()=>[n[3]||(n[3]=l("p",null,[l("code",null,"v-file-input"),t(" component can accept only specific media formats/file types if you want.")],-1)),e(I)]),_:1,__:[3]},8,["code"])]),_:1}),e(p,{cols:"12",md:"6"},{default:i(()=>[e(a,{title:"Chips",code:c(fe)},{default:i(()=>[n[4]||(n[4]=l("p",null,[t("Use "),l("code",null,"chip"),t(" prop to display the selected file as a chip.")],-1)),e(v)]),_:1,__:[4]},8,["code"])]),_:1}),e(p,{cols:"12",md:"6"},{default:i(()=>[e(a,{title:"Counter",code:c(Fe)},{default:i(()=>[n[5]||(n[5]=l("p",null,[t("When using the "),l("code",null,"show-size"),t(" property along with "),l("code",null,"counter"),t(", the total number of files and size will be displayed under the input.")],-1)),e(y)]),_:1,__:[5]},8,["code"])]),_:1}),e(p,{cols:"12",md:"6"},{default:i(()=>[e(a,{title:"Multiple",code:c(ge)},{default:i(()=>[n[6]||(n[6]=l("p",null,[t(" The "),l("code",null,"v-file-input"),t(" can contain multiple files at the same time when using the "),l("code",null,"multiple"),t(" prop. ")],-1)),e(C)]),_:1,__:[6]},8,["code"])]),_:1}),e(p,{cols:"12",md:"6"},{default:i(()=>[e(a,{title:"Prepend icon",code:c(he)},{default:i(()=>[n[7]||(n[7]=l("p",null,[t(" The "),l("code",null,"v-file-input"),t(" has a default "),l("code",null,"prepend-icon"),t(" that can be set on the component or adjusted globally. ")],-1)),e(w)]),_:1,__:[7]},8,["code"])]),_:1}),e(p,{cols:"12",md:"6"},{default:i(()=>[e(a,{title:"Show size",code:c(ve)},{default:i(()=>[n[8]||(n[8]=l("p",null,[t("The displayed size of the selected file(s) can be configured with the "),l("code",null,"show-size"),t(" property.")],-1)),e(z)]),_:1,__:[8]},8,["code"])]),_:1}),e(p,{cols:"12",md:"6"},{default:i(()=>[e(a,{title:"Validation",code:c(ye)},{default:i(()=>[n[9]||(n[9]=l("p",null,[t("You can use the "),l("code",null,"rules"),t(" prop to create your own custom validation parameters.")],-1)),e($)]),_:1,__:[9]},8,["code"])]),_:1}),e(p,{cols:"12",md:"6"},{default:i(()=>[e(a,{title:"Selection slot",code:c(Ie)},{default:i(()=>[n[10]||(n[10]=l("p",null,[t("Using the "),l("code",null,"selection"),t(" slot, you can customize the appearance of your input selections.")],-1)),e(D)]),_:1,__:[10]},8,["code"])]),_:1}),e(p,{cols:"12",md:"6"},{default:i(()=>[e(a,{title:"Loading",code:c(be)},{default:i(()=>[n[11]||(n[11]=l("p",null,[t("Use "),l("code",null,"loading"),t(" prop to displays linear progress bar.")],-1)),e(x)]),_:1,__:[11]},8,["code"])]),_:1})]),_:1})}}});export{We as default};
