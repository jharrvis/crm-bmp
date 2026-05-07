export type DashboardPayload = {
  client: {
    id: number;
    client_code: string;
    name: string;
    status: string;
  };
  summary: {
    active_subscriptions_count: number;
    total_subscriptions_count: number;
    unpaid_invoices_count: number;
    overdue_invoices_count: number;
    open_tickets_count: number;
    unread_notifications_count: number;
  };
  recent_invoices: Array<{
    id: number;
    invoice_number: string;
    invoice_date: string | null;
    due_date: string | null;
    status: string;
    total_amount: number;
  }>;
  recent_notifications: Array<{
    id: number;
    type: string;
    title: string;
    message: string;
    read_at: string | null;
    created_at: string | null;
  }>;
};
