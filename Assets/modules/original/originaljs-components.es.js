import{createElement,ObserverNodeAttributes,Transition}from"./originaljs.js";let template$3=`<style>\n.c-button {\n  position: relative;\n  padding: 5px 10px;\n  border: none;\n  border-radius: 2px;\n  transition: filter 0.13s ease-in-out;\n  cursor: pointer;\n  background: none;\n}\n.c-button:hover {\n  filter: brightness(1.1);\n}\n.c-button:active {\n  filter: brightness(0.8);\n}\n.c-button_normal {\n  border: 1px solid #ccc;\n}\n.c-button_normal:hover {\n  color: white;\n  background: var(--primary-color);\n  border-color: transparent;\n}\n.c-button_primary {\n  color: white;\n  background-color: var(--primary-color);\n  border: none;\n}\n.c-button_warning {\n  color: white;\n  background-color: var(--warning-color);\n  border: none;\n}\n.c-button_success {\n  color: white;\n  background-color: var(--success-color);\n  border: none;\n}\n.c-button_danger {\n  color: white;\n  background-color: var(--danger-color);\n  border: none;\n}\n.c-button_success.c-button_plain {\n  color: var(--success-color);\n  border: 1px solid var(--success-color);\n  background-color: transparent;\n}\n.c-button_success.c-button_plain::before {\n  position: absolute;\n  top: 0;\n  left: 0;\n  width: 100%;\n  height: 100%;\n  opacity: 0.2;\n  background-color: currentColor;\n}\n.c-button_success.c-button_plain:hover::before {\n  content: "";\n}\n</style>\n<button class="c-button {buttonClassName}" ><slot /></button>\n`;class CButton extends(createElement(["type"])){constructor(){super();this.buttonClassName="c-button_normal"}set type(val){const className="c-button_"+val;this.setState("buttonClassName",className)}render(){return template$3}}let template$2=`<link\nrel="stylesheet"\nhref="{src}"\n/>\n<i class="{font} {value}" style="font-size:{size}"></i>\n`;class CIcon extends(createElement(["font","value","src","size"])){constructor(){super(...arguments);this.font="iconfont";this.value="icon";this.src="";this.size="16px"}render(){return template$2}}let template$1=`\n<style>\n  .c-modal {\n    position: fixed;\n    left: 0;\n    right: 0;\n    margin: 0 auto;\n    z-index: 2;\n    width: 50%;\n    padding: 20px;\n    background-color: white;\n    border-radius: var(--border-radius);\n    box-sizing: border-box;\n  }\n\n  .c-modal_header {\n    display: flex;\n    justify-content: space-between;\n    align-items: center;\n    padding-bottom: 10px;\n    border-bottom: 1px solid #eee;\n  }\n\n  .c-modal-close {\n    cursor: pointer;\n  }\n\n  .c-modal-close:hover {\n    color: red;\n  }\n\n  .c-model_body {\n    margin-top: 20px;\n    word-break: break-all;\n  }\n\n  .c-model_footer {\n    display: flex;\n    justify-content: space-between;\n    margin-top: 20px;\n  }\n</style>\n<div class="c-modal" style="z-index:{zIndex};width:{width};min-height:{height};top:{top}">\n  <div class="c-modal_header">\n    <div class="c-modal-title">{title}</div>\n    <div class="c-modal-close" onclick="hide">\n      <c-icon font="shoutao" value="st-icon-close" size="20px"></c-icon>\n    </div>\n  </div>\n  <div class="c-model_body">\n    <slot></slot>\n  </div>\n  <div class="c-model_footer">\n    <slot name="footer"></slot>\n    <div>\n      <div>\n      </div>\n      <div>\n        <c-button onclick="hide">取消</c-button>\n        <c-button type="primary" onclick="confirm">确定</c-button>\n      </div>\n    </div>\n  </div>\n</div>\n<c-overlay onclick="hide"></c-overlay>\n`;class CModal extends(createElement(["title","height","width","zIndex","top"])){constructor(){super();this._slots={};this.zIndex=2;this.title="Title";this.height="150px";this.width="700px";this.top="10vh";ObserverNodeAttributes(this,"hidden",(()=>{this.show()}))}render(){return template$1}connected(){this._collectionSlots()}show(){const modalT=new Transition;const overlayT=new Transition;overlayT.el("c-overlay",this.$ref).step({opacity:0},.1).step({opacity:1},.2);modalT.el(".c-modal",this.$ref).step({transform:"translateY(-40px)",opacity:0},.1).step({transform:"translateY(0px)",opacity:"1"},.2).clear()}hide(){const modalT=new Transition;const overlayT=new Transition;modalT.el(".c-modal",this.$ref).step({transform:"translateY(-40px)",opacity:"0.2"}).clear();overlayT.el("c-overlay",this.$ref).step({opacity:0},.25).clear().end((()=>{this.hidden=true}))}confirm(){this.hide()}_collectionSlots(){const slots=this.$ref.querySelectorAll("slot");for(const slot of Array.from(slots)){const slotName=slot.name||"default";if(!this._slots[slotName]){this._slots[slotName]=[]}slot.addEventListener("slotchange",(()=>{if(slotName==="default"){this._slots[slotName].push(...this.$ref.querySelector("slot:not(name)").assignedNodes())}else{this._slots[slotName].push(...this.$ref.querySelector("slot[name='"+slotName+"']").assignedNodes())}}))}}}let template=`<style>\n.c-overlay {\n  position: fixed;\n  z-index:{zIndex};\n  top: 0;\n  left: 0;\n  width: 100%;\n  height: 100%;\n  background-color: rgba(0, 0, 0, 0.2);\n}\n</style>\n<div class="c-overlay"></div>\n`;class COverlay extends(createElement(["zIndex"])){constructor(){super(...arguments);this.zIndex=1}render(){return template}}var index={CButton:CButton,CIcon:CIcon,CModal:CModal,COverlay:COverlay};export default index;
