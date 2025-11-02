export const useMarginRepository = () => {
  const api = useAPI();
  const marginsCache = ref([
    { mode: "run", status: true, slots: [] },
    { mode: "price", status: false, slots: [] },
  ]);

  const transformMarginsForBackend = (margins, mode) => [
    {
      mode: "run",
      status: mode === "runs",
      slots: mode === "runs" ? margins : marginsCache.value[0].slots,
    },
    {
      mode: "price",
      status: mode === "price",
      slots: mode === "price" ? margins : marginsCache.value[1].slots,
    },
  ];

  const transformMarginsForFrontend = (item) => ({
    id: Math.random().toString(36).substring(2, 9),
    from: item.from,
    to: item.to,
    type: item.type,
    value: item.value,
  });

  async function getMargins(_mode, category) {
    if (_mode !== null && _mode !== "runs" && _mode !== "price")
      throw new Error("Invalid mode; choose either 'runs' or 'price'");

    if (_mode === "runs") _mode = "run";

    let endpoint = `margins`;
    if (category !== "general") endpoint = `categories/${category}/margins`;
    let response = await api.get(endpoint);
    if (!response.data.length) response.data = marginsCache.value;
    marginsCache.value = response.data;

    let mode = _mode;
    if (mode === null) {
      const activeMode = response.data.find((item) => item.status);
      mode = activeMode.mode;
    }

    const margins = response.data.find((item) => item.mode === mode);
    const transformed = margins.slots.map(transformMarginsForFrontend);

    if (mode === "run") mode = "runs";
    return [transformed, mode];
  }

  async function saveMargins(margins, mode, category) {
    if (mode !== "runs" && mode !== "price")
      throw new Error("Invalid mode; choose either 'runs' or 'price'");

    let payload;
    const transformed = transformMarginsForBackend(margins, mode);
    if (category === "general") {
      payload = { general: transformed };
    } else {
      payload = { margin: transformed };
    }

    let endpoint = `margins`;
    if (category !== "general") endpoint = `categories/${category}/margins`;
    const response = await api.put(endpoint, payload);
    return response.data;
  }

  return { getMargins, saveMargins };
};
