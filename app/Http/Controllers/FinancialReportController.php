<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:financial_reports.view');
    }

    public function index(Request $request)
    {
        $period = $request->get('period', now()->format('Y-m')); // Format: YYYY-MM
        $date = Carbon::createFromFormat('Y-m', $period);
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        $verifiedPaymentsTotal = Payment::where('status', 'verified')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        $invoicesIssuedTotal = Invoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('total_amount');

        $outstandingTotal = Invoice::whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->sum('total_amount');

        $paidInvoicesTotal = Invoice::whereIn('status', ['paid', 'partially_paid'])
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('total_amount');

        $today = now()->startOfDay();

        $aging0to30 = Invoice::where('status', 'overdue')
            ->whereBetween('due_date', [$today->copy()->subDays(30), $today])
            ->sum('total_amount');

        $aging31to60 = Invoice::where('status', 'overdue')
            ->whereBetween('due_date', [$today->copy()->subDays(60), $today->copy()->subDays(31)])
            ->sum('total_amount');

        $aging61to90 = Invoice::where('status', 'overdue')
            ->whereBetween('due_date', [$today->copy()->subDays(90), $today->copy()->subDays(61)])
            ->sum('total_amount');

        $agingOver90 = Invoice::where('status', 'overdue')
            ->where('due_date', '<', $today->copy()->subDays(90))
            ->sum('total_amount');

        $recentPayments = Payment::with(['invoice.client', 'verifiedBy'])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->orderBy('payment_date', 'desc')
            ->limit(10)
            ->get();

        return view('reports.financial.index', compact(
            'period',
            'verifiedPaymentsTotal',
            'invoicesIssuedTotal',
            'outstandingTotal',
            'paidInvoicesTotal',
            'aging0to30',
            'aging31to60',
            'aging61to90',
            'agingOver90',
            'recentPayments'
        ));
    }
}
