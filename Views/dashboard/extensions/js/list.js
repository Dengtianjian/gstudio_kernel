const extensionStatusEls = {};
function installExtension(extensionId) {
  if (!extensionStatusEls[extensionId]) {
    extensionStatusEls[extensionId] = document.querySelector(
      "li[data-extension='" + extensionId + "'] .operation-status"
    );
  }
  let statusEl = extensionStatusEls[extensionId];
  statusEl.innerText = "安装中，请稍后";
  statusEl.style.display = "block";
  CDZXHTTP.post("_extension/install", {
    extension_id: extensionId,
  })
    .then((res) => {
      CMessage("安装成功");
      statusEl.innerText = "";
      statusEl.style.display = "none";
      // location.reload();
    })
    .catch((err) => {
      statusEl.innerText = "";
      statusEl.style.display = "none";
      CMessage(err.message);
    });
}
