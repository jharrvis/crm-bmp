"use client";

import { useEffect, useState } from "react";

type BeforeInstallPromptEvent = Event & {
  prompt: () => Promise<void>;
  userChoice: Promise<{
    outcome: "accepted" | "dismissed";
    platform: string;
  }>;
};

const DISMISSED_KEY = "bmpnet.portal.install.dismissed";

export function PwaInstallPrompt() {
  const [deferredPrompt, setDeferredPrompt] = useState<BeforeInstallPromptEvent | null>(null);
  const [showIosHint, setShowIosHint] = useState(false);
  const [isHidden, setIsHidden] = useState(true);

  useEffect(() => {
    if (typeof window === "undefined") {
      return;
    }

    const isStandalone =
      window.matchMedia("(display-mode: standalone)").matches ||
      Boolean((window.navigator as Navigator & { standalone?: boolean }).standalone);

    if (isStandalone || window.localStorage.getItem(DISMISSED_KEY) === "1") {
      return;
    }

    const userAgent = window.navigator.userAgent.toLowerCase();
    const isIos = /iphone|ipad|ipod/.test(userAgent);
    const isSafari = /safari/.test(userAgent) && !/crios|fxios|edgios/.test(userAgent);

    if (isIos && isSafari) {
      setShowIosHint(true);
      setIsHidden(false);
    }

    const handleBeforeInstallPrompt = (event: Event) => {
      event.preventDefault();
      setDeferredPrompt(event as BeforeInstallPromptEvent);
      setShowIosHint(false);
      setIsHidden(false);
    };

    const handleAppInstalled = () => {
      setDeferredPrompt(null);
      setShowIosHint(false);
      setIsHidden(true);
      window.localStorage.setItem(DISMISSED_KEY, "1");
    };

    window.addEventListener("beforeinstallprompt", handleBeforeInstallPrompt);
    window.addEventListener("appinstalled", handleAppInstalled);

    return () => {
      window.removeEventListener("beforeinstallprompt", handleBeforeInstallPrompt);
      window.removeEventListener("appinstalled", handleAppInstalled);
    };
  }, []);

  const hidePrompt = () => {
    if (typeof window !== "undefined") {
      window.localStorage.setItem(DISMISSED_KEY, "1");
    }

    setIsHidden(true);
  };

  const handleInstall = async () => {
    if (!deferredPrompt) {
      return;
    }

    await deferredPrompt.prompt();
    const choice = await deferredPrompt.userChoice;

    if (choice.outcome === "accepted") {
      hidePrompt();
    }

    setDeferredPrompt(null);
  };

  if (isHidden) {
    return null;
  }

  return (
    <div className="ds-install-banner" role="status" aria-live="polite">
      <div className="ds-install-copy">
        <div className="ds-install-title">Install aplikasi BMPnet</div>
        <div className="ds-install-text">
          {showIosHint
            ? "Di iPhone, buka Share lalu pilih Add to Home Screen."
            : "Tambahkan portal ke home screen agar lebih cepat dibuka."}
        </div>
      </div>

      <div className="ds-install-actions">
        {deferredPrompt ? (
          <button type="button" className="ds-btn ds-btn-install" onClick={handleInstall}>
            Install
          </button>
        ) : null}

        <button type="button" className="ds-btn ds-btn-install-muted" onClick={hidePrompt}>
          Nanti
        </button>
      </div>
    </div>
  );
}
