import Link from "next/link";

export default function NotFound() {
  return (
    <div className="app-root">
      <div className="phone-frame">
        <div className="screen auth-screen">
          <div className="auth-body" style={{ gridTemplateColumns: "1fr" }}>
            <section className="card card-strong section-pad">
              <div className="badge badge-orange">404</div>
              <h1 style={{ margin: "18px 0 8px", fontSize: 30 }}>Halaman tidak ditemukan</h1>
              <p className="muted small">
                Halaman yang Anda cari tidak tersedia di portal client.
              </p>
              <div style={{ marginTop: 20 }}>
                <Link href="/login" className="screen-link">
                  Kembali ke login
                </Link>
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>
  );
}
