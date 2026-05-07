import { cookies } from "next/headers";
import { redirect } from "next/navigation";
import { crmRequest } from "@/lib/crm-api";
import { SESSION_COOKIE_NAME } from "@/lib/config";

type VerifyOtpResponse = {
  token: string;
};

export async function POST(request: Request) {
  const formData = await request.formData();
  const email = String(formData.get("email") ?? "");
  const otp = String(formData.get("otp") ?? "");
  const deviceName = String(formData.get("device_name") ?? "Next.js Portal");

  try {
    const payload = await crmRequest<VerifyOtpResponse>("/auth/verify-otp", {
      method: "POST",
      body: {
        email,
        otp,
        device_name: deviceName,
      },
    });

    const store = await cookies();
    store.set(SESSION_COOKIE_NAME, payload.token, {
      httpOnly: true,
      sameSite: "lax",
      secure: false,
      path: "/",
      maxAge: 60 * 60 * 24 * 30,
    });

    redirect("/dashboard");
  } catch (error) {
    const message = error instanceof Error ? error.message : "Verifikasi OTP gagal.";
    redirect(`/verify-otp?email=${encodeURIComponent(email)}&error=${encodeURIComponent(message)}`);
  }
}
