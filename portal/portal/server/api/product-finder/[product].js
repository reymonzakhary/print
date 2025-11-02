export default defineEventHandler(async (event) => {
  const payloads = await readBody(event);
  const product = getRouterParam(event, "product");
  const headers = getRequestHeaders(event);

  // Configure API client
  const config = useRuntimeConfig();
  const api = $fetch.create({
    baseURL: config.public.baseURL,
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
      Referer: headers.referer || "", // Provide default if undefined
      Authorization: headers.authorization || "", // Provide default if undefined
    },
    credentials: "include",
  });

  // Create an array of promises for each API call
  const url = `/finder/categories/${product}/products`;
  const requests = payloads.map(async (p) => await api(url, { method: "POST", body: p }));

  // Get all the results
  const results = await Promise.allSettled(requests);

  // Get all successful results
  const products = results.map((result, index) => {
    if (result.status === "fulfilled") {
      if (result.value.data?.length > 0) {
        const filteredResult = result.value.data.filter((prod) => prod.results?.prices?.length > 0);
        if (filteredResult.length > 0) return filteredResult;
        return {
          error: true,
          message: "Producers found but no prices",
          payload: payloads[index],
          response: result.value,
        };
      }
      return {
        error: true,
        message: "No producers found",
        payload: payloads[index],
        response: result.value,
      };
    }
    return {
      error: true,
      message: "An error has occurred",
      payload: payloads[index],
      response: result.value,
    };
  });

  return products;
});
