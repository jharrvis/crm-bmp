<?php

namespace App\Http\Controllers;

use App\Models\Mailbox;
use App\Models\Subscription;
use App\Models\SubscriptionMailHosting;
use App\Jobs\DeleteMailboxJob;
use App\Jobs\ProvisionMailboxJob;
use App\Jobs\SetMailboxStatusJob;
use App\Jobs\SyncZimbraMailboxesJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MailboxController extends Controller
{
    /**
     * Display a listing of mailboxes for a subscription.
     */
    public function index(Subscription $subscription)
    {
        $this->authorize('mailboxes.view');

        $mailHosting = $subscription->mailHosting()->with(['mailServer', 'mailboxes'])->first();

        abort_if(! $mailHosting, 404, 'Langganan ini tidak memiliki layanan mail hosting.');

        return view('mailboxes.index', compact('subscription', 'mailHosting'));
    }

    /**
     * Create a new mailbox on the mail server.
     */
    public function store(Request $request, Subscription $subscription)
    {
        $this->authorize('mailboxes.create');

        $mailHosting = $subscription->mailHosting()->with('mailServer')->first();

        if (! $mailHosting) {
            return back()->with('error', 'Layanan mail hosting tidak ditemukan.');
        }

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6|max:255',
            'display_name' => 'nullable|string|max:255',
            'quota_mb' => 'nullable|integer|min:1',
        ]);

        $email = strtolower($validated['email']);
        $domain = $mailHosting->domain;

        if (! str_ends_with($email, '@'.$domain)) {
            return back()->withErrors(['email' => "Email harus menggunakan domain {$domain}."])->withInput();
        }

        if (Mailbox::where('email', $email)->exists()) {
            return back()->withErrors(['email' => 'Mailbox dengan alamat tersebut sudah ada.'])->withInput();
        }

        $maxMailboxes = $mailHosting->max_mailboxes;

        if ($maxMailboxes > 0 && $mailHosting->mailboxes()->count() >= $maxMailboxes) {
            return back()->with('error', 'Kuota mailbox layanan sudah tercapai (maksimal '.$maxMailboxes.' mailbox).');
        }

        if ($mailHosting->provisioning_status !== 'ready') {
            return back()->with('error', 'Domain mail belum siap diprovisikan. Tunggu proses provisioning selesai.')->withInput();
        }

        try {
            $quota = $validated['quota_mb'] ?? $mailHosting->mailbox_quota_mb;
            if ($mailHosting->mailbox_quota_mb > 0 && $quota > $mailHosting->mailbox_quota_mb) {
                return back()->withErrors(['quota_mb' => 'Kuota mailbox tidak boleh melebihi batas paket.'])->withInput();
            }

            $mailbox = DB::transaction(function () use ($mailHosting, $email, $validated, $quota) {
                return $mailHosting->mailboxes()->create([
                    'email' => $email,
                    'display_name' => $validated['display_name'] ?? null,
                    'password_encrypted' => $validated['password'],
                    'quota_mb' => $quota,
                    'alias_count' => 0,
                    'is_active' => false,
                    'managed_by_crm' => true,
                    'provisioning_status' => 'pending',
                ]);
            });

            ProvisionMailboxJob::dispatch($mailbox->id)->afterCommit();

            return redirect()->route('subscriptions.mailboxes.index', $subscription)
                ->with('success', 'Mailbox '.$email.' masuk antrean provisioning.');
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Gagal menyiapkan mailbox. Silakan coba kembali.');
        }
    }

    /**
     * Import existing Zimbra accounts as local read-only mailbox records.
     */
    public function sync(Subscription $subscription)
    {
        $this->authorize('mailboxes.sync');

        $mailHosting = $subscription->mailHosting()->with('mailServer')->first();

        if (! $mailHosting || ! $mailHosting->mailServer) {
            return back()->with('error', 'Layanan atau server mail hosting tidak ditemukan.');
        }

        SyncZimbraMailboxesJob::dispatch($mailHosting->id, auth()->id())->afterCommit();

        return back()->with('success', 'Sinkronisasi mailbox dari Zimbra masuk antrean. Proses ini hanya membaca Zimbra dan menambahkan data lokal yang belum ada.');
    }

    /**
     * Suspend a mailbox (maintenance mode).
     */
    public function suspend(Subscription $subscription, Mailbox $mailbox)
    {
        $this->authorize('mailboxes.update');

        $mailHosting = $subscription->mailHosting()->with('mailServer')->first();

        if (! $mailHosting || $mailbox->subscription_mail_hosting_id !== $mailHosting->id) {
            return back()->with('error', 'Mailbox tidak ditemukan pada layanan ini.');
        }

        if (! $mailbox->managed_by_crm) {
            return back()->with('error', 'Mailbox hasil sinkronisasi bersifat read-only dan tidak dapat diubah dari CRM.');
        }

        SetMailboxStatusJob::dispatch($mailbox->id, false)->afterCommit();

        return back()->with('success', 'Permintaan menonaktifkan mailbox masuk antrean.');
    }

    /**
     * Reactivate a suspended mailbox.
     */
    public function activate(Subscription $subscription, Mailbox $mailbox)
    {
        $this->authorize('mailboxes.update');

        $mailHosting = $subscription->mailHosting()->with('mailServer')->first();

        if (! $mailHosting || $mailbox->subscription_mail_hosting_id !== $mailHosting->id) {
            return back()->with('error', 'Mailbox tidak ditemukan pada layanan ini.');
        }

        if (! $mailbox->managed_by_crm) {
            return back()->with('error', 'Mailbox hasil sinkronisasi bersifat read-only dan tidak dapat diubah dari CRM.');
        }

        SetMailboxStatusJob::dispatch($mailbox->id, true)->afterCommit();

        return back()->with('success', 'Permintaan mengaktifkan mailbox masuk antrean.');
    }

    /**
     * Remove the mailbox from the mail server.
     */
    public function destroy(Subscription $subscription, Mailbox $mailbox)
    {
        $this->authorize('mailboxes.delete');

        $mailHosting = $subscription->mailHosting()->with('mailServer')->first();

        if (! $mailHosting || $mailbox->subscription_mail_hosting_id !== $mailHosting->id) {
            return back()->with('error', 'Mailbox tidak ditemukan pada layanan ini.');
        }

        if (! $mailbox->managed_by_crm) {
            return back()->with('error', 'Mailbox hasil sinkronisasi bersifat read-only dan tidak dapat dihapus dari CRM.');
        }

        $mailbox->update(['provisioning_status' => 'deleting', 'provisioning_error' => null]);
        DeleteMailboxJob::dispatch($mailbox->id)->afterCommit();

        return back()->with('success', 'Permintaan menghapus mailbox masuk antrean.');
    }
}
