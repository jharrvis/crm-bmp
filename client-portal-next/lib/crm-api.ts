import { CRM_API_BASE_URL } from "@/lib/config";

type RequestOptions = {
  method?: "GET" | "POST";
  token?: string | null;
  body?: unknown;
};

export async function crmRequest<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const response = await fetch(`${CRM_API_BASE_URL}${path}`, {
    method: options.method ?? "GET",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...(options.token ? { Authorization: `Bearer ${options.token}` } : {}),
    },
    body: options.body ? JSON.stringify(options.body) : undefined,
    cache: "no-store",
  });

  const payload = await response.json().catch(() => ({}));

  if (!response.ok) {
    const message =
      payload?.message ||
      (payload?.errors
        ? Object.values(payload.errors).flat().join(", ")
        : "CRM API request failed.");

    throw new Error(message);
  }

  return payload as T;
}
