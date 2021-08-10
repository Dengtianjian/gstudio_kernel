class CImage extends HTMLElement {
  constructor() {
    super();
    const template = document.querySelector("#CImage");
    this.attachShadow({
      mode: "open",
    }).append(template.content.cloneNode(true));
    this.shadowRoot.querySelector("img").onload = function () {
      this.style.display = "block";
      this.parentNode.querySelector(".image-error").style.display = "none";
    };
    this.shadowRoot.querySelector("img").onerror = function () {
      this.style.display = "none";
      this.parentNode.querySelector(".image-error").style.display = "flex";
    };
  }
  static get observedAttributes() {
    return ["src", "width", "height", "round"];
  }
  attributeChangedCallback(name, oldValue, newValue) {
    switch (name) {
      case "round":
        if (isNaN(Number(newValue))) {
          this.shadowRoot.querySelector("img").style.borderRadius = "5px";
        } else {
          this.shadowRoot.querySelector("img").style.borderRadius =
            newValue + "px";
        }
        break;
      default:
        this.shadowRoot.querySelector("img").setAttribute(name, newValue);
        break;
    }
  }
}
export default CImage;
