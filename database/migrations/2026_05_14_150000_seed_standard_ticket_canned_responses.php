<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $templates = [
            [
                'title' => 'Acknowledgement Umum',
                'slug' => 'general-acknowledgement',
                'category' => 'general',
                'message' => "Halo {{client_name}},\n\nTerima kasih, laporan Anda untuk ticket {{ticket_number}} sudah kami terima.\n\nSaat ini ticket sedang kami teruskan ke tim {{queue_name}} untuk proses tindak lanjut. Jika ada informasi tambahan, silakan balas melalui portal atau kanal komunikasi resmi BMPnet.\n\nSalam,\nTim Support BMPnet",
                'sort_order' => 10,
            ],
            [
                'title' => 'Permintaan Detail Tambahan',
                'slug' => 'general-request-more-details',
                'category' => 'general',
                'message' => "Halo {{client_name}},\n\nAgar pengecekan untuk ticket {{ticket_number}} bisa kami percepat, mohon bantu informasikan detail tambahan berikut:\n- Jam mulai kendala\n- Lokasi/perangkat yang terdampak\n- Screenshot/foto error bila ada\n- Apakah kendala terjadi ke semua user/perangkat\n\nSetelah data diterima, tim kami akan lanjutkan analisa.\n\nSalam,\nTim Support BMPnet",
                'sort_order' => 20,
            ],
            [
                'title' => 'Follow Up Menunggu Konfirmasi Client',
                'slug' => 'general-awaiting-client-confirmation',
                'category' => 'general',
                'message' => "Halo {{client_name}},\n\nKami sedang menunggu konfirmasi tambahan dari sisi Anda untuk ticket {{ticket_number}}.\n\nMohon bantu update kondisi terbaru agar penanganan bisa kami lanjutkan secepatnya.\n\nSalam,\nTim Support BMPnet",
                'sort_order' => 30,
            ],
            [
                'title' => 'Jadwal Kunjungan Onsite',
                'slug' => 'general-onsite-schedule',
                'category' => 'general',
                'message' => "Halo {{client_name}},\n\nBerdasarkan hasil pengecekan awal ticket {{ticket_number}}, penanganan lanjutan kemungkinan perlu dilakukan melalui kunjungan onsite.\n\nMohon bantu konfirmasi PIC di lokasi ({{primary_contact_name}} / {{primary_contact_phone}}) dan waktu yang paling memungkinkan untuk tim kami datang.\n\nSalam,\nTim Support BMPnet",
                'sort_order' => 40,
            ],
            [
                'title' => 'Koneksi Down Sedang Investigasi NOC',
                'slug' => 'connectivity-down-investigation',
                'category' => 'connectivity',
                'message' => "Halo {{client_name}},\n\nTicket {{ticket_number}} untuk layanan {{subscription_code}} / {{package_name}} sudah masuk ke tim {{queue_name}}.\n\nSaat ini kami sedang melakukan pengecekan kondisi koneksi dari sisi monitoring dan perangkat terkait. Jika ada perkembangan, kami akan update kembali melalui ticket ini.\n\nSalam,\nTim NOC BMPnet",
                'sort_order' => 100,
            ],
            [
                'title' => 'Minta Restart Router/ONT',
                'slug' => 'connectivity-request-router-restart',
                'category' => 'connectivity',
                'message' => "Halo {{client_name}},\n\nMohon bantu lakukan restart pada perangkat koneksi di lokasi Anda terlebih dahulu.\n\nInformasi layanan yang kami catat:\n- Subscription: {{subscription_code}}\n- IP Address: {{ip_address}}\n- PPPoE User: {{pppoe_user}}\n- Router/ONT: {{router_model}}\n\nSetelah restart selesai, mohon konfirmasi kembali hasilnya melalui ticket ini.\n\nSalam,\nTim NOC BMPnet",
                'sort_order' => 110,
            ],
            [
                'title' => 'Indikasi Gangguan Jalur/Fiber',
                'slug' => 'connectivity-fiber-path-issue',
                'category' => 'connectivity',
                'message' => "Halo {{client_name}},\n\nDari hasil analisa awal ticket {{ticket_number}}, terdapat indikasi gangguan pada sisi jalur/fiber/backhaul layanan Anda.\n\nTim kami sedang melakukan koordinasi lanjutan agar proses pemulihan dapat diselesaikan secepat mungkin. Kami akan update kembali setelah ada progres terbaru.\n\nSalam,\nTim NOC BMPnet",
                'sort_order' => 120,
            ],
            [
                'title' => 'Latency / Packet Loss Monitoring',
                'slug' => 'connectivity-latency-packetloss',
                'category' => 'connectivity',
                'message' => "Halo {{client_name}},\n\nKami telah menerima laporan terkait latency/packet loss pada layanan {{subscription_code}}.\n\nTim kami sedang melakukan monitoring trafik dan performa koneksi untuk mengidentifikasi sumber gangguan. Apabila Anda memiliki contoh jam kejadian paling terasa, mohon bantu informasikan agar analisa kami lebih terarah.\n\nSalam,\nTim NOC BMPnet",
                'sort_order' => 130,
            ],
            [
                'title' => 'Maintenance / Window Pekerjaan',
                'slug' => 'connectivity-maintenance-window',
                'category' => 'connectivity',
                'message' => "Halo {{client_name}},\n\nKami informasikan bahwa ticket {{ticket_number}} sedang ditangani dalam window pekerjaan terjadwal oleh tim {{queue_name}}.\n\nApabila selama proses ini ada gangguan sementara pada layanan {{subscription_code}}, mohon menunggu sampai pekerjaan selesai. Kami akan menginformasikan hasil akhirnya melalui ticket ini.\n\nSalam,\nTim NOC BMPnet",
                'sort_order' => 140,
            ],
            [
                'title' => 'Speed Test dan Verifikasi Teknis',
                'slug' => 'technical-speed-test-check',
                'category' => 'technical',
                'message' => "Halo {{client_name}},\n\nUntuk membantu analisa ticket {{ticket_number}}, mohon bantu lakukan pengujian berikut:\n- Speedtest menggunakan kabel LAN bila memungkinkan\n- Catat jam pengujian\n- Screenshot hasil speedtest\n- Informasikan perangkat yang digunakan\n\nData langganan tercatat:\n- Service: {{service_name}}\n- Package: {{package_name}}\n- Effective price: {{effective_price}}\n\nSalam,\nTim Technical Support BMPnet",
                'sort_order' => 200,
            ],
            [
                'title' => 'Pengecekan Konfigurasi Perangkat',
                'slug' => 'technical-device-config-check',
                'category' => 'technical',
                'message' => "Halo {{client_name}},\n\nKami sedang memeriksa kemungkinan kendala konfigurasi di perangkat layanan Anda.\n\nInformasi yang terdata pada sistem kami:\n- Subscription: {{subscription_code}}\n- IP Address: {{ip_address}}\n- PPPoE User: {{pppoe_user}}\n- Router/ONT: {{router_model}}\n- Host Monitoring: {{zabbix_host_name}}\n\nBila ada perubahan perangkat di sisi pelanggan, mohon bantu informasikan.\n\nSalam,\nTim Technical Support BMPnet",
                'sort_order' => 210,
            ],
            [
                'title' => 'Gangguan WiFi / Jaringan Lokal',
                'slug' => 'technical-wifi-local-network',
                'category' => 'technical',
                'message' => "Halo {{client_name}},\n\nDari gejala awal ticket {{ticket_number}}, kendala kemungkinan berada pada jaringan lokal/WiFi internal di lokasi.\n\nMohon bantu cek:\n- Apakah kendala terjadi pada semua perangkat?\n- Apakah koneksi via kabel LAN juga bermasalah?\n- Apakah ada perubahan konfigurasi jaringan internal baru-baru ini?\n\nJika perlu, tim kami dapat bantu arahkan langkah pengecekan lanjutan.\n\nSalam,\nTim Technical Support BMPnet",
                'sort_order' => 220,
            ],
            [
                'title' => 'Migrasi / Upgrade Layanan',
                'slug' => 'technical-upgrade-migration',
                'category' => 'technical',
                'message' => "Halo {{client_name}},\n\nPermintaan upgrade/migrasi untuk layanan {{subscription_code}} sedang kami proses.\n\nDetail layanan saat ini:\n- Service: {{service_name}}\n- Package aktif: {{package_name}}\n- Tanggal instalasi: {{installed_at}}\n\nTim kami akan menginformasikan jadwal dan langkah selanjutnya setelah proses administrasi dan teknis siap.\n\nSalam,\nTim Technical Support BMPnet",
                'sort_order' => 230,
            ],
            [
                'title' => 'Permintaan Salinan Invoice',
                'slug' => 'billing-invoice-copy',
                'category' => 'billing',
                'message' => "Halo {{client_name}},\n\nPermintaan salinan invoice untuk layanan {{subscription_code}} sudah kami terima.\n\nTim billing sedang menyiapkan dokumen yang diperlukan dan akan mengirimkan update melalui ticket {{ticket_number}} ini.\n\nSalam,\nTim Billing BMPnet",
                'sort_order' => 300,
            ],
            [
                'title' => 'Verifikasi Pembayaran',
                'slug' => 'billing-payment-verification',
                'category' => 'billing',
                'message' => "Halo {{client_name}},\n\nBukti pembayaran Anda sedang kami verifikasi.\n\nMohon pastikan informasi berikut sudah sesuai saat mengirimkan bukti:\n- Nama pelanggan: {{client_name}}\n- Kode pelanggan: {{client_code}}\n- Subscription: {{subscription_code}}\n- Tanggal billing berikutnya: {{next_billing_date}}\n\nSetelah verifikasi selesai, status layanan akan kami update.\n\nSalam,\nTim Billing BMPnet",
                'sort_order' => 310,
            ],
            [
                'title' => 'Tagihan Jatuh Tempo',
                'slug' => 'billing-overdue-followup',
                'category' => 'billing',
                'message' => "Halo {{client_name}},\n\nKami menginformasikan bahwa terdapat tindak lanjut terkait tagihan untuk layanan {{subscription_code}}.\n\nMohon bantu cek status pembayaran Anda. Jika pembayaran sudah dilakukan, silakan kirim bukti transfer agar dapat kami verifikasi. Jika belum, mohon lakukan pembayaran sesuai jadwal billing.\n\nSalam,\nTim Billing BMPnet",
                'sort_order' => 320,
            ],
            [
                'title' => 'Permintaan Dokumen Billing / Pajak',
                'slug' => 'billing-tax-document-request',
                'category' => 'billing',
                'message' => "Halo {{client_name}},\n\nPermintaan dokumen billing/pajak untuk ticket {{ticket_number}} sedang kami proses.\n\nApabila ada format dokumen atau data khusus yang dibutuhkan, mohon bantu informasikan agar tim billing dapat menyiapkannya dengan tepat.\n\nSalam,\nTim Billing BMPnet",
                'sort_order' => 330,
            ],
            [
                'title' => 'Jadwal Aktivasi / Provisioning',
                'slug' => 'provisioning-activation-schedule',
                'category' => 'general',
                'message' => "Halo {{client_name}},\n\nPermintaan aktivasi/provisioning untuk layanan {{subscription_code}} telah masuk ke tim {{queue_name}}.\n\nSaat ini kami sedang menjadwalkan proses lanjutan dan akan menghubungi PIC {{primary_contact_name}} di {{primary_contact_phone}} bila diperlukan koordinasi tambahan.\n\nSalam,\nTim Provisioning BMPnet",
                'sort_order' => 400,
            ],
            [
                'title' => 'Menunggu Kesiapan Lokasi',
                'slug' => 'provisioning-awaiting-site-readiness',
                'category' => 'general',
                'message' => "Halo {{client_name}},\n\nUntuk menindaklanjuti ticket {{ticket_number}}, saat ini kami menunggu konfirmasi kesiapan lokasi dari sisi pelanggan.\n\nMohon bantu informasikan bila lokasi, listrik, rack/perangkat, dan PIC onsite sudah siap agar tim kami dapat menjadwalkan langkah berikutnya.\n\nSalam,\nTim Provisioning BMPnet",
                'sort_order' => 410,
            ],
            [
                'title' => 'Normal Kembali / Minta Konfirmasi',
                'slug' => 'general-resolved-confirmation',
                'category' => 'general',
                'message' => "Halo {{client_name}},\n\nDari hasil pengecekan terakhir, layanan {{subscription_code}} saat ini terindikasi sudah kembali normal.\n\nMohon bantu konfirmasi apakah koneksi di sisi Anda juga sudah berjalan baik. Jika masih ada kendala, silakan informasikan detail terbarunya melalui ticket {{ticket_number}}.\n\nSalam,\nTim Support BMPnet",
                'sort_order' => 500,
            ],
        ];

        foreach ($templates as $template) {
            $exists = DB::table('ticket_canned_responses')
                ->where('slug', $template['slug'])
                ->exists();

            if ($exists) {
                DB::table('ticket_canned_responses')
                    ->where('slug', $template['slug'])
                    ->update([
                        'title' => $template['title'],
                        'category' => $template['category'],
                        'message' => $template['message'],
                        'is_active' => true,
                        'sort_order' => $template['sort_order'],
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table('ticket_canned_responses')->insert([
                    'title' => $template['title'],
                    'slug' => $template['slug'],
                    'category' => $template['category'],
                    'message' => $template['message'],
                    'is_active' => true,
                    'sort_order' => $template['sort_order'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('ticket_canned_responses')->whereIn('slug', [
            'general-acknowledgement',
            'general-request-more-details',
            'general-awaiting-client-confirmation',
            'general-onsite-schedule',
            'connectivity-down-investigation',
            'connectivity-request-router-restart',
            'connectivity-fiber-path-issue',
            'connectivity-latency-packetloss',
            'connectivity-maintenance-window',
            'technical-speed-test-check',
            'technical-device-config-check',
            'technical-wifi-local-network',
            'technical-upgrade-migration',
            'billing-invoice-copy',
            'billing-payment-verification',
            'billing-overdue-followup',
            'billing-tax-document-request',
            'provisioning-activation-schedule',
            'provisioning-awaiting-site-readiness',
            'general-resolved-confirmation',
        ])->delete();
    }
};
