import { cookies } from "next/headers";
import { redirect } from "next/navigation";
import { SESSION_COOKIE_NAME } from "@/lib/config";

export async function getPortalToken() {
  const store = await cookies();
  return store.get(SESSION_COOKIE_NAME)?.value ?? null;
}

export async function requirePortalToken() {
  const token = await getPortalToken();

  if (!token) {
    redirect("/login");
  }

  return token;
}
