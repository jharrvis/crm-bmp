import Link from "next/link";
import type { ReactNode } from "react";

type NavKey = "dashboard" | "subscriptions" | "invoices" | "tickets" | "notifications";

const navItems: Array<{ key: NavKey; href: string; label: string; icon: ReactNode }> = [
  {
    key: "dashboard",
    href: "/dashboard",
    label: "Home",
    icon: (
      <svg className="nav-icon" viewBox="0 0 24 24">
        <path d="M3 10.5 12 3l9 7.5" />
        <path d="M5 9.8V21h14V9.8" />
      </svg>
    ),
  },
  {
    key: "subscriptions",
    href: "/subscriptions",
    label: "Usage",
    icon: (
      <svg className="nav-icon" viewBox="0 0 24 24">
        <path d="M4 18h16" />
        <path d="M7 14l3-3 3 2 4-5" />
      </svg>
    ),
  },
  {
    key: "invoices",
    href: "/invoices",
    label: "Invoice",
    icon: (
      <svg className="nav-icon" viewBox="0 0 24 24">
        <path d="M7 3h10l3 3v15H4V3h3Z" />
        <path d="M8 10h8M8 14h8M8 18h5" />
      </svg>
    ),
  },
  {
    key: "tickets",
    href: "/tickets",
    label: "Tiket",
    icon: (
      <svg className="nav-icon" viewBox="0 0 24 24">
        <path d="M20 14a2 2 0 0 0 0-4V5H4v5a2 2 0 0 0 0 4v5h16v-5Z" />
        <path d="M12 5v14" />
      </svg>
    ),
  },
  {
    key: "notifications",
    href: "/notifications",
    label: "Notif",
    icon: (
      <svg className="nav-icon" viewBox="0 0 24 24">
        <path d="M15 17H5l2-2v-4a5 5 0 1 1 10 0v4l2 2h-4" />
        <path d="M10 19a2 2 0 0 0 4 0" />
      </svg>
    ),
  },
];

export function PortalShell({
  active,
  title,
  subtitle,
  action,
  children,
}: {
  active: NavKey;
  title: string;
  subtitle?: string;
  action?: React.ReactNode;
  children: React.ReactNode;
}) {
  return (
    <div className="app-root">
      <div className="phone-frame">
        <div className="screen">
          <div className="portal-shell">
            <main className="stack">
              <header className="topbar">
                <div className="topbar-copy">
                  <div className="brand-mark">
                    <div className="brand-mark-icon">B</div>
                    <div className="brand-mark-copy">
                      <span className="brand-eyebrow">Portal Client</span>
                      <span className="brand-title">
                        BMP<span style={{ color: "#2563eb" }}>net</span>
                      </span>
                    </div>
                  </div>
                  <h1 className="topbar-title">{title}</h1>
                  {subtitle ? <p className="topbar-subtitle">{subtitle}</p> : null}
                </div>
                {action ? <div className="topbar-action">{action}</div> : null}
              </header>
              {children}
            </main>

            <nav className="bottom-nav-wrap">
              <div className="bottom-nav">
                {navItems.map((item) => (
                  <Link
                    key={item.key}
                    href={item.href}
                    className={`nav-item ${item.key === active ? "active" : ""}`}
                  >
                    {item.icon}
                    <span>{item.label}</span>
                  </Link>
                ))}
              </div>
            </nav>
          </div>
        </div>
      </div>
    </div>
  );
}
