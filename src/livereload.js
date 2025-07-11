// live reloading via SSE
// we start listening for changes one second after page load
// this makes sure that the page does not reload immediately after creating a new page
(() => {
  // dont load in modals
  let isModal = !!document.querySelector("body.modal");
  if (isModal) return;

  // dont load in iframes
  if (window.self !== window.top) return;

  // prevent double loading on tracy bluescreen
  if (document.body.classList.contains("LiveReload")) return;
  document.body.classList.add("LiveReload");

  class LiveReload {
    constructor() {
      this.url = document.currentScript.getAttribute("data-url");
      this.force = parseInt(document.currentScript.getAttribute("data-force"));
      this.reloading = false;

      // start stream
      const evtSource = new EventSource(this.url, { withCredentials: true });
      evtSource.onmessage = this.onMessage.bind(this);
      evtSource.onerror = this.onError.bind(this);

      // show startup info
      this.showStartupInfo();
    }

    forceReload() {
      // remove all .InputfieldStateChanged classes
      document.querySelectorAll(".InputfieldStateChanged").forEach((input) => {
        input.classList.remove("InputfieldStateChanged");
      });
      location.reload();
    }

    hasUnsavedChanges() {
      return !!document.querySelectorAll(".InputfieldStateChanged").length;
    }

    onError(event) {
      if (document.querySelector("#tracy-bs")) return;
      console.error("Error occurred in EventSource.");
      console.error(event);
    }

    onMessage(event) {
      if (this.reloading) return;
      let changed = event.data;
      if (!changed) return;
      this.changed = changed;
      localStorage.setItem(
        "livereload-count",
        (parseInt(localStorage.getItem("livereload-count")) || 0) + 1
      );
      localStorage.setItem("livereload-last", changed);
      this.reload();
    }

    reload() {
      this.reloading = true;
      this.showChangedInfo();
      if (document.hidden) return this.reloadHidden();
      if (this.hasUnsavedChanges()) return this.reloadUnsaved();
      this.forceReload();
    }

    reloadHidden() {
      console.log("Tab is not visible - waiting for reload ...");
      const interval = setInterval(() => {
        if (document.hidden) return;
        clearInterval(interval);
        this.reload();
      }, 100);
    }

    reloadUnsaved() {
      if (this.force) return this.forceReload();
      UIkit.notification({
        message:
          "Unsaved changes prevent reload - use $config->livereloadForce to force reload.",
        status: "warning",
        pos: "top-center",
        timeout: 0,
      });
    }

    showChangedInfo() {
      if (this.changeInfoShown) return;
      console.log("File changed: " + this.changed);
      this.changeInfoShown = true;
    }

    showStartupInfo() {
      const reloadCount = localStorage.getItem("livereload-count");
      console.log(
        `LiveReload is listening for file changes (force=${this.force}, count=${reloadCount}) ...`
      );
      // show last changed file
      const lastChanged = localStorage.getItem("livereload-last");
      if (lastChanged) console.log("Last changed file: " + lastChanged);
    }
  }

  new LiveReload();
})();
