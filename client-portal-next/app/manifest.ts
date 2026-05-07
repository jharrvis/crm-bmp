import type { MetadataRoute } from "next";

export default function manifest(): MetadataRoute.Manifest {
  return {
    name: "Portal Client BMPnet",
    short_name: "BMP Portal",
    description: "Portal client mobile-first untuk usage, invoice, tiket, dan notifikasi.",
    start_url: "/login",
    display: "standalone",
    background_color: "#eef4ff",
    theme_color: "#1d4ed8",
    orientation: "portrait",
    icons: [
      {
        src: "/portal-icon.svg",
        sizes: "any",
        type: "image/svg+xml",
      },
    ],
  };
}
