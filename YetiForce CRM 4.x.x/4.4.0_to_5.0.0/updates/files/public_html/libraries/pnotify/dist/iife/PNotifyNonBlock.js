var _typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},_extends=Object.assign||function(t){for(var o=1;o<arguments.length;o++){var e=arguments[o];for(var n in e)Object.prototype.hasOwnProperty.call(e,n)&&(t[n]=e[n])}return t},PNotifyNonBlock=function(n){"use strict";n=n&&n.__esModule?n.default:n;var o;function t(t){var o,e;e=t,(o=this)._handlers=Object.create(null),o._bind=e._bind,o.options=e,o.root=e.root||o,o.store=o.root.store||e.store,this._state=s(_extends({_notice:null,_options:{}},n.modules.NonBlock.defaults),t.data),this._intro=!0,this._fragment=(this._state,{c:i,m:i,p:i,d:i}),t.target&&(this._fragment.c(),this._mount(t.target,t.anchor))}function i(){}function s(t,o){for(var e in o)t[e]=o[e];return t}function e(t){for(;t&&t.length;)t.shift()()}return s(t.prototype,{destroy:function(t){this.destroy=i,this.fire("destroy"),this.set=i,this._fragment.d(!1!==t),this._fragment=null,this._state={}},get:function(){return this._state},fire:function(t,o){var e=t in this._handlers&&this._handlers[t].slice();if(!e)return;for(var n=0;n<e.length;n+=1){var i=e[n];i.__calling||(i.__calling=!0,i.call(this,o),i.__calling=!1)}},on:function(t,o){var e=this._handlers[t]||(this._handlers[t]=[]);return e.push(o),{cancel:function(){var t=e.indexOf(o);~t&&e.splice(t,1)}}},set:function(t){if(this._set(s({},t)),this.root._lock)return;this.root._lock=!0,e(this.root._beforecreate),e(this.root._oncreate),e(this.root._aftercreate),this.root._lock=!1},_set:function(t){var o=this._state,e={},n=!1;for(var i in t)this._differs(t[i],o[i])&&(e[i]=n=!0);if(!n)return;this._state=s(s({},o),t),this._recompute(e,this._state),this._bind&&this._bind(e,this._state);this._fragment&&(this.fire("state",{changed:e,current:this._state,previous:o}),this._fragment.p(e,this._state),this.fire("update",{changed:e,current:this._state,previous:o}))},_mount:function(t,o){this._fragment[this._fragment.i?"i":"m"](t,o||null)},_differs:function(t,o){return t!=t?o==o:t!==o||t&&"object"===(void 0===t?"undefined":_typeof(t))||"function"==typeof t}}),s(t.prototype,{initModule:function(t){this.set(t),this.doNonBlockClass()},update:function(){this.doNonBlockClass()},doNonBlockClass:function(){this.get().nonblock?this.get()._notice.addModuleClass("nonblock"):this.get()._notice.removeModuleClass("nonblock")}}),t.prototype._recompute=i,(o=t).key="NonBlock",o.defaults={nonblock:!1},o.init=function(t){return new o({target:document.body,data:{_notice:t}})},n.modules.NonBlock=o,t}(PNotify);
//# sourceMappingURL=PNotifyNonBlock.js.map