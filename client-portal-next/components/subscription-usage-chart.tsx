type UsageChartProps = {
  labels: string[];
  dataIn: number[];
  dataOut: number[];
};

const CHART_WIDTH = 720;
const CHART_HEIGHT = 240;
const PADDING_TOP = 18;
const PADDING_RIGHT = 16;
const PADDING_BOTTOM = 30;
const PADDING_LEFT = 42;

export function SubscriptionUsageChart({
  labels,
  dataIn,
  dataOut,
}: UsageChartProps) {
  const innerWidth = CHART_WIDTH - PADDING_LEFT - PADDING_RIGHT;
  const innerHeight = CHART_HEIGHT - PADDING_TOP - PADDING_BOTTOM;
  const maxValue = Math.max(1, ...dataIn, ...dataOut);
  const xStep = labels.length > 1 ? innerWidth / (labels.length - 1) : innerWidth;

  const lineIn = buildPath(dataIn, maxValue, xStep, innerHeight);
  const lineOut = buildPath(dataOut, maxValue, xStep, innerHeight);
  const areaIn = buildAreaPath(dataIn, maxValue, xStep, innerHeight);
  const areaOut = buildAreaPath(dataOut, maxValue, xStep, innerHeight);

  const yTicks = Array.from({ length: 5 }, (_, index) => {
    const value = (maxValue / 4) * (4 - index);
    const y = PADDING_TOP + (innerHeight / 4) * index;

    return { value, y };
  });

  const xTicks = buildTickIndexes(labels.length);

  return (
    <div className="ds-chart-card">
      <div className="ds-chart-legend">
        <span className="ds-chart-legend-item">
          <span className="ds-chart-swatch ds-chart-swatch-in" />
          Download
        </span>
        <span className="ds-chart-legend-item">
          <span className="ds-chart-swatch ds-chart-swatch-out" />
          Upload
        </span>
      </div>

      <div className="ds-chart-wrap">
        <svg
          viewBox={`0 0 ${CHART_WIDTH} ${CHART_HEIGHT}`}
          className="ds-chart-svg"
          role="img"
          aria-label="Bandwidth monitoring chart"
        >
          {yTicks.map((tick) => (
            <g key={tick.y}>
              <line
                x1={PADDING_LEFT}
                y1={tick.y}
                x2={CHART_WIDTH - PADDING_RIGHT}
                y2={tick.y}
                className="ds-chart-grid-line"
              />
              <text x={0} y={tick.y + 4} className="ds-chart-axis">
                {formatMbps(tick.value)}
              </text>
            </g>
          ))}

          {xTicks.map((tickIndex) => {
            const x = PADDING_LEFT + xStep * tickIndex;

            return (
              <g key={tickIndex}>
                <line
                  x1={x}
                  y1={PADDING_TOP}
                  x2={x}
                  y2={PADDING_TOP + innerHeight}
                  className="ds-chart-grid-line ds-chart-grid-line-soft"
                />
                <text x={x} y={CHART_HEIGHT - 8} textAnchor="middle" className="ds-chart-axis">
                  {labels[tickIndex]}
                </text>
              </g>
            );
          })}

          <path d={areaIn} className="ds-chart-area ds-chart-area-in" />
          <path d={areaOut} className="ds-chart-area ds-chart-area-out" />
          <path d={lineIn} className="ds-chart-line ds-chart-line-in" />
          <path d={lineOut} className="ds-chart-line ds-chart-line-out" />
        </svg>
      </div>
    </div>
  );
}

function buildPath(values: number[], maxValue: number, xStep: number, innerHeight: number): string {
  if (values.length === 0) {
    return "";
  }

  return values
    .map((value, index) => {
      const x = PADDING_LEFT + xStep * index;
      const y = PADDING_TOP + innerHeight - (value / maxValue) * innerHeight;

      return `${index === 0 ? "M" : "L"} ${x.toFixed(2)} ${y.toFixed(2)}`;
    })
    .join(" ");
}

function buildAreaPath(values: number[], maxValue: number, xStep: number, innerHeight: number): string {
  if (values.length === 0) {
    return "";
  }

  const line = buildPath(values, maxValue, xStep, innerHeight);
  const lastX = PADDING_LEFT + xStep * (values.length - 1);
  const baselineY = PADDING_TOP + innerHeight;

  return `${line} L ${lastX.toFixed(2)} ${baselineY.toFixed(2)} L ${PADDING_LEFT} ${baselineY.toFixed(2)} Z`;
}

function buildTickIndexes(length: number): number[] {
  if (length <= 1) {
    return [0];
  }

  const maxTicks = 6;
  const step = Math.max(1, Math.floor((length - 1) / (maxTicks - 1)));
  const indexes: number[] = [];

  for (let i = 0; i < length; i += step) {
    indexes.push(i);
  }

  if (indexes[indexes.length - 1] !== length - 1) {
    indexes.push(length - 1);
  }

  return indexes;
}

function formatMbps(value: number): string {
  return `${Math.round(value)} Mbps`;
}
