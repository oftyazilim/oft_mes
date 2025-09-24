import{_ as b,V as A}from"./AppAutocomplete.vue_vue_type_script_setup_true_lang-B-47HFb9.js";import{d as g,r as S,g as h,o as v,m as r,aU as C,f as l,b as a,Z as T,eH as G,w as I,q as w,e as s,t as n}from"./main-BuFlbFoB.js";import{a as F}from"./avatar-1-mrBiEG2D.js";import{a as D}from"./avatar-2-J3iDMrW6.js";import{a as W}from"./avatar-3-CCfT4M2Q.js";import{a as R}from"./avatar-4-CkT0aFRW.js";import{a as O}from"./avatar-5-roKDnu6B.js";import{a as H}from"./avatar-6-D6_LJK0H.js";import{a as L}from"./avatar-7-BqLdN_QF.js";import{a as U}from"./avatar-8-x-cqZxtu.js";import{a as E}from"./VList-Cj2fj59X.js";import{V as P}from"./VChip-CC2vVqkp.js";import{V}from"./VRow-upMvWQZB.js";import{V as u}from"./VCol-Cmdke4Ye.js";import{_ as q}from"./AppCardCode.vue_vue_type_style_index_0_lang-BXKpwKj5.js";import"./focus-XlYqf9t_.js";import"./VSelect-DwxJsfwL.js";import"./VTextField-kEUWuOqC.js";/* empty css                   */import"./VCounter-NuYK0B5N.js";import"./VImg-B96WrjZ5.js";import"./VField-DOB4DUX_.js";import"./easing-Bybner-F.js";import"./VInput-DVNYuhM3.js";import"./form-CH1KWc_x.js";import"./forwardRefs-C-GTDzx5.js";import"./dialog-transition-DykugV7V.js";import"./VMenu-C2STc1Y0.js";import"./VOverlay-Ch4F6s8E.js";import"./delay-Cnb0ZFPa.js";import"./lazy-10-C2jWw.js";import"./scopeId-C4A71ple.js";import"./VCheckboxBtn-DwxcjTMS.js";import"./VSelectionControl-8grhmBCm.js";import"./VAvatar-upru2IB3.js";import"./filter-DEDmy-BJ.js";import"./ssrBoot-BKRizT2w.js";import"./VDivider-ffl4p6lR.js";import"./VSlideGroup-BJPp97A_.js";/* empty css              */import"./vue3-perfect-scrollbar-B2V-EmjG.js";import"./VCard-DEtsoI_r.js";import"./VCardText-DTHkAN_U.js";const $=g({__name:"DemoAutocompleteValidation",setup(f){const t=["foo","bar","fizz","buzz"],e=S(["foo"]),p=[o=>!!o.length||"Select at least one option."];return(o,m)=>{const c=b;return v(),h(c,{modelValue:r(e),"onUpdate:modelValue":m[0]||(m[0]=i=>C(e)?e.value=i:null),items:t,rules:p,placeholder:"Select Option",multiple:""},null,8,["modelValue"])}}}),z=g({__name:"DemoAutocompleteStateSelector",setup(f){const t=S(!1),e=S(null),p=["Alabama","Alaska","American Samoa","Arizona","Arkansas","California","Colorado","Connecticut","Delaware","District of Columbia","Federated States of Micronesia","Florida","Georgia","Guam","Hawaii","Idaho","Illinois","Indiana","Iowa","Kansas","Kentucky","Louisiana","Maine","Marshall Islands","Maryland","Massachusetts","Michigan","Minnesota","Mississippi","Missouri","Montana","Nebraska","Nevada","New Hampshire","New Jersey","New Mexico","New York","North Carolina","North Dakota","Northern Mariana Islands","Ohio","Oklahoma","Oregon","Palau","Pennsylvania","Puerto Rico","Rhode Island","South Carolina","South Dakota","Tennessee","Texas","Utah","Vermont","Virgin Island","Virginia","Washington","West Virginia","Wisconsin","Wyoming"];return(o,m)=>{const c=b;return v(),h(c,{modelValue:r(e),"onUpdate:modelValue":m[1]||(m[1]=i=>C(e)?e.value=i:null),hint:r(t)?"Click the icon to save":"Click the icon to edit",placeholder:"Select Your State",items:p,readonly:!r(t),label:`State — ${r(t)?"Editable":"Readonly"}`,"persistent-hint":"","prepend-icon":"tabler-building","menu-props":{maxHeight:"200px"}},{append:l(()=>[a(G,{mode:"out-in"},{default:l(()=>[(v(),h(T,{key:`icon-${r(t)}`,color:r(t)?"success":"info",icon:r(t)?"tabler-checks":"tabler-edit-circle",onClick:m[0]||(m[0]=i=>t.value=!r(t))},null,8,["color","icon"]))]),_:1})]),_:1},8,["modelValue","hint","readonly","label"])}}}),Y=g({__name:"DemoAutocompleteAsyncItems",setup(f){const t=S(!1),e=S(),p=S(null),o=["Alabama","Alaska","American Samoa","Arizona","Arkansas","California","Colorado","Connecticut","Delaware","District of Columbia","Federated States of Micronesia","Florida","Georgia","Guam","Hawaii","Idaho","Illinois","Indiana","Iowa","Kansas","Kentucky","Louisiana","Maine","Marshall Islands","Maryland","Massachusetts","Michigan","Minnesota","Mississippi","Missouri","Montana","Nebraska","Nevada","New Hampshire","New Jersey","New Mexico","New York","North Carolina","North Dakota","Northern Mariana Islands","Ohio","Oklahoma","Oregon","Palau","Pennsylvania","Puerto Rico","Rhode Island","South Carolina","South Dakota","Tennessee","Texas","Utah","Vermont","Virgin Island","Virginia","Washington","West Virginia","Wisconsin","Wyoming"],m=S(o),c=i=>{t.value=!0,setTimeout(()=>{m.value=o.filter(d=>(d||"").toLowerCase().includes((i||"").toLowerCase())),t.value=!1},500)};return I(e,i=>{i&&i!==p.value&&c(i)}),(i,d)=>(v(),h(A,{modelValue:r(p),"onUpdate:modelValue":d[0]||(d[0]=_=>C(p)?p.value=_:null),search:r(e),"onUpdate:search":d[1]||(d[1]=_=>C(e)?e.value=_:null),loading:r(t),items:r(m),placeholder:"Search for a state",label:"What state are you from?",variant:"underlined","menu-props":{maxHeight:"200px"}},null,8,["modelValue","search","loading","items"]))}}),j=g({__name:"DemoAutocompleteSlots",setup(f){const t=S(["Sandra Adams","Britta Holt"]),e=[{name:"Sandra Adams",group:"Group 1",avatar:F},{name:"Ali Connors",group:"Group 1",avatar:D},{name:"Trevor Hansen",group:"Group 1",avatar:W},{name:"Tucker Smith",group:"Group 1",avatar:R},{name:"Britta Holt",group:"Group 2",avatar:O},{name:"Jane Smith ",group:"Group 2",avatar:H},{name:"John Smith",group:"Group 2",avatar:L},{name:"Sandra Williams",group:"Group 2",avatar:U}];return(p,o)=>{const m=b;return v(),h(m,{modelValue:r(t),"onUpdate:modelValue":o[0]||(o[0]=c=>C(t)?t.value=c:null),chips:"","closable-chips":"",multiple:"",items:e,"item-title":"name","item-value":"name",placeholder:"Select User",label:"Select"},{chip:l(({props:c,item:i})=>[a(P,w(c,{"prepend-avatar":i.raw.avatar,text:i.raw.name}),null,16,["prepend-avatar","text"])]),item:l(({props:c,item:i})=>{var d,_,y;return[a(E,w(c,{"prepend-avatar":(d=i==null?void 0:i.raw)==null?void 0:d.avatar,title:(_=i==null?void 0:i.raw)==null?void 0:_.name,subtitle:(y=i==null?void 0:i.raw)==null?void 0:y.group}),null,16,["prepend-avatar","title","subtitle"])]}),_:1},8,["modelValue"])}}}),B=g({__name:"DemoAutocompleteCustomFilter",setup(f){const t=[{name:"Florida",abbr:"FL",id:1},{name:"Georgia",abbr:"GA",id:2},{name:"Nebraska",abbr:"NE",id:3},{name:"California",abbr:"CA",id:4},{name:"New York",abbr:"NY",id:5}];function e(p,o,m){const c=m.raw.name.toLowerCase(),i=m.raw.abbr.toLowerCase(),d=o.toLowerCase();return c.includes(d)||i.includes(d)}return(p,o)=>{const m=b;return v(),h(m,{label:"States",items:t,"custom-filter":e,"item-title":"name","item-value":"abbr",placeholder:"Select State"})}}}),J=g({__name:"DemoAutocompleteChips",setup(f){const t=["California","Colorado","Florida","Georgia","Texas","Wyoming"];return(e,p)=>{const o=b;return v(),h(o,{label:"States",items:t,placeholder:"Select State",chips:"",multiple:"","closable-chips":""})}}}),K=g({__name:"DemoAutocompleteClearable",setup(f){const t=["California","Colorado","Florida","Georgia","Texas","Wyoming"];return(e,p)=>{const o=b;return v(),h(o,{label:"States",items:t,multiple:"",placeholder:"Select State",clearable:""})}}}),X=g({__name:"DemoAutocompleteMultiple",setup(f){const t=["California","Colorado","Florida","Georgia","Texas","Wyoming"];return(e,p)=>{const o=b;return v(),h(o,{label:"States",items:t,placeholder:"Select State",multiple:"",eager:""})}}}),Z=g({__name:"DemoAutocompleteVariant",setup(f){const t=["California","Colorado","Florida","Georgia","Texas","Wyoming"];return(e,p)=>(v(),h(V,null,{default:l(()=>[a(u,{cols:"12",md:"6"},{default:l(()=>[a(A,{variant:"solo",label:"Solo",items:t,placeholder:"Select State"})]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(A,{variant:"outlined",label:"Outlined",placeholder:"Select State",items:t})]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(A,{variant:"underlined",label:"Underlined",placeholder:"Select State",items:t})]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(A,{variant:"filled",label:"Filled",placeholder:"Select State",items:t})]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(A,{variant:"plain",label:"Plain",placeholder:"Select State",items:t})]),_:1})]),_:1}))}}),Q=g({__name:"DemoAutocompleteDensity",setup(f){const t=S("Florida"),e=["California","Colorado","Florida","Georgia","Texas","Wyoming"];return(p,o)=>{const m=b;return v(),h(m,{modelValue:r(t),"onUpdate:modelValue":o[0]||(o[0]=c=>C(t)?t.value=c:null),label:"States",density:"compact",placeholder:"Select State",items:e},null,8,["modelValue"])}}}),ee=g({__name:"DemoAutocompleteBasic",setup(f){const t=["California","Colorado","Florida","Georgia","Texas","Wyoming"];return(e,p)=>{const o=b;return v(),h(o,{label:"States",items:t,placeholder:"Select State"})}}}),ae={ts:`<script setup lang="ts">
const loading = ref(false)
const search = ref()
const select = ref(null)

const states = [
  'Alabama',
  'Alaska',
  'American Samoa',
  'Arizona',
  'Arkansas',
  'California',
  'Colorado',
  'Connecticut',
  'Delaware',
  'District of Columbia',
  'Federated States of Micronesia',
  'Florida',
  'Georgia',
  'Guam',
  'Hawaii',
  'Idaho',
  'Illinois',
  'Indiana',
  'Iowa',
  'Kansas',
  'Kentucky',
  'Louisiana',
  'Maine',
  'Marshall Islands',
  'Maryland',
  'Massachusetts',
  'Michigan',
  'Minnesota',
  'Mississippi',
  'Missouri',
  'Montana',
  'Nebraska',
  'Nevada',
  'New Hampshire',
  'New Jersey',
  'New Mexico',
  'New York',
  'North Carolina',
  'North Dakota',
  'Northern Mariana Islands',
  'Ohio',
  'Oklahoma',
  'Oregon',
  'Palau',
  'Pennsylvania',
  'Puerto Rico',
  'Rhode Island',
  'South Carolina',
  'South Dakota',
  'Tennessee',
  'Texas',
  'Utah',
  'Vermont',
  'Virgin Island',
  'Virginia',
  'Washington',
  'West Virginia',
  'Wisconsin',
  'Wyoming',
]

const items = ref(states)

const querySelections = (query: string) => {
  loading.value = true

  // Simulated ajax query
  setTimeout(() => {
    items.value = states.filter(state => (state || '').toLowerCase().includes((query || '').toLowerCase()))
    loading.value = false
  }, 500)
}

watch(search, query => {
  query && query !== select.value && querySelections(query)
})
<\/script>

<template>
  <VAutocomplete
    v-model="select"
    v-model:search="search"
    :loading="loading"
    :items="items"
    placeholder="Search for a state"
    label="What state are you from?"
    variant="underlined"
    :menu-props="{ maxHeight: '200px' }"
  />
</template>
`,js:`<script setup>
const loading = ref(false)
const search = ref()
const select = ref(null)

const states = [
  'Alabama',
  'Alaska',
  'American Samoa',
  'Arizona',
  'Arkansas',
  'California',
  'Colorado',
  'Connecticut',
  'Delaware',
  'District of Columbia',
  'Federated States of Micronesia',
  'Florida',
  'Georgia',
  'Guam',
  'Hawaii',
  'Idaho',
  'Illinois',
  'Indiana',
  'Iowa',
  'Kansas',
  'Kentucky',
  'Louisiana',
  'Maine',
  'Marshall Islands',
  'Maryland',
  'Massachusetts',
  'Michigan',
  'Minnesota',
  'Mississippi',
  'Missouri',
  'Montana',
  'Nebraska',
  'Nevada',
  'New Hampshire',
  'New Jersey',
  'New Mexico',
  'New York',
  'North Carolina',
  'North Dakota',
  'Northern Mariana Islands',
  'Ohio',
  'Oklahoma',
  'Oregon',
  'Palau',
  'Pennsylvania',
  'Puerto Rico',
  'Rhode Island',
  'South Carolina',
  'South Dakota',
  'Tennessee',
  'Texas',
  'Utah',
  'Vermont',
  'Virgin Island',
  'Virginia',
  'Washington',
  'West Virginia',
  'Wisconsin',
  'Wyoming',
]

const items = ref(states)

const querySelections = query => {
  loading.value = true

  // Simulated ajax query
  setTimeout(() => {
    items.value = states.filter(state => (state || '').toLowerCase().includes((query || '').toLowerCase()))
    loading.value = false
  }, 500)
}

watch(search, query => {
  query && query !== select.value && querySelections(query)
})
<\/script>

<template>
  <VAutocomplete
    v-model="select"
    v-model:search="search"
    :loading="loading"
    :items="items"
    placeholder="Search for a state"
    label="What state are you from?"
    variant="underlined"
    :menu-props="{ maxHeight: '200px' }"
  />
</template>
`},te={ts:`<script setup lang="ts">
const items = ['California', 'Colorado', 'Florida', 'Georgia', 'Texas', 'Wyoming']
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="items"
    placeholder="Select State"
  />
</template>
`,js:`<script setup>
const items = [
  'California',
  'Colorado',
  'Florida',
  'Georgia',
  'Texas',
  'Wyoming',
]
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="items"
    placeholder="Select State"
  />
</template>
`},oe={ts:`<script setup lang="ts">
const items = ['California', 'Colorado', 'Florida', 'Georgia', 'Texas', 'Wyoming']
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="items"
    placeholder="Select State"
    chips
    multiple
    closable-chips
  />
</template>
`,js:`<script setup>
const items = [
  'California',
  'Colorado',
  'Florida',
  'Georgia',
  'Texas',
  'Wyoming',
]
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="items"
    placeholder="Select State"
    chips
    multiple
    closable-chips
  />
</template>
`},le={ts:`<script setup lang="ts">
const items = ['California', 'Colorado', 'Florida', 'Georgia', 'Texas', 'Wyoming']
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="items"
    multiple
    placeholder="Select State"
    clearable
  />
</template>
`,js:`<script setup>
const items = [
  'California',
  'Colorado',
  'Florida',
  'Georgia',
  'Texas',
  'Wyoming',
]
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="items"
    multiple
    placeholder="Select State"
    clearable
  />
</template>
`},ie={ts:`<script setup lang="ts">
const states = [
  { name: 'Florida', abbr: 'FL', id: 1 },
  { name: 'Georgia', abbr: 'GA', id: 2 },
  { name: 'Nebraska', abbr: 'NE', id: 3 },
  { name: 'California', abbr: 'CA', id: 4 },
  { name: 'New York', abbr: 'NY', id: 5 },
]

function customFilter(itemTitle: any, queryText: any, item: any) {
  const textOne = item.raw.name.toLowerCase()
  const textTwo = item.raw.abbr.toLowerCase()
  const searchText = queryText.toLowerCase()

  return textOne.includes(searchText) || textTwo.includes(searchText)
}
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="states"
    :custom-filter="customFilter"
    item-title="name"
    item-value="abbr"
    placeholder="Select State"
  />
</template>
`,js:`<script setup>
const states = [
  {
    name: 'Florida',
    abbr: 'FL',
    id: 1,
  },
  {
    name: 'Georgia',
    abbr: 'GA',
    id: 2,
  },
  {
    name: 'Nebraska',
    abbr: 'NE',
    id: 3,
  },
  {
    name: 'California',
    abbr: 'CA',
    id: 4,
  },
  {
    name: 'New York',
    abbr: 'NY',
    id: 5,
  },
]

function customFilter(itemTitle, queryText, item) {
  const textOne = item.raw.name.toLowerCase()
  const textTwo = item.raw.abbr.toLowerCase()
  const searchText = queryText.toLowerCase()
  
  return textOne.includes(searchText) || textTwo.includes(searchText)
}
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="states"
    :custom-filter="customFilter"
    item-title="name"
    item-value="abbr"
    placeholder="Select State"
  />
</template>
`},se={ts:`<script setup lang="ts">
const select = ref('Florida')
const items = ['California', 'Colorado', 'Florida', 'Georgia', 'Texas', 'Wyoming']
<\/script>

<template>
  <AppAutocomplete
    v-model="select"
    label="States"
    density="compact"
    placeholder="Select State"
    :items="items"
  />
</template>
`,js:`<script setup>
const select = ref('Florida')

const items = [
  'California',
  'Colorado',
  'Florida',
  'Georgia',
  'Texas',
  'Wyoming',
]
<\/script>

<template>
  <AppAutocomplete
    v-model="select"
    label="States"
    density="compact"
    placeholder="Select State"
    :items="items"
  />
</template>
`},re={ts:`<script setup lang="ts">
const items = ['California', 'Colorado', 'Florida', 'Georgia', 'Texas', 'Wyoming']
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="items"
    placeholder="Select State"
    multiple
    eager
  />
</template>
`,js:`<script setup>
const items = [
  'California',
  'Colorado',
  'Florida',
  'Georgia',
  'Texas',
  'Wyoming',
]
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="items"
    placeholder="Select State"
    multiple
    eager
  />
</template>
`},ne={ts:`<script setup lang="ts">
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar3 from '@images/avatars/avatar-3.png'
import avatar4 from '@images/avatars/avatar-4.png'
import avatar5 from '@images/avatars/avatar-5.png'
import avatar6 from '@images/avatars/avatar-6.png'
import avatar7 from '@images/avatars/avatar-7.png'
import avatar8 from '@images/avatars/avatar-8.png'

const friends = ref(['Sandra Adams', 'Britta Holt'])

const people = [
  { name: 'Sandra Adams', group: 'Group 1', avatar: avatar1 },
  { name: 'Ali Connors', group: 'Group 1', avatar: avatar2 },
  { name: 'Trevor Hansen', group: 'Group 1', avatar: avatar3 },
  { name: 'Tucker Smith', group: 'Group 1', avatar: avatar4 },
  { name: 'Britta Holt', group: 'Group 2', avatar: avatar5 },
  { name: 'Jane Smith ', group: 'Group 2', avatar: avatar6 },
  { name: 'John Smith', group: 'Group 2', avatar: avatar7 },
  { name: 'Sandra Williams', group: 'Group 2', avatar: avatar8 },
]
<\/script>

<template>
  <AppAutocomplete
    v-model="friends"
    chips
    closable-chips
    multiple
    :items="people"
    item-title="name"
    item-value="name"
    placeholder="Select User"
    label="Select"
  >
    <template #chip="{ props, item }">
      <VChip
        v-bind="props"
        :prepend-avatar="item.raw.avatar"
        :text="item.raw.name"
      />
    </template>

    <template #item="{ props, item }">
      <VListItem
        v-bind="props"
        :prepend-avatar="item?.raw?.avatar"
        :title="item?.raw?.name"
        :subtitle="item?.raw?.group"
      />
    </template>
  </AppAutocomplete>
</template>
`,js:`<script setup>
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar3 from '@images/avatars/avatar-3.png'
import avatar4 from '@images/avatars/avatar-4.png'
import avatar5 from '@images/avatars/avatar-5.png'
import avatar6 from '@images/avatars/avatar-6.png'
import avatar7 from '@images/avatars/avatar-7.png'
import avatar8 from '@images/avatars/avatar-8.png'

const friends = ref([
  'Sandra Adams',
  'Britta Holt',
])

const people = [
  {
    name: 'Sandra Adams',
    group: 'Group 1',
    avatar: avatar1,
  },
  {
    name: 'Ali Connors',
    group: 'Group 1',
    avatar: avatar2,
  },
  {
    name: 'Trevor Hansen',
    group: 'Group 1',
    avatar: avatar3,
  },
  {
    name: 'Tucker Smith',
    group: 'Group 1',
    avatar: avatar4,
  },
  {
    name: 'Britta Holt',
    group: 'Group 2',
    avatar: avatar5,
  },
  {
    name: 'Jane Smith ',
    group: 'Group 2',
    avatar: avatar6,
  },
  {
    name: 'John Smith',
    group: 'Group 2',
    avatar: avatar7,
  },
  {
    name: 'Sandra Williams',
    group: 'Group 2',
    avatar: avatar8,
  },
]
<\/script>

<template>
  <AppAutocomplete
    v-model="friends"
    chips
    closable-chips
    multiple
    :items="people"
    item-title="name"
    item-value="name"
    placeholder="Select User"
    label="Select"
  >
    <template #chip="{ props, item }">
      <VChip
        v-bind="props"
        :prepend-avatar="item.raw.avatar"
        :text="item.raw.name"
      />
    </template>

    <template #item="{ props, item }">
      <VListItem
        v-bind="props"
        :prepend-avatar="item?.raw?.avatar"
        :title="item?.raw?.name"
        :subtitle="item?.raw?.group"
      />
    </template>
  </AppAutocomplete>
</template>
`},me={ts:`<script setup lang="ts">
const isEditing = ref(false)
const selectedState = ref(null)

const states = [
  'Alabama',
  'Alaska',
  'American Samoa',
  'Arizona',
  'Arkansas',
  'California',
  'Colorado',
  'Connecticut',
  'Delaware',
  'District of Columbia',
  'Federated States of Micronesia',
  'Florida',
  'Georgia',
  'Guam',
  'Hawaii',
  'Idaho',
  'Illinois',
  'Indiana',
  'Iowa',
  'Kansas',
  'Kentucky',
  'Louisiana',
  'Maine',
  'Marshall Islands',
  'Maryland',
  'Massachusetts',
  'Michigan',
  'Minnesota',
  'Mississippi',
  'Missouri',
  'Montana',
  'Nebraska',
  'Nevada',
  'New Hampshire',
  'New Jersey',
  'New Mexico',
  'New York',
  'North Carolina',
  'North Dakota',
  'Northern Mariana Islands',
  'Ohio',
  'Oklahoma',
  'Oregon',
  'Palau',
  'Pennsylvania',
  'Puerto Rico',
  'Rhode Island',
  'South Carolina',
  'South Dakota',
  'Tennessee',
  'Texas',
  'Utah',
  'Vermont',
  'Virgin Island',
  'Virginia',
  'Washington',
  'West Virginia',
  'Wisconsin',
  'Wyoming',
]
<\/script>

<template>
  <AppAutocomplete
    v-model="selectedState"
    :hint="!isEditing ? 'Click the icon to edit' : 'Click the icon to save'"
    placeholder="Select Your State"
    :items="states"
    :readonly="!isEditing"
    :label="\`State — \${isEditing ? 'Editable' : 'Readonly'}\`"
    persistent-hint
    prepend-icon="tabler-building"
    :menu-props="{ maxHeight: '200px' }"
  >
    <template #append>
      <VSlideXReverseTransition mode="out-in">
        <VIcon
          :key="\`icon-\${isEditing}\`"
          :color="isEditing ? 'success' : 'info'"
          :icon="isEditing ? 'tabler-checks' : 'tabler-edit-circle'"
          @click="isEditing = !isEditing"
        />
      </VSlideXReverseTransition>
    </template>
  </AppAutocomplete>
</template>
`,js:`<script setup>
const isEditing = ref(false)
const selectedState = ref(null)

const states = [
  'Alabama',
  'Alaska',
  'American Samoa',
  'Arizona',
  'Arkansas',
  'California',
  'Colorado',
  'Connecticut',
  'Delaware',
  'District of Columbia',
  'Federated States of Micronesia',
  'Florida',
  'Georgia',
  'Guam',
  'Hawaii',
  'Idaho',
  'Illinois',
  'Indiana',
  'Iowa',
  'Kansas',
  'Kentucky',
  'Louisiana',
  'Maine',
  'Marshall Islands',
  'Maryland',
  'Massachusetts',
  'Michigan',
  'Minnesota',
  'Mississippi',
  'Missouri',
  'Montana',
  'Nebraska',
  'Nevada',
  'New Hampshire',
  'New Jersey',
  'New Mexico',
  'New York',
  'North Carolina',
  'North Dakota',
  'Northern Mariana Islands',
  'Ohio',
  'Oklahoma',
  'Oregon',
  'Palau',
  'Pennsylvania',
  'Puerto Rico',
  'Rhode Island',
  'South Carolina',
  'South Dakota',
  'Tennessee',
  'Texas',
  'Utah',
  'Vermont',
  'Virgin Island',
  'Virginia',
  'Washington',
  'West Virginia',
  'Wisconsin',
  'Wyoming',
]
<\/script>

<template>
  <AppAutocomplete
    v-model="selectedState"
    :hint="!isEditing ? 'Click the icon to edit' : 'Click the icon to save'"
    placeholder="Select Your State"
    :items="states"
    :readonly="!isEditing"
    :label="\`State — \${isEditing ? 'Editable' : 'Readonly'}\`"
    persistent-hint
    prepend-icon="tabler-building"
    :menu-props="{ maxHeight: '200px' }"
  >
    <template #append>
      <VSlideXReverseTransition mode="out-in">
        <VIcon
          :key="\`icon-\${isEditing}\`"
          :color="isEditing ? 'success' : 'info'"
          :icon="isEditing ? 'tabler-checks' : 'tabler-edit-circle'"
          @click="isEditing = !isEditing"
        />
      </VSlideXReverseTransition>
    </template>
  </AppAutocomplete>
</template>
`},pe={ts:`<script setup lang="ts">
const items = ['foo', 'bar', 'fizz', 'buzz']
const values = ref(['foo'])
const nameRules = [(v: string) => !!v.length || 'Select at least one option.']
<\/script>

<template>
  <AppAutocomplete
    v-model="values"
    :items="items"
    :rules="nameRules"
    placeholder="Select Option"
    multiple
  />
</template>
`,js:`<script setup>
const items = [
  'foo',
  'bar',
  'fizz',
  'buzz',
]

const values = ref(['foo'])
const nameRules = [v => !!v.length || 'Select at least one option.']
<\/script>

<template>
  <AppAutocomplete
    v-model="values"
    :items="items"
    :rules="nameRules"
    placeholder="Select Option"
    multiple
  />
</template>
`},ce={ts:`<script setup lang="ts">
const items = ['California', 'Colorado', 'Florida', 'Georgia', 'Texas', 'Wyoming']
<\/script>

<template>
  <VRow>
    <VCol
      cols="12"
      md="6"
    >
      <!-- 👉 solo variant  -->
      <VAutocomplete
        variant="solo"
        label="Solo"
        :items="items"
        placeholder="Select State"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <!-- 👉 outlined variant -->
      <VAutocomplete
        variant="outlined"
        label="Outlined"
        placeholder="Select State"
        :items="items"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <!-- 👉 underlined variant -->
      <VAutocomplete
        variant="underlined"
        label="Underlined"
        placeholder="Select State"
        :items="items"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <!-- 👉 filled variant  -->
      <VAutocomplete
        variant="filled"
        label="Filled"
        placeholder="Select State"
        :items="items"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <!--  👉 plain variant -->
      <VAutocomplete
        variant="plain"
        label="Plain"
        placeholder="Select State"
        :items="items"
      />
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const items = [
  'California',
  'Colorado',
  'Florida',
  'Georgia',
  'Texas',
  'Wyoming',
]
<\/script>

<template>
  <VRow>
    <VCol
      cols="12"
      md="6"
    >
      <!-- 👉 solo variant  -->
      <VAutocomplete
        variant="solo"
        label="Solo"
        :items="items"
        placeholder="Select State"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <!-- 👉 outlined variant -->
      <VAutocomplete
        variant="outlined"
        label="Outlined"
        placeholder="Select State"
        :items="items"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <!-- 👉 underlined variant -->
      <VAutocomplete
        variant="underlined"
        label="Underlined"
        placeholder="Select State"
        :items="items"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <!-- 👉 filled variant  -->
      <VAutocomplete
        variant="filled"
        label="Filled"
        placeholder="Select State"
        :items="items"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <!--  👉 plain variant -->
      <VAutocomplete
        variant="plain"
        label="Plain"
        placeholder="Select State"
        :items="items"
      />
    </VCol>
  </VRow>
</template>
`},aa=g({__name:"autocomplete",setup(f){return(t,e)=>{const p=ee,o=q,m=Q,c=Z,i=X,d=K,_=J,y=B,x=j,k=Y,M=z,N=$;return v(),h(V,{class:"match-height"},{default:l(()=>[a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"Basic",code:r(te)},{default:l(()=>[e[0]||(e[0]=s("p",null,[n(" The "),s("code",null," v-autocomplete "),n(" component offers simple and flexible type-ahead functionality. This is useful when searching large sets of data or even dynamically fetching information from an API. ")],-1)),a(p)]),_:1,__:[0]},8,["code"])]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"Density",code:r(se)},{default:l(()=>[e[1]||(e[1]=s("p",null,[n(" You can use "),s("code",null," density "),n(" prop to adjusts vertical spacing within the component. Available options are: "),s("code",null,"default"),n(", "),s("code",null,"comfortable"),n(", and "),s("code",null,"compact"),n(". ")],-1)),a(m)]),_:1,__:[1]},8,["code"])]),_:1}),a(u,{cols:"12",md:"12"},{default:l(()=>[a(o,{title:"Variant",code:r(ce)},{default:l(()=>[e[2]||(e[2]=s("p",null,[n("Use "),s("code",null,"Solo"),n(", "),s("code",null,"Outlined"),n(", "),s("code",null,"Underlined"),n(", "),s("code",null,"Filled"),n(" and "),s("code",null,"Plain"),n(" options of "),s("code",null,"variant"),n(" prop to change the look of Autocomplete. ")],-1)),a(c)]),_:1,__:[2]},8,["code"])]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"Multiple",code:r(re)},{default:l(()=>[e[3]||(e[3]=s("p",null,[n("Use "),s("code",null,"multiple"),n(" prop to select multiple. Accepts array for value")],-1)),a(i)]),_:1,__:[3]},8,["code"])]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"Clearable",code:r(le)},{default:l(()=>[e[4]||(e[4]=s("p",null,[n("Use "),s("code",null,"clearable"),n(" prop to add input clear functionality.")],-1)),a(d)]),_:1,__:[4]},8,["code"])]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"Chips",code:r(oe)},{default:l(()=>[e[5]||(e[5]=s("p",null,[n("Use "),s("code",null," chips "),n(" prop to use chips in select.")],-1)),a(_)]),_:1,__:[5]},8,["code"])]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"Custom-Filter",code:r(ie)},{default:l(()=>[e[6]||(e[6]=s("p",null,[n("The "),s("code",null," custom-filter "),n(" prop can be used to filter each individual item with custom logic.In example we will filter state based on their name and abbreviations ")],-1)),a(y)]),_:1,__:[6]},8,["code"])]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"Slots",code:r(ne)},{default:l(()=>[e[7]||(e[7]=s("p",null,"With the power of slots, you can customize the visual output of the select. In this example we add a profile picture for both the chips and list items using their props. ",-1)),a(x)]),_:1,__:[7]},8,["code"])]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"Async items",code:r(ae)},{default:l(()=>[e[8]||(e[8]=s("p",null,"Sometimes you need to load data externally based upon a search query. ",-1)),a(k)]),_:1,__:[8]},8,["code"])]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"State Selector",code:r(me)},{default:l(()=>[e[9]||(e[9]=s("p",null,"Using a combination of v-autocomplete slots and transitions, you can create a stylish toggle able autocomplete field such as below state selector.",-1)),a(M)]),_:1,__:[9]},8,["code"])]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"validation",code:r(pe)},{default:l(()=>[e[10]||(e[10]=s("p",null,[n("Use "),s("code",null,"rules"),n(" prop to validate autocomplete. Accepts a mixed array of types function, boolean and string. Functions pass an input value as an argument and must return either true / false or a string containing an error message.")],-1)),a(N)]),_:1,__:[10]},8,["code"])]),_:1})]),_:1})}}});export{aa as default};
