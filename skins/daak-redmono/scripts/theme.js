(() => {
  const root = document.documentElement;
  const storageKey = "redmono-mail-theme";
  const cookieName = "colorMode";
  const userCacheKey = "redmono_user_email";

  const enforceLight = () => {
    try {
      localStorage.setItem(storageKey, "light");
    } catch (e) {}
    document.cookie = cookieName + "=light; path=/; max-age=31536000; SameSite=Lax";
    root.classList.remove("dark-mode");
    root.classList.add("light-mode");
    root.style.colorScheme = "light";
  };

  const updateFrames = () => {
    document.querySelectorAll("iframe").forEach((frame) => {
      try {
        const doc = frame.contentDocument;
        const docEl = doc?.documentElement;
        if (!docEl) return;

        docEl.classList.remove("dark-mode");
        docEl.classList.add("light-mode");
        docEl.style.colorScheme = "light";

        const isWatermark =
          (frame.getAttribute("src") || "").includes("watermark") ||
          !docEl.classList.contains("iframe");
        if (doc.body && isWatermark) {
          doc.body.style.backgroundColor = "transparent";

          const isTr = (document.documentElement.lang || navigator.language || "")
            .toLowerCase()
            .startsWith("tr");
          const task = window.rcmail?.env?.task || "";
          const action = window.rcmail?.env?.action || "";
          const titleEl = doc.querySelector("p.title, .title");
          const subEl = doc.querySelector("p.subtitle, .subtitle");
          if (titleEl && subEl) {
            const copy = isTr
              ? {
                  folders: ["Bir klasör seçin", "Özelliklerini görüntülemek veya düzenlemek için soldaki listeden bir klasöre tıklayın"],
                  identities: ["Bir kimlik seçin", "Ayarlarını görüntülemek veya düzenlemek için soldaki listeden bir kimliğe tıklayın"],
                  responses: ["Bir hazır yanıt seçin", "Görüntülemek veya düzenlemek için soldaki listeden bir hazır yanıta tıklayın"],
                  sieve: ["Bir süzgeç seçin", "Kuralları görüntülemek veya düzenlemek için soldaki listeden bir süzgece tıklayın"],
                  settings: ["Bir ayar bölümü seçin", "Görüntülemek veya düzenlemek için soldaki listeden bir öğeye tıklayın"],
                  addressbook: ["Bir kişi seçin", "Bilgileri görüntülemek veya düzenlemek için soldaki listeden bir kişiye tıklayın"],
                  mail: ["Bir e-posta seçin", "Okumak veya yanıtlamak için soldaki listeden bir e-postaya tıklayın"],
                }
              : {
                  folders: ["Select a folder", "Click a folder in the list on the left to view or edit its properties"],
                  identities: ["Select an identity", "Click an identity in the list on the left to view or edit its settings"],
                  responses: ["Select a response", "Click a canned response in the list on the left to view or edit it"],
                  sieve: ["Select a filter", "Click a filter rule in the list on the left to view or edit its conditions"],
                  settings: ["Select a setting", "Click an item in the list on the left to view or edit its preferences"],
                  addressbook: ["Select a contact", "Click a contact in the list on the left to view or edit its details"],
                  mail: ["Select a message", "Click an email in the list on the left to read or reply"],
                };

            let key = "mail";
            if (task === "settings") {
              if (action === "folders") key = "folders";
              else if (action === "identities") key = "identities";
              else if (action === "responses") key = "responses";
              else if (action.includes("managesieve")) key = "sieve";
              else key = "settings";
            } else if (task === "addressbook") {
              key = "addressbook";
            }

            titleEl.textContent = copy[key][0];
            subEl.textContent = copy[key][1];
          }
        }
      } catch (e) {}
    });
  };

  const getActiveFolderName = () => {
    const activeLink = document.querySelector(
      "#mailboxlist li.selected > a, #folderlist-content li.selected > a"
    );
    if (!activeLink) return "Gelen";
    const clone = activeLink.cloneNode(true);
    clone.querySelectorAll(".unreadcount, .voice").forEach((el) => el.remove());
    const name = clone.textContent.replace(/\s+/g, " ").trim();
    return name || "Gelen";
  };

  const syncHeadersAndToolbars = () => {
    document
      .querySelectorAll(
        "#layout-sidebar > .header > .header-title, #layout-list > .header > .header-title, #layout-content > .header > .header-title"
      )
      .forEach((titleEl) => {
        if (
          titleEl.closest("#layout-sidebar") ||
          titleEl.closest("#layout-list")
        ) {
          titleEl.classList.add("all-sizes");
        }
        titleEl.style.marginLeft = "0px";
        titleEl.style.marginRight = "0px";
      });

    const listTitle = document.querySelector("#messagelist-header .header-title");
    if (listTitle) {
      listTitle.style.paddingRight = "4px";
      if (!listTitle.textContent.trim()) {
        listTitle.textContent = getActiveFolderName();
      }
    }

    const contentTitle = document.querySelector("#layout-content > .header > .header-title");
    if (contentTitle) {
      contentTitle.style.paddingRight = "0px";
    }

    const hiddenClasses = ["hidden-big", "hidden-large", "hidden-lbs", "hidden"];
    const isWide =
      root.classList.contains("layout-large") ||
      root.classList.contains("layout-normal") ||
      window.innerWidth > 1024;
    document.querySelectorAll(".header .toolbar.menu li").forEach((li) => {
      const link = li.querySelector(":scope > a, :scope > .dropbutton > a:first-child");
      if (!link) return;
      hiddenClasses.forEach((cls) => {
        li.classList.toggle(cls, link.classList.contains(cls));
      });
      const linkHidden =
        link.style.display === "none" ||
        (isWide && (link.getAttribute("data-hidden") || "").includes("big"));
      li.classList.toggle("hidden", linkHidden || link.classList.contains("hidden"));
    });
  };

  const renderAccountBadge = () => {
    const badge = document.getElementById("maham-account-badge");
    if (!badge) return;

    let username = "";
    const headerUser = document.querySelector("#layout-sidebar .header-title.username, .header-title.username");
    if (headerUser && headerUser.textContent) {
      username = headerUser.textContent.trim();
    }
    if (!username && window.rcmail?.env?.username) {
      username = String(window.rcmail.env.username).trim();
    }
    if (username) {
      try {
        localStorage.setItem(userCacheKey, username);
      } catch (e) {}
    } else {
      try {
        username = localStorage.getItem(userCacheKey) || "";
      } catch (e) {}
    }

    if (!username) {
      badge.style.display = "none";
      return;
    }

    const displayName = username.includes("@") ? username.split("@")[0] : username;
    const initial = (displayName.charAt(0) || "R").toUpperCase();

    badge.textContent = "";
    badge.style.display = "flex";

    const avatar = document.createElement("span");
    avatar.className = "maham-avatar";
    avatar.textContent = initial;

    const meta = document.createElement("div");
    meta.className = "maham-account-meta";

    const nameEl = document.createElement("div");
    nameEl.className = "maham-account-name";
    nameEl.textContent = displayName;
    nameEl.title = username;

    const roleEl = document.createElement("div");
    roleEl.className = "maham-account-role";
    roleEl.textContent = username.includes("@")
      ? username
      : (window.rcmail?.env?.product_name || "DAAK-Redmono Mail");

    meta.appendChild(nameEl);
    meta.appendChild(roleEl);
    badge.appendChild(avatar);
    badge.appendChild(meta);
  };

  const initMahamSidebar = () => {
    enforceLight();

    const foldersSlot = document.getElementById("maham-folders-slot");
    const folderListContent = document.getElementById("folderlist-content");

    if (foldersSlot) {
      if (folderListContent) {
        foldersSlot.style.display = "";
        if (!foldersSlot.contains(folderListContent)) {
          foldersSlot.appendChild(folderListContent);
        }
        document.body?.classList.add("maham-folders-docked");
      } else {
        foldersSlot.style.display = "none";
        document.body?.classList.remove("maham-folders-docked");
      }
    }

    renderAccountBadge();
    syncHeadersAndToolbars();
    updateFrames();
    requestAnimationFrame(syncHeadersAndToolbars);
    setTimeout(syncHeadersAndToolbars, 60);
  };

  enforceLight();

  document.addEventListener("DOMContentLoaded", () => {
    initMahamSidebar();

    document.addEventListener("click", (ev) => {
      const folderLink = ev.target?.closest?.("#mailboxlist li a, #folderlist-content li a");
      if (folderLink) {
        const listTitle = document.querySelector("#messagelist-header .header-title");
        if (listTitle) {
          const clone = folderLink.cloneNode(true);
          clone.querySelectorAll(".unreadcount, .voice").forEach((el) => el.remove());
          const name = clone.textContent.replace(/\s+/g, " ").trim();
          if (name) {
            listTitle.textContent = name;
          }
        }
        requestAnimationFrame(syncHeadersAndToolbars);
      }
    });

    if (window.rcmail && typeof window.rcmail.addEventListener === "function") {
      window.rcmail.addEventListener("init", initMahamSidebar);
      window.rcmail.addEventListener("listupdate", () => {
        renderAccountBadge();
        syncHeadersAndToolbars();
      });
      window.rcmail.addEventListener("afterlist", syncHeadersAndToolbars);
      window.rcmail.addEventListener("selectfolder", () => {
        const listTitle = document.querySelector("#messagelist-header .header-title");
        if (listTitle) {
          listTitle.textContent = getActiveFolderName();
        }
        syncHeadersAndToolbars();
      });
      window.rcmail.addEventListener("skin-resize", syncHeadersAndToolbars);
    }

    document.querySelectorAll("iframe").forEach((frame) => {
      frame.addEventListener("load", updateFrames);
    });
  });

  function localizeChromeLabels() {
    const isTr = (document.documentElement.lang || navigator.language || "")
      .toLowerCase()
      .startsWith("tr");
    if (!isTr) return;

    const acctLbl = document.getElementById("maham-account-label");
    if (acctLbl) acctLbl.textContent = "Hesap";

    const loginSub = document.getElementById("daak-login-subtitle");
    if (loginSub) loginSub.textContent = "Mesajlarınıza güvenli biçimde her cihazdan erişin.";

    const loginSec = document.getElementById("daak-login-secure");
    if (loginSec) loginSec.textContent = "Güvenli bağlantı";
  }

  document.addEventListener("DOMContentLoaded", localizeChromeLabels);
  window.addEventListener("resize", syncHeadersAndToolbars);
  window.addEventListener("load", initMahamSidebar);
})();
