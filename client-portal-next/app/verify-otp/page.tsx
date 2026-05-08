export default async function VerifyOtpPage({
  searchParams,
}: {
  searchParams: Promise<{ email?: string; message?: string; error?: string; lock_email?: string }>;
}) {
  const params = await searchParams;
  const emailValue = params.email ? decodeURIComponent(params.email) : "";
  const lockEmail = params.lock_email === "1";

  return (
    <div className="ds-auth-root">
      <div className="ds-auth-bg-blob-1" />
      <div className="ds-auth-bg-blob-2" />

      <div className="ds-auth-container">
        <div className="ds-auth-brand">
          <div className="ds-auth-logo">
            <span className="material-symbols-outlined">shield_lock</span>
          </div>
          <h1 className="ds-auth-title">Verifikasi OTP</h1>
          <p className="ds-auth-subtitle">Masuk ke portal dengan kode OTP yang valid</p>
        </div>

        <section className="ds-auth-card">
          <form action="/api/auth/verify-otp" method="POST" className="ds-auth-form">
            <div>
              <h2 className="ds-title-sm" style={{ margin: 0 }}>
                Masukkan Kode OTP
              </h2>
              <p className="ds-body-sm ds-text-muted" style={{ margin: "8px 0 0" }}>
                Gunakan kode OTP yang dikirim ke email Anda atau yang diberikan admin CRM.
              </p>
            </div>

            {params.message ? (
              <div className="ds-alert ds-alert-success">{decodeURIComponent(params.message)}</div>
            ) : null}

            {params.error ? (
              <div className="ds-alert ds-alert-error">{decodeURIComponent(params.error)}</div>
            ) : null}

            <div className="ds-field-group">
              <label className="ds-field-label">Email</label>
              <div className="ds-input-wrap">
                <span className="material-symbols-outlined ds-input-icon">mail</span>
                <input
                  className="ds-input with-icon"
                  type="email"
                  name="email"
                  defaultValue={emailValue}
                  placeholder="nama@email.com"
                  required
                  readOnly={lockEmail}
                />
              </div>
              {lockEmail ? (
                <p className="ds-body-sm ds-text-muted" style={{ margin: "6px 0 0" }}>
                  Email dikunci karena halaman ini dibuka dari link verifikasi admin.
                </p>
              ) : null}
            </div>

            <div className="ds-field-group">
              <label className="ds-field-label">Kode OTP</label>
              <div className="ds-input-wrap">
                <span className="material-symbols-outlined ds-input-icon">password</span>
                <input
                  className="ds-input with-icon"
                  type="text"
                  name="otp"
                  placeholder="123456"
                  required
                />
              </div>
            </div>

            <div className="ds-field-group">
              <label className="ds-field-label">Nama Device</label>
              <div className="ds-input-wrap">
                <span className="material-symbols-outlined ds-input-icon">smartphone</span>
                <input
                  className="ds-input with-icon"
                  type="text"
                  name="device_name"
                  defaultValue="Portal Client"
                  required
                />
              </div>
            </div>

            <button className="ds-btn ds-btn-primary" type="submit">
              Verifikasi dan Login
            </button>
          </form>
        </section>

        <div className="ds-auth-footer">
          <div className="ds-auth-links">
            <a href="/login" className="ds-auth-link">
              <span className="material-symbols-outlined">arrow_back</span>
              <span>Kembali ke Login</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  );
}
