import { PortalShell } from "@/components/portal-shell";
import { crmRequest } from "@/lib/crm-api";
import { requirePortalToken } from "@/lib/session";
import type { DashboardPayload } from "@/lib/types";

export default async function DashboardPage() {
  const token = await requirePortalToken();
  const payload = await crmRequest<DashboardPayload>("/dashboard", { token });

  return (
    <PortalShell
      active="dashboard"
      title={payload.client.name}
      subtitle={`${payload.client.client_code} • Status ${payload.client.status}`}
      action={
        <form action="/api/auth/logout" method="POST">
          <button className="button button-ghost" type="submit">
            Logout
          </button>
        </form>
      }
    >
      <section className="hero-card card">
        <div className="hero-kicker">Ringkasan Hari Ini</div>
        <h2 className="hero-title">{payload.summary.active_subscriptions_count}</h2>
        <p className="hero-copy">
          Langganan aktif siap dipantau langsung dari portal client BMPnet.
        </p>
      </section>

      <section className="stat-grid">
        <SummaryCard
          title="Langganan"
          value={payload.summary.active_subscriptions_count}
          tone="blue"
          caption="Layanan aktif"
        />
        <SummaryCard
          title="Invoice"
          value={payload.summary.unpaid_invoices_count}
          tone="orange"
          caption="Belum lunas"
        />
        <SummaryCard
          title="Tiket"
          value={payload.summary.open_tickets_count}
          tone="blue"
          caption="Masih terbuka"
        />
        <SummaryCard
          title="Notif"
          value={payload.summary.unread_notifications_count}
          tone="green"
          caption="Belum dibaca"
        />
      </section>

      <section className="card section-pad panel-block">
        <div className="section-header">
          <div>
            <h3 className="section-title">Invoice Terbaru</h3>
            <p className="section-copy">Tagihan terbaru yang tersambung dari CRM.</p>
          </div>
          <span className="badge badge-orange">{payload.recent_invoices.length}</span>
        </div>
        <div className="list">
          {payload.recent_invoices.length === 0 ? (
            <div className="empty-state">
              <p className="muted small" style={{ margin: 0 }}>
                Belum ada invoice.
              </p>
            </div>
          ) : (
            payload.recent_invoices.map((invoice) => (
              <div key={invoice.id} className="list-card">
                <div className="list-head">
                  <div>
                    <p className="list-title">
                      <strong>{invoice.invoice_number}</strong>
                    </p>
                    <p className="list-subtitle">
                      Inv: {invoice.invoice_date ?? "-"} • Due: {invoice.due_date ?? "-"}
                    </p>
                  </div>
                  <span className={`badge ${invoice.status === "paid" ? "badge-green" : "badge-orange"}`}>
                    {invoice.status}
                  </span>
                </div>
                <div className="split-metric">
                  <span className="muted small">Total tagihan</span>
                  <strong>Rp {invoice.total_amount.toLocaleString("id-ID")}</strong>
                </div>
              </div>
            ))
          )}
        </div>
      </section>

      <section className="card section-pad panel-block">
        <div className="section-header">
          <div>
            <h3 className="section-title">Notifikasi Terbaru</h3>
            <p className="section-copy">Update invoice, tiket, dan informasi layanan.</p>
          </div>
          <span className="badge badge-blue">{payload.recent_notifications.length}</span>
        </div>
        <div className="list">
          {payload.recent_notifications.length === 0 ? (
            <div className="empty-state">
              <p className="muted small" style={{ margin: 0 }}>
                Belum ada notifikasi.
              </p>
            </div>
          ) : (
            payload.recent_notifications.map((notification) => (
              <div
                key={notification.id}
                className="list-card"
                style={{ background: notification.read_at ? "rgba(255,255,255,0.94)" : "#eff6ff" }}
              >
                <div className="top-chip-row">
                  <span className={`badge ${notification.read_at ? "badge-green" : "badge-blue"} badge-dot`}>
                    {notification.read_at ? "Sudah dibaca" : "Baru"}
                  </span>
                </div>
                <p className="list-title">
                  <strong>{notification.title}</strong>
                </p>
                <p className="list-subtitle">{notification.message}</p>
              </div>
            ))
          )}
        </div>
      </section>
    </PortalShell>
  );
}

function SummaryCard({
  title,
  value,
  tone,
  caption,
}: {
  title: string;
  value: number;
  tone: "blue" | "orange" | "green";
  caption: string;
}) {
  const className =
    tone === "green" ? "badge-green" : tone === "orange" ? "badge-orange" : "badge-blue";

  return (
    <div className="stat-card">
      <div className="stat-meta">
        <div className={`badge ${className}`}>{title}</div>
      </div>
      <div className="stat-value">{value}</div>
      <div className="stat-caption">{caption}</div>
    </div>
  );
}
