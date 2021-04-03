const AddonPopupEl = document.querySelector(".addons-popup");
const AddonPopupMaskEl=document.querySelector(".addons-popup-mask");
function showAddonPopup(el) {
  AddonPopupEl.style.display = "block";
  AddonPopupMaskEl.style.display = "block";
}

function hideAddonPopup() {
  AddonPopupEl.style.display = "none";
  AddonPopupMaskEl.style.display = "none";
}