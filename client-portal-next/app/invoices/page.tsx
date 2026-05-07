import { PortalShell } from "@/components/portal-shell";
import { crmRequest } from "@/lib/crm-api";
import { requirePortalToken } from "@/lib/session";

type InvoicePayload = {
  data: Array<{
    id: number;
    invoice_number: string;
    invoice_date: string | null;
    due_date: string | null;
    status: string;
    total_amount: number;
    paid_at: string | null;
    items_count: number;
  }>;
};

export default async function InvoicesPage() {
  const token = await requirePortalToken();
  const payload = await crmRequest<InvoicePayload>("/invoices", { token });

  return (
    <PortalShell
      active="invoices"
      title="Invoice & Tagihan"
      subtitle="Ringkasan invoice client yang terhubung langsung ke CRM."
    >
      <section className="card section-pad panel-block">
        <div className="section-header">
          <div>
            <h3 className="section-title">Daftar Tagihan</h3>
            <p className="section-copy">Pantau status invoice dan tanggal jatuh tempo terbaru.</p>
          </div>
          <span className="badge badge-orange">{payload.data.length}</span>
        </div>

        <div className="list">
          {payload.data.length === 0 ? (
            <div className="empty-state">
              <p className="muted small" style={{ margin: 0 }}>
                Belum ada invoice.
              </p>
            </div>
          ) : (
            payload.data.map((invoice) => (
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
                <div className="metric-row">
                  <span className="muted small">{invoice.items_count} item</span>
                  {invoice.paid_at ? <span className="badge badge-green">Paid</span> : null}
                </div>
                <div className="split-metric">
                  <span className="muted small">Total</span>
                  <strong>Rp {invoice.total_amount.toLocaleString("id-ID")}</strong>
                </div>
              </div>
            ))
          )}
        </div>
      </section>
    </PortalShell>
  );
}
