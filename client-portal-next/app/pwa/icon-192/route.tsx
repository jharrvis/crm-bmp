import { ImageResponse } from "next/og";

export const runtime = "edge";

export async function GET() {
  return new ImageResponse(
    (
      <div
        style={{
          width: "100%",
          height: "100%",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          background: "linear-gradient(180deg, #0b4fc2 0%, #003d9b 100%)",
          color: "#ffffff",
          fontSize: 80,
          fontWeight: 800,
          borderRadius: 48,
        }}
      >
        BMP
      </div>
    ),
    {
      width: 192,
      height: 192,
    },
  );
}
