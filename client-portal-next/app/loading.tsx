const shimmer: React.CSSProperties = {
  background:
    "linear-gradient(90deg, rgba(225,226,236,0.92) 25%, rgba(243,243,253,0.98) 50%, rgba(225,226,236,0.92) 75%)",
  backgroundSize: "200% 100%",
  animation: "portal-loading-shimmer 1.05s linear infinite",
};

function Bar({
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
        ...shimmer,
        width,
        height,
        borderRadius: radius,
      }}
    />
  );
}

export default function Loading() {
  return (
    <div className="ds-root">
      <header className="ds-top-bar">
        <div className="ds-top-bar-brand">
          <div
            style={{
              ...shimmer,
              width: 26,
              height: 26,
              borderRadius: 8,
            }}
          />
          <Bar width="96px" height={18} radius={8} />
        </div>

        <div className="ds-top-bar-avatar" style={{ ...shimmer, border: "none" }} />
      </header>

      <div
        style={{
          padding: "16px var(--container-margin) 0",
          maxWidth: 900,
          margin: "0 auto",
          width: "100%",
        }}
      >
        <Bar width="180px" height={22} radius={10} />
        <div style={{ marginTop: 8 }}>
          <Bar width="260px" height={12} radius={999} />
        </div>
      </div>

      <main className="ds-screen">
        <div className="ds-grid-3">
          {[0, 1, 2].map((item) => (
            <div key={item} className="ds-stat-card">
              <div className="ds-row-between">
                <Bar width="64px" height={10} />
                <div
                  style={{
                    ...shimmer,
                    width: 20,
                    height: 20,
                    borderRadius: 6,
                  }}
                />
              </div>
              <div style={{ marginTop: 12 }}>
                <Bar width="28px" height={30} radius={10} />
              </div>
            </div>
          ))}
        </div>

        <section className="ds-card ds-card-pad">
          <div className="ds-row-between" style={{ alignItems: "flex-start", marginBottom: 16 }}>
            <div style={{ display: "grid", gap: 8 }}>
              <Bar width="180px" height={18} radius={10} />
              <Bar width="240px" height={10} />
            </div>
            <Bar width="52px" height={28} radius={999} />
          </div>

          <div
            style={{
              ...shimmer,
              height: 248,
              borderRadius: 20,
              marginBottom: 16,
            }}
          />

          <div style={{ display: "grid", gap: 12 }}>
            <Bar width="88px" height={10} />
            <div className="ds-grid-3">
              {["1h", "6h", "24h"].map((label) => (
                <div
                  key={label}
                  style={{
                    ...shimmer,
                    height: 42,
                    borderRadius: 12,
                  }}
                />
              ))}
            </div>
          </div>

          <div className="ds-stats-grid" style={{ marginTop: 16 }}>
            {[0, 1, 2, 3].map((item) => (
              <div key={item} className="ds-kpi-card">
                <Bar width="74px" height={10} />
                <div style={{ marginTop: 10 }}>
                  <Bar width="92px" height={22} radius={10} />
                </div>
              </div>
            ))}
          </div>
        </section>

        <div>
          <div className="ds-row-between" style={{ marginBottom: 12 }}>
            <Bar width="132px" height={18} radius={10} />
            <Bar width="74px" height={10} />
          </div>

          <div className="ds-stack-sm">
            {[0, 1].map((item) => (
              <div key={item} className="ds-list-item">
                <div className="ds-list-item-head">
                  <div style={{ display: "grid", gap: 8 }}>
                    <Bar width="210px" height={12} />
                    <Bar width="156px" height={10} />
                  </div>
                  <div style={{ display: "grid", gap: 8, justifyItems: "end" }}>
                    <Bar width="68px" height={24} radius={999} />
                    <Bar width="84px" height={24} radius={999} />
                  </div>
                </div>

                <div className="ds-list-item-footer">
                  <Bar width="140px" height={10} />
                  <Bar width="96px" height={12} />
                </div>
              </div>
            ))}
          </div>
        </div>
      </main>

      <nav className="ds-bottom-nav">
        {Array.from({ length: 5 }, (_, index) => (
          <div
            key={index}
            className={`ds-nav-item${index === 0 ? " active" : ""}`}
            style={{ pointerEvents: "none" }}
          >
            <div
              style={{
                ...shimmer,
                width: 20,
                height: 20,
                borderRadius: 6,
              }}
            />
            <Bar width="46px" height={8} radius={999} />
          </div>
        ))}
      </nav>

      <style>{`
        @keyframes portal-loading-shimmer {
          0% { background-position: 200% 0; }
          100% { background-position: -200% 0; }
        }
      `}</style>
    </div>
  );
}
