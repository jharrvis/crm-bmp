export default async function LoginPage({
  searchParams,
}: {
  searchParams: Promise<{ error?: string; email?: string }>;
}) {
  const params = await searchParams;

  return (
    <div className="ds-auth-root">
      <div className="ds-auth-bg-blob-1" />
      <div className="ds-auth-bg-blob-2" />

      <div className="ds-auth-container">
        <div className="ds-auth-brand">
          <div className="ds-auth-logo">
            <span className="material-symbols-outlined">signal_cellular_alt</span>
          </div>
          <h1 className="ds-auth-title">
            BMP<span style={{ color: "var(--primary-container)" }}>net</span>
          </h1>
          <p className="ds-auth-subtitle">Portal Client ISP Management</p>
        </div>

        <section className="ds-auth-card">
          <form action="/api/auth/request-otp" method="POST" className="ds-auth-form">
            <div>
              <h2 className="ds-title-sm" style={{ margin: 0 }}>
                Login Portal
              </h2>
              <p className="ds-body-sm ds-text-muted" style={{ margin: "8px 0 0" }}>
                Masukkan email terdaftar untuk menerima kode OTP.
              </p>
            </div>

            <div className="ds-field-group">
              <label className="ds-field-label">Email</label>
              <div className="ds-input-wrap">
                <span className="material-symbols-outlined ds-input-icon">mail</span>
                <input
                  className="ds-input with-icon"
                  type="email"
                  name="email"
                  defaultValue={params.email ? decodeURIComponent(params.email) : ""}
                  placeholder="nama@email.com"
                  required
                />
              </div>
            </div>

            <button className="ds-btn ds-btn-primary" type="submit">
              Kirim OTP
            </button>

            {params.error ? (
              <div className="ds-alert ds-alert-error">{decodeURIComponent(params.error)}</div>
            ) : null}
          </form>
        </section>

        <div className="ds-auth-footer">
          <div className="ds-auth-links">
            <a href="/verify-otp" className="ds-auth-link">
              <span className="material-symbols-outlined">key</span>
              <span>Saya Sudah Punya OTP</span>
            </a>
          </div>

          <div className="ds-security-badge">
            <span className="material-symbols-outlined ds-text-primary">verified_user</span>
            <span className="ds-body-sm ds-text-muted">Akses aman via OTP Portal Client</span>
          </div>
        </div>
      </div>
    </div>
  );
}
