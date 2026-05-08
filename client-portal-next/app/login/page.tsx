export default async function LoginPage({
  searchParams,
}: {
  searchParams: Promise<{ error?: string; email?: string }>;
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
                    ISP Management
                  </p>
                  <h1 className="auth-heading">
                    BMP<span style={{ color: "#2563eb" }}>net</span>
                  </h1>
                </div>
              </div>

              <p className="auth-subcopy" style={{ marginTop: 8 }}>
                Masukkan email terdaftar untuk menerima kode OTP.
              </p>

              <form action="/api/auth/request-otp" method="POST" style={{ marginTop: 24 }}>
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
                      defaultValue={params.email ? decodeURIComponent(params.email) : ""}
                      placeholder="nama@email.com"
                      required
                    />
                  </div>
                </div>

                <button className="button button-primary" type="submit" style={{ marginTop: 18 }}>
                  Kirim OTP
                </button>
              </form>

              <div style={{ marginTop: 14, textAlign: "center" }}>
                <a
                  href="/verify-otp"
                  className="small"
                  style={{ color: "#2563eb", fontWeight: 800 }}
                >
                  Saya sudah punya OTP
                </a>
              </div>

              {params.error ? (
                <div className="alert alert-error" style={{ marginTop: 18 }}>
                  {decodeURIComponent(params.error)}
                </div>
              ) : null}
            </section>
          </div>
        </div>
      </div>
    </div>
  );
}
