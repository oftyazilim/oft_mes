import{dq as d,dr as K,ds as X,b0 as v,d as $,aU as T,dt as q,du as z}from"./main-CcIa1HPl.js";import{e as p,d as G}from"./dom_component-DOEaAjwM.js";const Lt=p.on,E=p.one,Wt=p.off,kt=p.trigger;p.Event;const Y=p.triggerHandler;/*!
 * devextreme-vue
 * Version: 25.1.5
 * Build date: Wed Sep 03 2025
 *
 * Copyright (c) 2012 - 2025 Developer Express Inc. ALL RIGHTS RESERVED
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file in the root of the project for details.
 *
 * https://github.com/DevExpress/devextreme-vue
 */function x(t,e){for(const n in t)if(t[n]===e)return n;return e}function J(t){return t[0].toUpperCase()+t.substr(1)}function Q(t){return t[0].toLowerCase()+t.substr(1)}function V(t){return Q(t.split("-").map(e=>J(e)).join(""))}function m(t){return t instanceof Date?t.getTime():t}function P(t,e){return m(t)===m(e)?!0:Array.isArray(t)&&Array.isArray(e)?t.length===0&&e.length===0:!1}function Z(t,e){Array.prototype.slice.call(t.childNodes).forEach(e)}function tt(t,e){const n=Object.keys(t);if(n.length!==Object.keys(e).length)return!1;for(const s of n)if(!e.hasOwnProperty(s))return!1;return!0}function et(t,e){let n=t;return e.split(".").forEach(s=>{var o;const i=D(s);n&&(n=i.isCollection?(o=n[i.name])==null?void 0:o[i.index]:n[i.name])}),n}function D(t){const e=t.split("[");return e.length===1?{isCollection:!1,name:t,fullName:t}:{isCollection:!0,name:e[0],fullName:t,index:Number(e[1].slice(0,-1))}}/*!
 * devextreme-vue
 * Version: 25.1.5
 * Build date: Wed Sep 03 2025
 *
 * Copyright (c) 2012 - 2025 Developer Express Inc. ALL RIGHTS RESERVED
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file in the root of the project for details.
 *
 * https://github.com/DevExpress/devextreme-vue
 */function nt(t,e,n){!t||t.length===0||_(t,e,n)}function h(t){const{patchFlag:e}=t;return e===d.KEYED_FRAGMENT||e===d.UNKEYED_FRAGMENT||e===d.STABLE_FRAGMENT||e===d.BAIL}function _(t,e,n){t.forEach(s=>{if(h(s)&&Array.isArray(s.children)&&_(s.children,e,n),h(s)||e.push(s),!s)return;const i=st(s);if(!(i!=null&&i.$_optionName))return;const o=it(s),r={...i.$_predefinedProps,...w(s.props||{})},a=n.createNested(i.$_optionName,r,i.$_isCollectionItem,i.$_expectedChildren);s.$_config=a,s.$_innerChanges={},o&&_(o,e,a)})}/*!
 * devextreme-vue
 * Version: 25.1.5
 * Build date: Wed Sep 03 2025
 *
 * Copyright (c) 2012 - 2025 Developer Express Inc. ALL RIGHTS RESERVED
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file in the root of the project for details.
 *
 * https://github.com/DevExpress/devextreme-vue
 */const u="modelValue";function y(t){if(!gt(t)||!t.$_config)return[];const e=t.$.subTree&&t.$.subTree.children;return Array.isArray(e)?e.filter(n=>{if(!h(n))return n}):[]}function st(t){return L(t)}function w(t){const e={};for(const n in t)t.hasOwnProperty(n)&&(e[V(n)]=t[n]);return e}function it(t){var e;return(e=t.children)!=null&&e.default?F(t.children.default()):[]}function M(t){if(!(!t.children||t.children==="object"||!t.children.default))return ht(t.children.default())?t.children.default:void 0}function ot(t){return M(t)}function j(t){return t.$slots}function rt(t){const e=j(t);return e.default?e.default():[]}function at(t,e,n){const s=K(t);return s.provide("eventBus",e.eventBus),ft(s,e),s.mount(n)}function N(t){const e=t.$.vnode.props||{};return w(e)}function I(t){return t.$?t.$.vnode:t}function ct(t){return t.$.vnode.type}function lt(t){return t[u]}function ut(t){const e=`update:${u}`;t.model.prop=u,t.model.event=e,t.props.modelValue={},t.emits={...t.emits,[`${e}`]:null}}function pt(t,e){for(const n in e)!t.hasOwnProperty(n)&&e.hasOwnProperty(n)&&(t[n]=e[n])}function ft(t,e){t._context.components=Object.assign(e.$.appContext.components,t._context.components),Object.setPrototypeOf(t._context.provides,Object.getPrototypeOf(e.$.provides)),Object.assign(t._context.provides,e.$.appContext.provides),t._context.config=e.$.appContext.config,t._context.directives=e.$.appContext.directives,t._context.mixins=e.$.appContext.mixins,pt(t._context.app,e.$.appContext.app)}function F(t){return t.filter(e=>{if(h(e))return F(e.children||[]);const n=e.type;if(n&&typeof n=="object"&&n.$_optionName)return delete e.$_config,delete e.$_innerChanges,e})}function ht(t){let e=!1;return t.forEach(n=>{!mt(n)&&!h(n)&&!dt(n)&&(e=!0)}),e}function dt(t){return t.type===X||t.type.toString()==="Symbol()"&&!t.children}function mt(t){return t.type&&typeof t.type=="object"&&t.type.$_optionName}function L(t){return t.type}function gt(t){return t.$.vnode&&t.$.vnode.children&&t.$.vnode.children.default}/*!
 * devextreme-vue
 * Version: 25.1.5
 * Build date: Wed Sep 03 2025
 *
 * Copyright (c) 2012 - 2025 Developer Express Inc. ALL RIGHTS RESERVED
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file in the root of the project for details.
 *
 * https://github.com/DevExpress/devextreme-vue
 */let _t={deepWatch:!1};function Ct(t){return _t[t]}/*!
 * devextreme-vue
 * Version: 25.1.5
 * Build date: Wed Sep 03 2025
 *
 * Copyright (c) 2012 - 2025 Developer Express Inc. ALL RIGHTS RESERVED
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file in the root of the project for details.
 *
 * https://github.com/DevExpress/devextreme-vue
 */class O{constructor(e,n,s,i,o,r,a){this._updateFunc=e,this._name=n,this._initialValues=s||{},this._nestedConfigurations=[],this._isCollectionItem=!!o,this._collectionItemIndex=r,this._expectedChildren=i||{},this._ownerConfig=a,this._componentChanges=[],this.updateValue=this.updateValue.bind(this)}get name(){return this._name}get fullName(){return this._name&&this._isCollectionItem?`${this._name}[${this._collectionItemIndex}]`:this._name}get componentsCountChanged(){return this._componentChanges}cleanComponentsCountChanged(){this._componentChanges=[]}get fullPath(){var e;return(e=this._ownerConfig)!=null&&e.fullPath?`${this._ownerConfig.fullPath}.${this.fullName}`:this.fullName}get ownerConfig(){return this._ownerConfig}get options(){return this._options}get initialValues(){return this._initialValues}get expectedChildren(){return this._expectedChildren}get nested(){return this._nestedConfigurations}get prevNestedOptions(){return this._prevNestedConfigOptions}get collectionItemIndex(){return this._collectionItemIndex}get isCollectionItem(){return this._isCollectionItem}get updateFunc(){return this._updateFunc}init(e){this._options=e||[]}set emitOptionChanged(e){this._emitOptionChanged=e}setPrevNestedOptions(e){this._prevNestedConfigOptions=e}onOptionChanged(e){P(e.value,e.previousValue)||this._onOptionChanged(e.fullName.split("."),e)}cleanNested(){this._nestedConfigurations=[]}createNested(e,n,s,i){const o=this._expectedChildren[e];let r=e,a=s;o&&(a=o.isCollectionItem,o.optionName&&(r=o.optionName));let l=-1;a&&r&&(l=this._nestedConfigurations.filter(f=>f._name&&f._name===r).length);const c=new O(this._updateFunc,r,n,i,a,l,this);return this._nestedConfigurations.push(c),c}updateValue(e,n){const s=[this.fullPath,e].filter(i=>i).join(".");this._updateFunc(s,n)}getNestedOptionValues(){const e={};return this._nestedConfigurations.forEach(n=>{if(!n._name)return;const s={...n.initialValues,...n.getNestedOptionValues()};if(s)if(!n._isCollectionItem)e[n._name]=s;else{let i=e[n._name];(!i||!Array.isArray(i))&&(i=[],e[n._name]=i),i.push(s)}}),e}getOptionsToWatch(){const e={};return this._nestedConfigurations.forEach(n=>n._name&&(e[n._name]=!0)),this._options.filter(n=>!e[n])}_onOptionChanged(e,n){if(e.length===0)return;const s=D(e[0]);if(s.isCollection||e.length>1){const i=this._getNestedConfig(s.fullName);if(i){i._onOptionChanged(e.slice(1),n);return}this._tryEmitOptionChanged(s.name,n.component.option(this.fullPath?`${this.fullPath}.${s.name}`:s.name))}else this._tryEmitOptionChanged(s.name,n.value)}_getNestedConfig(e){for(const n of this._nestedConfigurations)if(n.fullName===e)return n}_tryEmitOptionChanged(e,n){this._emitOptionChanged&&this._emitOptionChanged(e,n)}}function W(t,e,n){const s=t&&t.getOptionsToWatch();s&&s.forEach(i=>{e.$watch(i,o=>{const r=v(o);(!n.hasOwnProperty(i)||n[i]!==r)&&t.updateValue(i,o),delete n[i]},{deep:Ct("deepWatch")})})}function $t(t,e){const{props:n}=t.$options;return n==null?void 0:n.hasOwnProperty(e)}function yt(t,e,n){var s;return t.model&&e.hasOwnProperty(u)&&((s=n==null?void 0:n.props)==null?void 0:s.hasOwnProperty(u))}function k(t,e,n){t.emitOptionChanged=(s,i)=>{var c;const o=e.$props,r=(c=e==null?void 0:e.$)==null?void 0:c.vnode,a=s==="value"&&yt(e.$options,o,r)?u:s,l=`update:${a}`;$t(e,s)&&!P(i,o[a])&&e.$emit&&(n[a]=v(i),e.$emit(l,i))}}/*!
 * devextreme-vue
 * Version: 25.1.5
 * Build date: Wed Sep 03 2025
 *
 * Copyright (c) 2012 - 2025 Developer Express Inc. ALL RIGHTS RESERVED
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file in the root of the project for details.
 *
 * https://github.com/DevExpress/devextreme-vue
 */function g(t){const e=I(t);if(e)return e.$_config||t.$_config}function Ot(t){const e=I(t);if(e)return e.$_innerChanges||t.$_innerChanges}function B(t,e,n,s){t&&(t.init(Object.keys(e)),n&&k(t,n,s))}function A({name:t,isCollectionItem:e,ownerConfig:n},s){const i=n==null?void 0:n.fullPath;return{optionPath:t&&i?`${i}.${t}`:t||"",isCollection:e,removed:s}}function Et(){return $({beforeMount(){const t=this,e=g(t),n=Ot(t);B(e,ct(t).props,t,n),W(e,this,n)},mounted(){this.$parent.$_instance&&this.$parent.$_config.componentsCountChanged.push(A(g(this)))},beforeUnmount(){const t=g(this);t&&this.$parent.$_config.componentsCountChanged.push(A(t,!0))},render(){return null}})}/*!
 * devextreme-vue
 * Version: 25.1.5
 * Build date: Wed Sep 03 2025
 *
 * Copyright (c) 2012 - 2025 Developer Express Inc. ALL RIGHTS RESERVED
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file in the root of the project for details.
 *
 * https://github.com/DevExpress/devextreme-vue
 */const xt="dx-template-wrapper",C="dxremove";/*!
 * devextreme-vue
 * Version: 25.1.5
 * Build date: Wed Sep 03 2025
 *
 * Copyright (c) 2012 - 2025 Developer Express Inc. ALL RIGHTS RESERVED
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file in the root of the project for details.
 *
 * https://github.com/DevExpress/devextreme-vue
 */const U="template";function Nt(t){var n;const e=t;if(e&&(n=e.$_config)!=null&&n.name)return e}function At(t){return U in t.type.props&&ot(t)}function bt(t){const e={},n=j(t);for(const i in n){if(i==="default"&&t.$slots.default)continue;const o=n[i];o&&(e[i]=o)}const s=y(t);for(const i of s){const o=Nt(i);if(!o)continue;const r=M(i);if(!r||!At(i))continue;const a=`${o.$_config.fullPath}.${U}`;e[a]=r}return e}function vt(t){const e=[];return t.forEach(n=>{const s=L(n);s!=null&&s.$_optionName||e.push(n)}),e}function Tt(t,e,n,s,i){return at({name:s,inject:["eventBus"],created(){this.eventBus.add(this.$_updatedHandler)},mounted(){n.onRendered()},unmounted(){this.eventBus.remove(this.$_updatedHandler)},methods:{$_updatedHandler(){this.$forceUpdate()}},render:()=>{const o=vt(t()(n));return o?o.length>1?o:o[0]:T("div")}},e,i)}/*!
 * devextreme-vue
 * Version: 25.1.5
 * Build date: Wed Sep 03 2025
 *
 * Copyright (c) 2012 - 2025 Developer Express Inc. ALL RIGHTS RESERVED
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file in the root of the project for details.
 *
 * https://github.com/DevExpress/devextreme-vue
 */class Vt{constructor(e){this._slots={},this._templates={},this._isDirty=!1,this._component=e,this.discover()}discover(){this._slots={...bt(this._component)},tt(this._templates,this._slots)||this._prepareTemplates()}get templates(){return this._templates}get isDirty(){return this._isDirty}resetDirtyFlag(){this._isDirty=!1}_prepareTemplates(){this._templates={};for(const e of Object.keys(this._slots))this._templates[e]=this.createDxTemplate(e);this._isDirty=!0}createDxTemplate(e){return{render:n=>{const s=((c,f=0)=>()=>{f===1&&c&&c(),f++})(n.onRendered),i={data:n.model,index:n.index,onRendered:s},o=document.createElement("div"),r=n.container.get?n.container.get(0):n.container;r.appendChild(o);const a=Tt(()=>this._slots[e],this._component,i,e,o),l=a.$el;for(r.removeChild(o);o.firstChild;)r.appendChild(o.firstChild);if(G.setClass(l,xt,!0),l.nodeType===Node.TEXT_NODE){const c=document.createElement(r.nodeName==="TABLE"?"tbody":"span");c.style.display="none",r.insertBefore(c,r.firstChild),E(c,C,()=>{a.$.appContext.app.unmount.bind(a)(),c.remove()})}else E(l,C,a.$.appContext.app.unmount.bind(a));return s(),l}}}}/*!
 * devextreme-vue
 * Version: 25.1.5
 * Build date: Wed Sep 03 2025
 *
 * Copyright (c) 2012 - 2025 Developer Express Inc. ALL RIGHTS RESERVED
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file in the root of the project for details.
 *
 * https://github.com/DevExpress/devextreme-vue
 */const Pt=["id","class","style"],R="dx-";z({buyNowLink:"https://go.devexpress.com/Licensing_Installer_Watermark_DevExtremeVue.aspx",licensingDocLink:"https://go.devexpress.com/Licensing_Documentation_DevExtremeVue.aspx"});function b(t){return t.trim().split(/\s+/)}function Dt(t,e){const n={};return Pt.forEach(s=>{const i=t[s];if(i!=null)if(s==="class"){const o=i.split(" ").filter(r=>!r.startsWith(R)).join(" ");n[s]=[o,e].filter(r=>r!=="").join(" ")}else n[s]=i}),n}function wt(){return $({inheritAttrs:!1,data(){return{eventBus:q(),prevClassAttr:""}},provide(){return{eventBus:this.eventBus}},render(){const t=this,e=[],n=Mt(this.$el)||[];return t.$_config.cleanNested&&t.$_config.cleanNested(),nt(rt(this),e,t.$_config),this.$_processChildren(e),T("div",{...Dt(this.$attrs,n.join(" "))},e)},beforeUpdate(){const t=this;t.$_config.setPrevNestedOptions(t.$_config.getNestedOptionValues()),this.$_syncElementClassesWithClassAttr()},updated(){const t=this,e=S(this.$el);if(y(t).forEach(n=>{var s;B(n.$_config,n.type.props||{},(s=n==null?void 0:n.component)==null?void 0:s.proxy,n.$_innerChanges)}),t.$_templatesManager.discover(),t.$_instance.beginUpdate(),this.$_applyConfigurationChanges(),t.$_templatesManager.isDirty){t.$_instance.option("integrationOptions.templates",t.$_templatesManager.templates);const{props:n}=t.$.vnode;for(const s of Object.keys(t.$_templatesManager.templates))t.$_instance.option(x(n,s),s);t.$_templatesManager.resetDirtyFlag()}for(const n of Object.keys(t.$_pendingOptions))t.$_instance.option(n,t.$_pendingOptions[n]);t.$_pendingOptions={},t.$_instance.endUpdate(),H(this.$el,e),this.eventBus.fire()},beforeUnmount(){const e=this.$_instance;e&&(Y(this.$el,C),e.dispose())},created(){const t=this,e=N(this);t.$_config=new O((n,s)=>{Array.isArray(s)?t.$_instance.option(n,s):t.$_pendingOptions[n===u?"value":n]=s},null,e&&{...e},t.$_expectedChildren),t.$_innerChanges={},t.$_config.init(this.$props&&Object.keys(this.$props))},methods:{$_syncElementClassesWithClassAttr(){var e,n;const t=typeof((e=this.$attrs)==null?void 0:e.class)=="string"?(n=this.$attrs)==null?void 0:n.class:"";this.prevClassAttr!==t&&(this.prevClassAttr.length&&this.$el.classList.remove(...b(this.prevClassAttr)),t.length&&this.$el.classList.add(...b(t)),this.prevClassAttr=t)},$_applyConfigurationChanges(){const t=this;t.$_config.componentsCountChanged.forEach(({optionPath:e,isCollection:n,removed:s})=>{const i=t.$_config.getNestedOptionValues();!n&&s?t.$_instance.resetOption(e):t.$_instance.option(e,et(i,e))}),t.$_config.cleanComponentsCountChanged()},$_createWidget(t){const e=this;e.$_pendingOptions={},e.$_templatesManager=new Vt(this);const n=e.$_config;n.initialValues.hasOwnProperty(u)&&(n.initialValues.value=lt(n.initialValues));const s={templatesRenderAsynchronously:e.$_hasAsyncTemplate,...N(e),...n.initialValues,...n.getNestedOptionValues(),...this.$_getIntegrationOptions()},i=new e.$_WidgetClass(t,s);e.$_instance=i,i.on("optionChanged",o=>n.onOptionChanged(o)),k(n,e,e.$_innerChanges),W(n,e,e.$_innerChanges),this.$_createEmitters(i)},$_getIntegrationOptions(){const t=this,e={integrationOptions:{watchMethod:this.$_getWatchMethod()},...this.$_getExtraIntegrationOptions()};if(t.$_templatesManager.isDirty){const{templates:n}=t.$_templatesManager;e.integrationOptions.templates=n;const{props:s}=t.$.vnode;for(const i of Object.keys(n))e[x(s,i)]=i;t.$_templatesManager.resetDirtyFlag()}return e},$_getWatchMethod(){return(t,e,n)=>(n=n||{},n.skipImmediate||e(t()),this.$watch(()=>t(),(s,i)=>{(m(i)!==m(s)||n.deep)&&e(s)},{deep:n.deep}))},$_getExtraIntegrationOptions(){return{}},$_processChildren(t){},$_createEmitters(t){this.$attrs&&Object.keys(this.$attrs).forEach(e=>{const n=V(e);t.on(n,s=>{this.$emit(e,s)})})}}})}function S(t){const e=[];return Z(t,n=>{const s=n.parentNode,i=n.hasAttribute&&n.hasAttribute("isExtension");(n.nodeName==="#comment"||i)&&s&&(e.push(n),s.removeChild(n))}),e}function Mt(t){return t&&Array.from(t.classList).filter(e=>e.startsWith(R))}function H(t,e){e.forEach(n=>{t.appendChild(n)})}function jt(){return $({extends:wt(),methods:{$_getExtraIntegrationOptions(){return{onInitializing(){this.beginUpdate()}}},$_processChildren(t){t.forEach(e=>{!e||typeof e!="object"||(e.$_hasOwner=!0)})}},mounted(){var n;const t=S(this.$el),e=this;this.$_createWidget(this.$el),this.$_syncElementClassesWithClassAttr(),e.$_instance.endUpdate(),H(this.$el,t),(n=this.$slots)!=null&&n.default&&y(e).forEach(s=>{const i=s;i&&i.$_isExtension&&i.$_attachTo(this.$el)})}})}/*!
 * devextreme-vue
 * Version: 25.1.5
 * Build date: Wed Sep 03 2025
 *
 * Copyright (c) 2012 - 2025 Developer Express Inc. ALL RIGHTS RESERVED
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file in the root of the project for details.
 *
 * https://github.com/DevExpress/devextreme-vue
 */function Bt(t){t.extends=jt(),t.model&&ut(t)}function Ut(t){t.extends=Et()}export{Ut as a,Lt as b,E as c,Y as d,Wt as o,Bt as p,kt as t};
