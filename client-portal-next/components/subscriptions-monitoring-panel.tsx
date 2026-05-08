"use client";

import Link from "next/link";
import { useState, useTransition } from "react";
import { SubscriptionUsageChart } from "@/components/subscription-usage-chart";
import type {
  PortalSubscriptionSummary,
  SubscriptionChartPayload,
  SubscriptionUsageInterface,
  SubscriptionUsagePayload,
} from "@/lib/types";

const PERIODS = ["1h", "6h", "24h", "7d", "30d", "90d"] as const;

type MonitoringPanelProps = {
  subscriptions: PortalSubscriptionSummary[];
  selectedSubscription: PortalSubscriptionSummary | null;
  usagePayload: SubscriptionUsagePayload | null;
  initialInterface: SubscriptionUsageInterface | null;
  initialChart: SubscriptionChartPayload | null;
  initialChartError: string | null;
  initialPeriod: string;
};

export function SubscriptionsMonitoringPanel({
  subscriptions,
  selectedSubscription,
  usagePayload,
  initialInterface,
  initialChart,
  initialChartError,
  initialPeriod,
}: MonitoringPanelProps) {
  const [selectedPeriod, setSelectedPeriod] = useState(initialPeriod);
  const [selectedInterface, setSelectedInterface] = useState<SubscriptionUsageInterface | null>(
    initialInterface,
  );
  const [chart, setChart] = useState<SubscriptionChartPayload | null>(initialChart);
  const [chartError, setChartError] = useState<string | null>(initialChartError);
  const [animationSeed, setAnimationSeed] = useState(0);
  const [isPending, startTransition] = useTransition();

  const active = subscriptions.filter((s) => s.status === "active").length;
  const withUsage = subscriptions.filter((s) => s.has_usage).length;

  const requestChart = (nextPeriod: string, nextInterface: SubscriptionUsageInterface | null) => {
    if (!selectedSubscription || !nextInterface) {
      return;
    }

    setSelectedPeriod(nextPeriod);
    setSelectedInterface(nextInterface);

    startTransition(async () => {
      const params = new URLSearchParams({
        subscription_id: String(selectedSubscription.id),
        period: nextPeriod,
        graphid: nextInterface.graphid ?? "",
        itemin: nextInterface.itemIn,
        itemout: nextInterface.itemOut,
      });

      try {
        const response = await fetch(`/api/subscriptions/monitoring?${params.toString()}`, {
          method: "GET",
          cache: "no-store",
        });

        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
          throw new Error(payload?.message ?? "Gagal memuat chart monitoring.");
        }

        setChart(payload as SubscriptionChartPayload);
        setChartError(null);
        setAnimationSeed((value) => value + 1);
      } catch (error) {
        setChartError(error instanceof Error ? error.message : "Gagal memuat chart monitoring.");
      }
    });
  };

  return (
    <>
      <div className="ds-grid-3">
        <div className="ds-stat-card">
          <div className="ds-row-between">
            <span className="ds-stat-caption">Aktif</span>
            <span className="material-symbols-outlined ds-text-secondary" style={{ fontSize: 20 }}>
              wifi
            </span>
          </div>
          <div className="ds-stat-value ds-text-secondary">{active}</div>
        </div>
        <div className="ds-stat-card">
          <div className="ds-row-between">
            <span className="ds-stat-caption">Total</span>
            <span className="material-symbols-outlined ds-text-primary" style={{ fontSize: 20 }}>
              lan
            </span>
          </div>
          <div className="ds-stat-value ds-text-primary">{subscriptions.length}</div>
        </div>
        <div className="ds-stat-card">
          <div className="ds-row-between">
            <span className="ds-stat-caption">Usage Ready</span>
            <span className="material-symbols-outlined ds-text-tertiary" style={{ fontSize: 20 }}>
              monitoring
            </span>
          </div>
          <div className="ds-stat-value ds-text-tertiary">{withUsage}</div>
        </div>
      </div>

      {selectedSubscription && selectedSubscription.has_usage ? (
        <section className="ds-card ds-card-pad ds-monitor-panel">
          <div className="ds-monitor-header">
            <div>
              <h3 className="ds-title-sm" style={{ margin: 0 }}>
                Bandwidth Monitoring
              </h3>
              <p className="ds-body-sm ds-text-muted" style={{ margin: "6px 0 0" }}>
                {selectedSubscription.subscription_code} • {selectedSubscription.service_name ?? "-"} •{" "}
                {selectedSubscription.package_name ?? "-"}
              </p>
            </div>
            <span className="ds-chip ds-chip-info">{selectedPeriod}</span>
          </div>

          <div className={`ds-chart-surface${isPending ? " is-loading" : ""}`}>
            {isPending ? (
              <div className="ds-chart-loader" aria-live="polite" aria-label="Memuat chart">
                <div className="ds-spinner" />
                <span className="ds-body-sm ds-text-muted">Memuat data monitoring...</span>
              </div>
            ) : null}

            {chartError ? (
              <div className="ds-alert ds-alert-error">{chartError}</div>
            ) : chart ? (
              <div key={animationSeed} className="ds-chart-animate">
                <div className="ds-row-between" style={{ marginBottom: 12, alignItems: "flex-start" }}>
                  <div>
                    <div className="ds-body-sm" style={{ fontWeight: 700 }}>
                      {chart.interface?.name ?? "Traffic Chart"}
                    </div>
                    <div className="ds-body-sm ds-text-muted" style={{ marginTop: 4 }}>
                      {chart.rangeLabel} • {chart.updatedAt} • {chart.dataMode}
                    </div>
                  </div>
                  {chart.isLive ? <span className="ds-chip ds-chip-online">Live</span> : null}
                </div>
                <SubscriptionUsageChart labels={chart.labels} dataIn={chart.dataIn} dataOut={chart.dataOut} />
              </div>
            ) : (
              <div className="ds-empty">Chart monitoring belum tersedia untuk langganan ini.</div>
            )}
          </div>

          <div className="ds-field-group">
            <label className="ds-field-label">Preset Range</label>
            <div className="ds-period-grid">
              {PERIODS.map((period) => (
                <button
                  key={period}
                  type="button"
                  onClick={() => requestChart(period, selectedInterface)}
                  className={`ds-period-chip${selectedPeriod === period ? " active" : ""}`}
                  disabled={isPending}
                >
                  {period}
                </button>
              ))}
            </div>
          </div>

          {chart ? (
            <div className="ds-stats-grid">
              <KpiCard title="Current IN" value={chart.stats.curIn} tone="primary" />
              <KpiCard title="Current OUT" value={chart.stats.curOut} tone="tertiary" />
              <KpiCard title="Max IN" value={chart.stats.maxIn} tone="secondary" />
              <KpiCard title="Max OUT" value={chart.stats.maxOut} tone="tertiary" />
            </div>
          ) : null}

          {usagePayload && usagePayload.interfaces.length > 0 ? (
            <div className="ds-field-group">
              <label className="ds-field-label">Interface</label>
              <div className="ds-stack-sm">
                {usagePayload.interfaces.map((item) => {
                  const isActive =
                    selectedInterface?.itemIn === item.itemIn &&
                    selectedInterface?.itemOut === item.itemOut;

                  return (
                    <button
                      key={`${item.itemIn}-${item.itemOut}`}
                      type="button"
                      onClick={() => requestChart(selectedPeriod, item)}
                      className={`ds-period-chip${isActive ? " active" : ""}`}
                      disabled={isPending}
                    >
                      {item.name ?? `Interface ${item.itemIn}`}
                    </button>
                  );
                })}
              </div>
            </div>
          ) : null}
        </section>
      ) : (
        <div className="ds-empty">Belum ada langganan dengan interface monitoring yang siap dipantau.</div>
      )}

      <div>
        <div className="ds-row-between" style={{ marginBottom: 12 }}>
          <h3 className="ds-title-sm" style={{ margin: 0 }}>
            Daftar Langganan
          </h3>
          <span className="ds-label-caps ds-text-muted">{subscriptions.length} layanan</span>
        </div>

        {subscriptions.length === 0 ? (
          <div className="ds-empty">Belum ada data langganan.</div>
        ) : (
          <div className="ds-stack-sm">
            {subscriptions.map((sub) => {
              const isActiveSelection = selectedSubscription?.id === sub.id;

              return (
                <Link
                  key={sub.id}
                  href={`/subscriptions?subscription_id=${sub.id}`}
                  className={`ds-link-card${isActiveSelection ? " active" : ""}`}
                >
                  <div className="ds-list-item">
                    <div className="ds-list-item-head">
                      <div>
                        <p className="ds-mono" style={{ margin: 0, color: "var(--on-surface)" }}>
                          {sub.subscription_code}
                        </p>
                        <p className="ds-body-sm ds-text-muted" style={{ margin: "4px 0 0" }}>
                          {sub.service_name ?? "-"} • {sub.package_name ?? "-"}
                        </p>
                      </div>
                      <div style={{ display: "flex", flexDirection: "column", alignItems: "flex-end", gap: 6 }}>
                        <span
                          className={`ds-chip ${
                            sub.status === "active" ? "ds-chip-online" : "ds-chip-neutral"
                          }`}
                        >
                          {sub.status}
                        </span>
                        <span className={`ds-chip ${sub.has_usage ? "ds-chip-info" : "ds-chip-neutral"}`}>
                          {sub.has_usage ? "Usage Ready" : "Pending"}
                        </span>
                      </div>
                    </div>
                    <div className="ds-list-item-footer">
                      <div style={{ display: "flex", alignItems: "center", gap: 6 }}>
                        <span className="material-symbols-outlined ds-text-muted" style={{ fontSize: 16 }}>
                          calendar_today
                        </span>
                        <span className="ds-body-sm ds-text-muted">
                          Next billing: {sub.next_billing_date ?? "-"}
                        </span>
                      </div>
                      <span className="ds-body-sm" style={{ fontWeight: 600 }}>
                        Rp {sub.effective_price.toLocaleString("id-ID")}
                      </span>
                    </div>
                  </div>
                </Link>
              );
            })}
          </div>
        )}
      </div>
    </>
  );
}

function KpiCard({
  title,
  value,
  tone,
}: {
  title: string;
  value: number;
  tone: "primary" | "secondary" | "tertiary";
}) {
  const color =
    tone === "secondary"
      ? "var(--secondary)"
      : tone === "tertiary"
        ? "var(--tertiary-container)"
        : "var(--primary)";

  return (
    <div className="ds-kpi-card">
      <div className="ds-label-caps ds-text-muted">{title}</div>
      <div className="ds-kpi-value" style={{ color }}>
        {value.toFixed(2)} Mbps
      </div>
    </div>
  );
}
