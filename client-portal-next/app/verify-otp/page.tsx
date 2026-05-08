export default async function VerifyOtpPage({
  searchParams,
}: {
  searchParams: Promise<{ email?: string; message?: string; error?: string }>;
}) {
  const params = await searchParams;

  return (
    <div className="app-root">
      <div className="phone-frame">
        <div className="screen auth-screen">
          <div className="auth-body" style={{ gridTemplateColumns: "1fr", maxWidth: 420, margin: "0 auto" }}>
            <section className="card card-strong section-pad">
              <div className="auth-logo-block">
                <div className="auth-logo-icon">B</div>
                <div>
                  <p className="brand-eyebrow" style={{ margin: 0 }}>
                    Verifikasi OTP
                  </p>
                  <h1 className="auth-heading">Masukkan kode</h1>
                </div>
              </div>

              <p className="auth-subcopy" style={{ marginTop: 8 }}>
                Gunakan kode OTP yang dikirim ke email Anda atau yang diberikan admin CRM untuk masuk ke portal.
              </p>

              {params.message ? (
                <div className="alert alert-success" style={{ marginTop: 18 }}>
                  {decodeURIComponent(params.message)}
                </div>
              ) : null}

              {params.error ? (
                <div className="alert alert-error" style={{ marginTop: 18 }}>
                  {decodeURIComponent(params.error)}
                </div>
              ) : null}

              <form action="/api/auth/verify-otp" method="POST" style={{ marginTop: 24 }}>
                <div className="stack">
                  <div className="field-group">
                    <label className="field-label">Email</label>
                    <div className="input-wrap">
                      <svg className="input-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path
                          d="M4 7.5h16M5.2 6h13.6A1.2 1.2 0 0 1 20 7.2v9.6a1.2 1.2 0 0 1-1.2 1.2H5.2A1.2 1.2 0 0 1 4 16.8V7.2A1.2 1.2 0 0 1 5.2 6Z"
                          fill="none"
                          stroke="currentColor"
                          strokeWidth="1.7"
                          strokeLinecap="round"
                          strokeLinejoin="round"
                        />
                        <path
                          d="m5 8 7 5 7-5"
                          fill="none"
                          stroke="currentColor"
                          strokeWidth="1.7"
                          strokeLinecap="round"
                          strokeLinejoin="round"
                        />
                      </svg>
                      <input
                        className="input with-icon"
                        type="email"
                        name="email"
                        defaultValue={params.email ?? ""}
                        placeholder="nama@email.com"
                        required
                      />
                    </div>
                  </div>
                  <div className="field-group">
                    <label className="field-label">Kode OTP</label>
                    <div className="input-wrap">
                      <svg className="input-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <rect
                          x="4"
                          y="5"
                          width="16"
                          height="14"
                          rx="3"
                          fill="none"
                          stroke="currentColor"
                          strokeWidth="1.7"
                        />
                        <path
                          d="M8 10h8M8 14h4"
                          fill="none"
                          stroke="currentColor"
                          strokeWidth="1.7"
                          strokeLinecap="round"
                        />
                      </svg>
                      <input
                        className="input with-icon"
                        type="text"
                        name="otp"
                        placeholder="123456"
                        required
                      />
                    </div>
                  </div>
                  <div className="field-group">
                    <label className="field-label">Nama Device</label>
                    <div className="input-wrap">
                      <svg className="input-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <rect
                          x="6"
                          y="4"
                          width="12"
                          height="16"
                          rx="3"
                          fill="none"
                          stroke="currentColor"
                          strokeWidth="1.7"
                        />
                        <path
                          d="M10 7h4M9 17h6"
                          fill="none"
                          stroke="currentColor"
                          strokeWidth="1.7"
                          strokeLinecap="round"
                        />
                      </svg>
                      <input
                        className="input with-icon"
                        type="text"
                        name="device_name"
                        defaultValue="Portal Client"
                        required
                      />
                    </div>
                  </div>
                </div>
                <button className="button button-primary" type="submit" style={{ marginTop: 18 }}>
                  Verifikasi dan Login
                </button>
              </form>
            </section>
          </div>
        </div>
      </div>
    </div>
  );
}
