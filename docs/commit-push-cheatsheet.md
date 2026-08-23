# Cheatsheet Commit & Push — BMPnet CRM

> 1 halaman ringkas. Sumber kebenaran: `AGENTS.md` §14 Branching & Git, §16 Checklist & Workflow, §17 Production, §22 Graphify + `docs/plans/implementation-workflow-dashboard-notification-map.md` §12 Commit Boundary.
> Jika ragu, urutan: `AGENTS.md` > Workflow > Source Plan > kode.

## 1) Kapan Commit (§16.1)

Commit jika **salah satu** terpenuhi:

1. 1 unit pekerjaan end-to-end selesai
2. 1 bug fix sudah terverifikasi
3. 1 fitur baru sudah bisa dipakai (walau belum 100%)
4. 1 dokumentasi penting sudah lengkap
5. Sebelum pindah ke scope lain yang berbeda

**Hindari:** commit campur banyak topik, commit setengah jadi tanpa konteks, commit file temp/cache/artefak lokal.

## 2) Pesan Commit (§14.3)

- Singkat, jelaskan **hasil** (bukan proses berpikir), 1 scope.
- Contoh: `Add in-app documentation module`, `Implement MVP activity logging`, `Show assigned users in role management`, `Fix invoice deletion handling`.
- Workflow dashboard-notif-map (§12) pakai 7 commit kecil:
  1. `Add notification registry and lifecycle`
  2. `Add dashboard real stats foundation`
  3. `Add operational map client markers`
  4. `Add customizable dashboard preferences`
  5. `Integrate notification widgets`
  6. `Add operational map dashboard widget`
  7. `Add infrastructure map layers`
- Jangan campur migration notifikasi + dashboard UI + map + polygon dalam 1 commit.

## 3) Kapan Push ke GitHub (§16.2)

**Push jika:**
1. Siap diuji di server lain
2. Sudah aman untuk branch aktif
3. Dokumentasi/changelog sudah ikut diperbarui
4. User eksplisit minta commit+push
5. Dibutuhkan untuk deploy production/staging/kolaborasi

**Jangan push jika:**
- Masih eksplorasi belum stabil
- Dampak lintas modul belum dicek (`graphify query` belum)
- Migration/seeder/deploy note belum jelas padahal dibutuhkan
- Masih ada file lokal tidak sengaja berubah

## 4) Urutan Kerja Default (§16.3)

```
1. implement perubahan
2. verifikasi minimum (php -l, npm run build jika UI, composer test jika logic kritis)
3. update docs relevan (docs/modules/*, docs/api/*)
4. update CHANGELOG.md (Added/Changed/Fixed + Deployment Notes)
5. graphify update (cek GRAPH_REPORT.md hash == git rev-parse HEAD)
6. commit pesan jelas
7. push jika siap dibagikan/dideploy
```

## 5) Checklist Sebelum Commit (§16)

- [ ] scope jelas, file tidak relevan tidak ikut (`git status --short`, `git diff --stat`)
- [ ] docs relevan sudah update
- [ ] `CHANGELOG.md` diperbarui jika user rasakan perubahan
- [ ] migration/seeder/deploy note ditulis jika perlu
- [ ] `php -l` / `composer test` minimum sudah dijalankan
- [ ] permission/role diperiksa jika sentuh menu/akses
- [ ] `graphify update` sudah dijalankan, `GRAPH_REPORT.md` fresh

## 6) Checklist Sebelum Push ke Production (§17)

- [ ] branch sudah sinkron (`git fetch`, `git log --oneline -5`)
- [ ] migration yang diperlukan sudah diketahui
- [ ] seeder yang diperlukan sudah diketahui
- [ ] cache clear steps dicatat (`php artisan config:clear`, `view:clear`, `permission:cache-reset`)
- [ ] perubahan env var dicatat (tambah ke `.env.example` dengan placeholder, bukan nilai produksi)
- [ ] risiko force-push dipahami (jangan `git push -f` tanpa komunikasi)

## 7) Branch & Merge (§14.1-14.2)

- Default: `master`
- Feature: `feature/nama-fitur` (contoh `feature/payment-recording`)
- Bugfix: `fix/deskripsi-bug` (contoh `fix/invoice-number-race-condition`)
- Hotfix: langsung ke `master` jika urgent, wajib deployment note
- Merge: squash untuk feature agar history bersih; jangan rebase/force-push branch yang sudah di-push tanpa alasan kuat.

## 8) Approval & Environment (§16.4)

- `AGENTS.md` tidak bisa menonaktifkan prompt approval dari environment/tooling.
- Approval `git commit` / `git push` tetap ikut aturan sandbox/runtime.
- Atur trusted prefix/approval rule di level tool, bukan di repo.

## 9) Quick Command

```bash
# cek
git status --short
git diff --stat
git diff --check
php -l app/Http/Controllers/NamaController.php
composer test
graphify update
cat graphify-out/GRAPH_REPORT.md | grep "Graph Freshness"

# commit & push
git add <file relevan saja>
git commit -m "Add dashboard real stats foundation"
git push origin <branch>
```

> Simpan file ini di `docs/` agar terbaca di UI Dokumentasi (sumber `docs/` — `AGENTS.md` §3.1.1).
