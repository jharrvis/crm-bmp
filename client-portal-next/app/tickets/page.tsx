import { redirect } from "next/navigation";
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
    subscription?: {
      id: number;
      subscription_code: string;
      package_name?: string | null;
      service_name?: string | null;
    } | null;
  }>;
};

type SubscriptionPayload = {
  data: Array<{
    id: number;
    subscription_code: string;
    package_name?: string | null;
    service_name?: string | null;
  }>;
};

async function createTicketAction(formData: FormData) {
  "use server";

  const token = await requirePortalToken();

  const subscriptionId = String(formData.get("subscription_id") ?? "").trim();
  const subject = String(formData.get("subject") ?? "").trim();
  const category = String(formData.get("category") ?? "technical").trim();
  const priority = String(formData.get("priority") ?? "normal").trim();
  const message = String(formData.get("message") ?? "").trim();

  try {
    await crmRequest("/tickets", {
      method: "POST",
      token,
      body: {
        subscription_id: subscriptionId ? Number(subscriptionId) : null,
        subject,
        category,
        priority,
        message,
      },
    });
  } catch (error) {
    const errorMessage =
      error instanceof Error ? error.message : "Gagal membuat tiket.";

    redirect(`/tickets?error=${encodeURIComponent(errorMessage)}`);
  }

  redirect("/tickets?created=1");
}

export default async function TicketsPage({
  searchParams,
}: {
  searchParams: Promise<{ created?: string; error?: string }>;
}) {
  const params = await searchParams;
  const token = await requirePortalToken();
  const [payload, subscriptions] = await Promise.all([
    crmRequest<TicketPayload>("/tickets", { token }),
    crmRequest<SubscriptionPayload>("/subscriptions", { token }),
  ]);

  const created = params.created === "1";
  const error = params.error?.trim();

  return (
    <PortalShell
      active="tickets"
      title="Tiket Support"
      subtitle="Buat tiket baru dan pantau tindak lanjut support CRM."
    >
      <section className="card section-pad panel-block">
        <div className="section-header">
          <div>
            <h3 className="section-title">Buat Ticket Baru</h3>
            <p className="section-copy">
              Laporkan gangguan koneksi, pertanyaan billing, atau kebutuhan bantuan teknis lainnya.
            </p>
          </div>
        </div>

        {created ? (
          <div className="alert alert-success" style={{ marginBottom: 16 }}>
            Tiket berhasil dibuat. Tim support akan menindaklanjuti secepatnya.
          </div>
        ) : null}

        {error ? (
          <div className="alert alert-error" style={{ marginBottom: 16 }}>
            {error}
          </div>
        ) : null}

        <form action={createTicketAction} style={{ display: "grid", gap: 14 }}>
          <div className="field-group">
            <label className="field-label">Layanan Terkait</label>
            <select name="subscription_id" className="select">
              <option value="">Pilih jika terkait layanan tertentu</option>
              {subscriptions.data.map((subscription) => (
                <option key={subscription.id} value={subscription.id}>
                  {subscription.subscription_code}{" "}
                  {subscription.package_name ? `| ${subscription.package_name}` : ""}
                </option>
              ))}
            </select>
          </div>

          <div style={{ display: "grid", gap: 14, gridTemplateColumns: "repeat(2, minmax(0, 1fr))" }}>
            <div className="field-group">
              <label className="field-label">Kategori</label>
              <select name="category" className="select" defaultValue="technical">
                <option value="connectivity">Connectivity</option>
                <option value="billing">Billing</option>
                <option value="technical">Technical</option>
                <option value="general">General</option>
              </select>
            </div>
            <div className="field-group">
              <label className="field-label">Priority</label>
              <select name="priority" className="select" defaultValue="normal">
                <option value="low">Low</option>
                <option value="normal">Normal</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
          </div>

          <div className="field-group">
            <label className="field-label">Subjek</label>
            <input
              type="text"
              name="subject"
              className="input"
              placeholder="Contoh: Internet down sejak pagi"
              required
            />
          </div>

          <div className="field-group">
            <label className="field-label">Pesan</label>
            <textarea
              name="message"
              className="textarea"
              rows={4}
              placeholder="Jelaskan kronologi, lokasi, indikator modem/router, atau kebutuhan bantuan Anda."
              required
            />
          </div>

          <button type="submit" className="button button-primary" style={{ width: "100%" }}>
            Kirim Ticket
          </button>
        </form>
      </section>

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
                    {ticket.subscription ? (
                      <p className="list-subtitle" style={{ marginTop: 6 }}>
                        {ticket.subscription.subscription_code}
                      </p>
                    ) : null}
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
