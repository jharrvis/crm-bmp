import { redirect } from "next/navigation";
import { crmRequest } from "@/lib/crm-api";

export async function POST(request: Request) {
  const formData = await request.formData();
  const email = String(formData.get("email") ?? "");

  try {
    await crmRequest("/auth/request-otp", {
      method: "POST",
      body: { email },
    });
  } catch (error) {
    const message = error instanceof Error ? error.message : "Gagal meminta OTP.";
    redirect(`/login?error=${encodeURIComponent(message)}&email=${encodeURIComponent(email)}`);
  }

  redirect(
    `/verify-otp?email=${encodeURIComponent(email)}&message=${encodeURIComponent("OTP berhasil dikirim ke email terdaftar.")}`,
  );
}
