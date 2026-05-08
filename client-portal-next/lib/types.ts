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

export type PortalSubscriptionSummary = {
  id: number;
  subscription_code: string;
  status: string;
  package_name: string | null;
  service_name: string | null;
  next_billing_date: string | null;
  effective_price: number;
  has_usage: boolean;
};

export type SubscriptionListPayload = {
  data: PortalSubscriptionSummary[];
};

export type SubscriptionUsageInterface = {
  graphid?: string | null;
  name?: string | null;
  itemIn: string;
  itemOut: string;
};

export type SubscriptionUsagePayload = {
  subscription_id: number;
  subscription_code: string;
  service_name: string | null;
  package_name: string | null;
  interfaces: SubscriptionUsageInterface[];
};

export type SubscriptionChartPayload = {
  labels: string[];
  dataIn: number[];
  dataOut: number[];
  stats: {
    curIn: number;
    curOut: number;
    maxIn: number;
    maxOut: number;
    avgIn: number;
    avgOut: number;
  };
  isLive: boolean;
  rangeLabel: string;
  updatedAt: string;
  points: number;
  dataMode: string;
  interface?: {
    graphid?: string | null;
    name?: string | null;
    itemIn: string;
    itemOut: string;
  };
};
