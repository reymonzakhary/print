export const useDiscountRepository = () => {
  const api = useAPI();
  const discountsCache = ref([
    { mode: "run", status: true, slots: [] },
    { mode: "price", status: false, slots: [] },
  ]);

  const transformDiscountsForBackend = (discounts, mode) => [
    {
      mode: "run",
      status: mode === "runs",
      slots: mode === "runs" ? discounts : discountsCache.value[0].slots,
    },
    {
      mode: "price",
      status: mode === "price",
      slots: mode === "price" ? discounts : discountsCache.value[1].slots,
    },
  ];

  const transformDiscountsForFrontend = (item) => ({
    id: Math.random().toString(36).substring(2, 9),
    from: item.from,
    to: item.to,
    type: item.type,
    value: item.value,
  });

  async function getDiscounts(_mode, category) {
    if (_mode !== null && _mode !== "runs" && _mode !== "price")
      throw new Error("Invalid mode; choose either 'runs' or 'price'");

    if (_mode === "runs") _mode = "run";

    let endpoint = `discounts`;
    if (category !== "general") endpoint = `categories/${category}/discounts`;
    let response = await api.get(endpoint);
    if (!response.data.length) response.data = discountsCache.value;
    discountsCache.value = response.data;

    let mode = _mode;
    if (mode === null) {
      const activeMode = response.data.find((item) => item.status);
      mode = activeMode.mode;
    }

    const discounts = response.data.find((item) => item.mode === mode);
    const transformed = discounts.slots.map(transformDiscountsForFrontend);

    if (mode === "run") mode = "runs";
    return [transformed, mode];
  }

  async function saveDiscounts(discounts, mode, category) {
    if (mode !== "runs" && mode !== "price")
      throw new Error("Invalid mode; choose either 'runs' or 'price'");

    let payload;
    const transformed = transformDiscountsForBackend(discounts, mode);
    if (category === "general") {
      payload = { general: transformed };
    } else {
      payload = { discount: transformed };
    }

    let endpoint = `discounts`;
    if (category !== "general") endpoint = `categories/${category}/discounts`;
    const response = await api.put(endpoint, payload);
    return response.data;
  }

  return { getDiscounts, saveDiscounts };
};
