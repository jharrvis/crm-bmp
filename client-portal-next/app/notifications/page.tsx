import { PortalShell } from "@/components/portal-shell";
import { crmRequest } from "@/lib/crm-api";
import { requirePortalToken } from "@/lib/session";

type NotificationPayload = {
  data: Array<{
    id: number;
    type: string;
    title: string;
    message: string;
    read_at: string | null;
    created_at: string | null;
  }>;
};

export default async function NotificationsPage() {
  const token = await requirePortalToken();
  const payload = await crmRequest<NotificationPayload>("/notifications", { token });

  return (
    <PortalShell
      active="notifications"
      title="Notifikasi"
      subtitle="Pusat notifikasi mobile untuk update invoice, tiket, dan informasi layanan."
    >
      <section className="card section-pad panel-block">
        <div className="section-header">
          <div>
            <h3 className="section-title">Pemberitahuan</h3>
            <p className="section-copy">Semua notifikasi terbaru yang masuk ke akun client Anda.</p>
          </div>
          <span className="badge badge-blue">{payload.data.length}</span>
        </div>

        <div className="list">
          {payload.data.length === 0 ? (
            <div className="empty-state">
              <p className="muted small" style={{ margin: 0 }}>
                Belum ada notifikasi.
              </p>
            </div>
          ) : (
            payload.data.map((notification) => (
              <div
                key={notification.id}
                className="list-card"
                style={{ background: notification.read_at ? "rgba(255,255,255,0.94)" : "#eff6ff" }}
              >
                <div className="metric-row">
                  <span className={`badge ${notification.read_at ? "badge-green" : "badge-blue"} badge-dot`}>
                    {notification.read_at ? "Sudah dibaca" : "Baru"}
                  </span>
                  <span className="muted small">{notification.created_at ?? "-"}</span>
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
