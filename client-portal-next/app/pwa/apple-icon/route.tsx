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
          background: "#ffffff",
          color: "#003d9b",
          fontSize: 74,
          fontWeight: 800,
          borderRadius: 44,
          border: "10px solid #003d9b",
        }}
      >
        BMP
      </div>
    ),
    {
      width: 180,
      height: 180,
    },
  );
}
