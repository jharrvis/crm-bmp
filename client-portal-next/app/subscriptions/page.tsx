import { PortalShell } from "@/components/portal-shell";
import { SubscriptionsMonitoringPanel } from "@/components/subscriptions-monitoring-panel";
import { crmRequest } from "@/lib/crm-api";
import { requirePortalToken } from "@/lib/session";
import type {
  SubscriptionChartPayload,
  SubscriptionListPayload,
  SubscriptionUsageInterface,
  SubscriptionUsagePayload,
} from "@/lib/types";

type SubscriptionsSearchParams = {
  subscription_id?: string;
  graphid?: string;
  itemin?: string;
  itemout?: string;
  period?: string;
};

export default async function SubscriptionsPage({
  searchParams,
}: {
  searchParams: Promise<SubscriptionsSearchParams>;
}) {
  const params = await searchParams;
  const token = await requirePortalToken();
  const payload = await crmRequest<SubscriptionListPayload>("/subscriptions", { token });

  const selectedPeriod = params.period?.trim() || "24h";
  const usageReadySubscriptions = payload.data.filter((subscription) => subscription.has_usage);
  const selectedSubscription =
    usageReadySubscriptions.find((subscription) => String(subscription.id) === params.subscription_id) ??
    usageReadySubscriptions[0] ??
    payload.data[0] ??
    null;

  let usagePayload: SubscriptionUsagePayload | null = null;
  let selectedInterface: SubscriptionUsageInterface | null = null;
  let initialChart: SubscriptionChartPayload | null = null;
  let initialChartError: string | null = null;

  if (selectedSubscription?.has_usage) {
    usagePayload = await crmRequest<SubscriptionUsagePayload>(
      `/subscriptions/${selectedSubscription.id}/usage`,
      { token },
    );

    selectedInterface =
      usagePayload.interfaces.find((item) => {
        const graphMatches = params.graphid && item.graphid === params.graphid;
        const itemMatches =
          params.itemin &&
          params.itemout &&
          item.itemIn === params.itemin &&
          item.itemOut === params.itemout;

        return graphMatches || itemMatches;
      }) ??
      usagePayload.interfaces[0] ??
      null;

    if (selectedInterface) {
      const query = new URLSearchParams({
        period: selectedPeriod,
        graphid: selectedInterface.graphid ?? "",
        itemin: selectedInterface.itemIn,
        itemout: selectedInterface.itemOut,
      });

      try {
        initialChart = await crmRequest<SubscriptionChartPayload>(
          `/subscriptions/${selectedSubscription.id}/usage/chart?${query.toString()}`,
          { token },
        );
      } catch (error) {
        initialChartError =
          error instanceof Error ? error.message : "Gagal memuat chart monitoring.";
      }
    }
  }

  return (
    <PortalShell
      active="subscriptions"
      title="Network Monitoring"
      subtitle="Daftar langganan dan status usage real-time."
    >
      <SubscriptionsMonitoringPanel
        subscriptions={payload.data}
        selectedSubscription={selectedSubscription}
        usagePayload={usagePayload}
        initialInterface={selectedInterface}
        initialChart={initialChart}
        initialChartError={initialChartError}
        initialPeriod={selectedPeriod}
      />
    </PortalShell>
  );
}
