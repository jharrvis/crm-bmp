const env = (value: string | undefined, fallback: string): string => {
  const normalized = value?.trim();

  return normalized && normalized.length > 0 ? normalized : fallback;
};

export const CRM_API_BASE_URL = env(
  process.env.CRM_API_BASE_URL,
  "https://crm.bmp.net.id/api/client-portal",
);

export const SESSION_COOKIE_NAME = env(
  process.env.SESSION_COOKIE_NAME,
  "client_portal_token",
);
