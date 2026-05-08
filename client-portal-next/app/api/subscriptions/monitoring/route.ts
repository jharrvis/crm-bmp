import { NextResponse } from "next/server";
import { crmRequest } from "@/lib/crm-api";
import { getPortalToken } from "@/lib/session";

export async function GET(request: Request) {
  const token = await getPortalToken();

  if (!token) {
    return NextResponse.json({ message: "Unauthenticated." }, { status: 401 });
  }

  const { searchParams } = new URL(request.url);
  const subscriptionId = searchParams.get("subscription_id");
  const period = searchParams.get("period") ?? "24h";
  const graphid = searchParams.get("graphid") ?? "";
  const itemin = searchParams.get("itemin") ?? "";
  const itemout = searchParams.get("itemout") ?? "";

  if (!subscriptionId) {
    return NextResponse.json({ message: "subscription_id wajib diisi." }, { status: 422 });
  }

  const query = new URLSearchParams({
    period,
    graphid,
    itemin,
    itemout,
  });

  try {
    const payload = await crmRequest(
      `/subscriptions/${subscriptionId}/usage/chart?${query.toString()}`,
      { token },
    );

    return NextResponse.json(payload);
  } catch (error) {
    const message = error instanceof Error ? error.message : "Gagal memuat chart monitoring.";

    return NextResponse.json({ message }, { status: 422 });
  }
}
