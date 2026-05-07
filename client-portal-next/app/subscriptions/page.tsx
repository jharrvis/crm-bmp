import { PortalShell } from "@/components/portal-shell";
import { crmRequest } from "@/lib/crm-api";
import { requirePortalToken } from "@/lib/session";

type SubscriptionListPayload = {
  data: Array<{
    id: number;
    subscription_code: string;
    status: string;
    package_name: string | null;
    service_name: string | null;
    next_billing_date: string | null;
    effective_price: number;
    has_usage: boolean;
  }>;
};

export default async function SubscriptionsPage() {
  const token = await requirePortalToken();
  const payload = await crmRequest<SubscriptionListPayload>("/subscriptions", { token });

  return (
    <PortalShell
      active="subscriptions"
      title="Usage Monitoring"
      subtitle="Daftar langganan yang siap dipantau dari portal client."
    >
      <section className="card section-pad panel-block">
        <div className="section-header">
          <div>
            <h3 className="section-title">Daftar Langganan</h3>
            <p className="section-copy">Pilih layanan yang sudah memiliki koneksi usage monitoring.</p>
          </div>
          <span className="badge badge-blue">{payload.data.length}</span>
        </div>

        <div className="list">
          {payload.data.length === 0 ? (
            <div className="empty-state">
              <p className="muted small" style={{ margin: 0 }}>
                Belum ada data langganan.
              </p>
            </div>
          ) : (
            payload.data.map((subscription) => (
              <div key={subscription.id} className="list-card">
                <div className="list-head">
                  <div>
                    <p className="list-title">
                      <strong>{subscription.subscription_code}</strong>
                    </p>
                    <p className="list-subtitle">
                      {subscription.service_name ?? "-"} • {subscription.package_name ?? "-"}
                    </p>
                  </div>
                  <span className={`badge ${subscription.has_usage ? "badge-green" : "badge-orange"}`}>
                    {subscription.has_usage ? "Ready" : "Pending"}
                  </span>
                </div>
                <div className="metric-row">
                  <span className="badge badge-blue">{subscription.status}</span>
                  <span className="muted small">
                    Rp {subscription.effective_price.toLocaleString("id-ID")}
                  </span>
                </div>
                <div className="split-metric">
                  <span className="muted small">Next billing</span>
                  <strong>{subscription.next_billing_date ?? "-"}</strong>
                </div>
              </div>
            ))
          )}
        </div>
      </section>
    </PortalShell>
  );
}
