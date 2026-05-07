import { cookies } from "next/headers";
import { redirect } from "next/navigation";
import { crmRequest } from "@/lib/crm-api";
import { SESSION_COOKIE_NAME } from "@/lib/config";

export async function POST() {
  const store = await cookies();
  const token = store.get(SESSION_COOKIE_NAME)?.value ?? null;

  if (token) {
    try {
      await crmRequest("/auth/logout", {
        method: "POST",
        token,
      });
    } catch {
      // ignore logout errors and clear local cookie
    }
  }

  store.delete(SESSION_COOKIE_NAME);
  redirect("/login");
}
