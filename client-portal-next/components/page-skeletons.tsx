import type { CSSProperties, ReactNode } from "react";

const shimmerStyle: CSSProperties = {
  background:
    "linear-gradient(90deg, rgba(225,226,236,0.92) 25%, rgba(243,243,253,0.98) 50%, rgba(225,226,236,0.92) 75%)",
  backgroundSize: "200% 100%",
  animation: "portal-loading-shimmer 1.05s linear infinite",
};

function SkeletonBar({
  width,
  height = 12,
  radius = 999,
}: {
  width: string;
  height?: number;
  radius?: number;
}) {
  return (
    <div
      style={{
        ...shimmerStyle,
        width,
        height,
        borderRadius: radius,
      }}
    />
  );
}

function SkeletonBlock({
  height,
  radius = 20,
}: {
  height: number;
  radius?: number;
}) {
  return (
    <div
      style={{
        ...shimmerStyle,
        height,
        borderRadius: radius,
      }}
    />
  );
}

function BottomNavSkeleton({ activeIndex = 0 }: { activeIndex?: number }) {
  return (
    <nav className="ds-bottom-nav">
      {Array.from({ length: 5 }, (_, index) => (
        <div
          key={index}
          className={`ds-nav-item${index === activeIndex ? " active" : ""}`}
          style={{ pointerEvents: "none" }}
        >
          <div
            style={{
              ...shimmerStyle,
              width: 20,
              height: 20,
              borderRadius: 6,
            }}
          />
          <SkeletonBar width="46px" height={8} />
        </div>
      ))}
    </nav>
  );
}

function PortalFrameSkeleton({
  activeIndex,
  titleWidth,
  subtitleWidth,
  children,
}: {
  activeIndex: number;
  titleWidth: string;
  subtitleWidth: string;
  children: ReactNode;
}) {
  return (
    <div className="ds-root">
      <header className="ds-top-bar">
        <div className="ds-top-bar-brand">
          <div
            style={{
              ...shimmerStyle,
              width: 26,
              height: 26,
              borderRadius: 8,
            }}
          />
          <SkeletonBar width="96px" height={18} radius={8} />
        </div>

        <div className="ds-top-bar-avatar" style={{ ...shimmerStyle, border: "none" }} />
      </header>

      <div
        style={{
          padding: "16px var(--container-margin) 0",
          maxWidth: 900,
          margin: "0 auto",
          width: "100%",
        }}
      >
        <SkeletonBar width={titleWidth} height={22} radius={10} />
        <div style={{ marginTop: 8 }}>
          <SkeletonBar width={subtitleWidth} height={12} />
        </div>
      </div>

      <main className="ds-screen">{children}</main>

      <BottomNavSkeleton activeIndex={activeIndex} />

      <style>{`
        @keyframes portal-loading-shimmer {
          0% { background-position: 200% 0; }
          100% { background-position: -200% 0; }
        }
      `}</style>
    </div>
  );
}

function StatsRow({ count = 3 }: { count?: number }) {
  const columns =
    count === 2
      ? "repeat(2, minmax(0, 1fr))"
      : count === 4
        ? "repeat(2, minmax(0, 1fr))"
        : "repeat(3, minmax(0, 1fr))";

  return (
    <div style={{ display: "grid", gridTemplateColumns: columns, gap: "var(--gutter)" }}>
      {Array.from({ length: count }, (_, index) => (
        <div key={index} className="ds-stat-card">
          <div className="ds-row-between">
            <SkeletonBar width="64px" height={10} />
            <div
              style={{
                ...shimmerStyle,
                width: 20,
                height: 20,
                borderRadius: 6,
              }}
            />
          </div>
          <div style={{ marginTop: 12 }}>
            <SkeletonBar width="40px" height={28} radius={10} />
          </div>
          <div style={{ marginTop: 8 }}>
            <SkeletonBar width="74px" height={10} />
          </div>
        </div>
      ))}
    </div>
  );
}

function ListCards({ count = 2, showChips = true }: { count?: number; showChips?: boolean }) {
  return (
    <div className="ds-stack-sm">
      {Array.from({ length: count }, (_, index) => (
        <div key={index} className="ds-list-item">
          <div className="ds-list-item-head">
            <div style={{ display: "grid", gap: 8 }}>
              <SkeletonBar width="210px" height={12} />
              <SkeletonBar width="156px" height={10} />
            </div>
            {showChips ? (
              <div style={{ display: "grid", gap: 8, justifyItems: "end" }}>
                <SkeletonBar width="68px" height={24} />
                <SkeletonBar width="84px" height={24} />
              </div>
            ) : (
              <SkeletonBar width="72px" height={12} />
            )}
          </div>

          <div className="ds-list-item-footer">
            <SkeletonBar width="140px" height={10} />
            <SkeletonBar width="96px" height={12} />
          </div>
        </div>
      ))}
    </div>
  );
}

function TableSkeleton() {
  return (
    <div className="ds-card">
      <div className="ds-card-header">
        <SkeletonBar width="140px" height={18} radius={10} />
        <SkeletonBar width="64px" height={10} />
      </div>

      <div className="ds-table-wrap">
        <table className="ds-table">
          <thead>
            <tr>
              {Array.from({ length: 4 }, (_, index) => (
                <th key={index}>
                  <SkeletonBar width="70px" height={10} />
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {Array.from({ length: 4 }, (_, row) => (
              <tr key={row}>
                {Array.from({ length: 4 }, (_, col) => (
                  <td key={col}>
                    <div style={{ display: "grid", gap: 8 }}>
                      <SkeletonBar width={col === 0 ? "140px" : "110px"} height={10} />
                      <SkeletonBar width={col === 2 ? "92px" : "74px"} height={10} />
                    </div>
                  </td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

export function DashboardPageSkeleton() {
  return (
    <PortalFrameSkeleton activeIndex={0} titleWidth="180px" subtitleWidth="260px">
      <section className="ds-hero-card">
        <div style={{ display: "grid", gap: 10 }}>
          <SkeletonBar width="110px" height={10} />
          <SkeletonBar width="56px" height={40} radius={12} />
          <SkeletonBar width="240px" height={12} />
        </div>
        <div style={{ display: "grid", gap: 10, justifyItems: "end" }}>
          <SkeletonBar width="82px" height={26} />
          <SkeletonBar width="110px" height={10} />
        </div>
      </section>

      <StatsRow count={4} />

      <div className="ds-card">
        <div className="ds-card-header">
          <SkeletonBar width="130px" height={18} radius={10} />
          <SkeletonBar width="64px" height={10} />
        </div>
        <div style={{ padding: "var(--space-md) var(--space-lg)" }}>
          <ListCards count={2} />
        </div>
      </div>

      <div className="ds-card">
        <div className="ds-card-header">
          <SkeletonBar width="154px" height={18} radius={10} />
          <SkeletonBar width="64px" height={10} />
        </div>
        <div style={{ padding: "var(--space-md) var(--space-lg)" }}>
          <ListCards count={2} />
        </div>
      </div>
    </PortalFrameSkeleton>
  );
}

export function SubscriptionsPageSkeleton() {
  return (
    <PortalFrameSkeleton activeIndex={1} titleWidth="210px" subtitleWidth="286px">
      <StatsRow count={3} />

      <section className="ds-card ds-card-pad">
        <div className="ds-row-between" style={{ alignItems: "flex-start", marginBottom: 16 }}>
          <div style={{ display: "grid", gap: 8 }}>
            <SkeletonBar width="180px" height={18} radius={10} />
            <SkeletonBar width="240px" height={10} />
          </div>
          <SkeletonBar width="52px" height={28} />
        </div>

        <SkeletonBlock height={248} />

        <div style={{ display: "grid", gap: 12, marginTop: 16 }}>
          <SkeletonBar width="88px" height={10} />
          <div className="ds-grid-3">
            {Array.from({ length: 3 }, (_, index) => (
              <SkeletonBlock key={index} height={42} radius={12} />
            ))}
          </div>
        </div>

        <div className="ds-stats-grid" style={{ marginTop: 16 }}>
          {Array.from({ length: 4 }, (_, index) => (
            <div key={index} className="ds-kpi-card">
              <SkeletonBar width="74px" height={10} />
              <div style={{ marginTop: 10 }}>
                <SkeletonBar width="92px" height={22} radius={10} />
              </div>
            </div>
          ))}
        </div>
      </section>

      <div>
        <div className="ds-row-between" style={{ marginBottom: 12 }}>
          <SkeletonBar width="132px" height={18} radius={10} />
          <SkeletonBar width="74px" height={10} />
        </div>
        <ListCards count={2} />
      </div>
    </PortalFrameSkeleton>
  );
}

export function InvoicesPageSkeleton() {
  return (
    <PortalFrameSkeleton activeIndex={2} titleWidth="184px" subtitleWidth="250px">
      <StatsRow count={2} />
      <TableSkeleton />
    </PortalFrameSkeleton>
  );
}

export function TicketsPageSkeleton() {
  return (
    <PortalFrameSkeleton activeIndex={3} titleWidth="172px" subtitleWidth="280px">
      <StatsRow count={3} />
      <div>
        <div className="ds-row-between" style={{ marginBottom: 12 }}>
          <SkeletonBar width="126px" height={18} radius={10} />
          <SkeletonBar width="70px" height={10} />
        </div>
        <ListCards count={3} showChips={false} />
      </div>
    </PortalFrameSkeleton>
  );
}

export function NotificationsPageSkeleton() {
  return (
    <PortalFrameSkeleton activeIndex={4} titleWidth="156px" subtitleWidth="260px">
      <div className="ds-row-between">
        <SkeletonBar width="112px" height={24} />
        <SkeletonBar width="78px" height={24} />
      </div>

      <div style={{ display: "grid", gap: 20 }}>
        {Array.from({ length: 2 }, (_, sectionIndex) => (
          <div key={sectionIndex}>
            <div className="ds-category-header">
              <SkeletonBar width="96px" height={14} radius={8} />
              <div className="ds-category-line" />
            </div>
            <ListCards count={2} showChips={false} />
          </div>
        ))}
      </div>
    </PortalFrameSkeleton>
  );
}
