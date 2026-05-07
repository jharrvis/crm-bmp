import { PortalShell } from "@/components/portal-shell";
import { crmRequest } from "@/lib/crm-api";
import { requirePortalToken } from "@/lib/session";

type TicketPayload = {
  data: Array<{
    id: number;
    ticket_number: string;
    subject: string;
    category: string;
    priority: string;
    status: string;
    created_at: string | null;
  }>;
};

export default async function TicketsPage() {
  const token = await requirePortalToken();
  const payload = await crmRequest<TicketPayload>("/tickets", { token });

  return (
    <PortalShell
      active="tickets"
      title="Tiket Support"
      subtitle="Daftar tiket client yang terhubung ke thread support CRM."
    >
      <section className="card section-pad panel-block">
        <div className="section-header">
          <div>
            <h3 className="section-title">Thread Support</h3>
            <p className="section-copy">Keluhan dan tindak lanjut support yang sedang berjalan.</p>
          </div>
          <span className="badge badge-blue">{payload.data.length}</span>
        </div>

        <div className="list">
          {payload.data.length === 0 ? (
            <div className="empty-state">
              <p className="muted small" style={{ margin: 0 }}>
                Belum ada tiket support.
              </p>
            </div>
          ) : (
            payload.data.map((ticket) => (
              <div key={ticket.id} className="list-card">
                <div className="list-head">
                  <div>
                    <p className="list-title">
                      <strong>{ticket.ticket_number}</strong>
                    </p>
                    <p style={{ margin: "8px 0 0", fontWeight: 700, lineHeight: 1.5 }}>{ticket.subject}</p>
                    <p className="list-subtitle">
                      {ticket.category} • Priority {ticket.priority}
                    </p>
                  </div>
                  <span className="badge badge-blue">{ticket.status}</span>
                </div>
                <div className="split-metric">
                  <span className="muted small">Dibuat</span>
                  <strong>{ticket.created_at ?? "-"}</strong>
                </div>
              </div>
            ))
          )}
        </div>
      </section>
    </PortalShell>
  );
}
